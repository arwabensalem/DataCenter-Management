"""Client LLM OpenAI-compatible (Ollama local ou API cloud via .env)."""

from __future__ import annotations

import json
import logging
import re
from typing import Any

import httpx

from . import config

logger = logging.getLogger(__name__)

SYSTEM_PROMPT = """Tu es GreenDC AI Advisor, assistant expert en efficacité énergétique des Data Centers
et photovoltaïque (contexte Tunisie possible).

Règles STRICTES :
1. Utilise UNIQUEMENT les données du Data Center fournies dans le contexte métier.
2. Utilise UNIQUEMENT les passages RAG fournis pour les connaissances documentaires.
3. Ne invente JAMAIS de chiffres, de sources, de pages, ni de réglementations.
4. Distingue clairement les FAITS (calculés / documentés) des RECOMMANDATIONS.
5. Les valeurs numériques importantes viennent des calculateurs métier — ne les recalcule pas.
6. Réponds en français par défaut ; en anglais si l'utilisateur le demande.
7. Si une information manque, dis-le clairement.
8. Cite les documents réellement fournis (titre + page si disponible).
9. N'affirme jamais avoir effectué un calcul qui n'a pas été fourni dans le contexte.
10. Si l'impact énergétique n'est pas chiffré par un calculateur, indique qu'il est qualitatif
    ou qu'une simulation est nécessaire.
11. Ne demande et n'utilise jamais de mots de passe ou données personnelles sensibles.

Format de réponse :
- Réponds de façon claire, structurée et conversationnelle (pas de dump brut de métriques).
- Commence par une réponse directe à la question.
- Puis développe brièvement (faits, causes, actions).
- Termine par une section « Sources » listant uniquement les documents fournis que tu as utilisés.
"""


def _is_local_ollama(base_url: str) -> bool:
    b = (base_url or "").lower()
    return "11434" in b or "ollama" in b


def is_llm_configured() -> bool:
    """True si un modèle est défini et qu'une clé OU un endpoint Ollama local est prêt."""
    if not config.LLM_MODEL:
        return False
    if config.LLM_API_KEY:
        return True
    return _is_local_ollama(config.LLM_BASE_URL)


def llm_provider_label() -> str:
    if not is_llm_configured():
        return "none"
    if _is_local_ollama(config.LLM_BASE_URL):
        return "ollama"
    return "openai_compatible"


def _build_user_message(question: str, context: dict[str, Any], history: list[dict[str, str]] | None) -> str:
    parts: list[str] = []

    business = context.get("business") or {}
    if business:
        parts.append("### Contexte métier (données calculées / MySQL)")
        parts.append("```json")
        parts.append(json.dumps(business, ensure_ascii=False, indent=2, default=str))
        parts.append("```")

    passages = context.get("rag_passages") or []
    if passages:
        parts.append("### Passages documentaires (RAG)")
        for i, p in enumerate(passages, 1):
            title = p.get("title") or p.get("filename") or "Document"
            page = p.get("page")
            page_str = f", page {page}" if page else ""
            src = p.get("source") or p.get("source_type") or ""
            parts.append(f"[{i}] {title}{page_str} ({src})")
            parts.append((p.get("text") or "")[:1200])
            parts.append("")

    if history:
        parts.append("### Historique récent (limité)")
        for msg in history[-6:]:
            role = msg.get("role", "user")
            parts.append(f"{role}: {msg.get('content', '')[:500]}")

    parts.append("### Question")
    parts.append(question)
    return "\n".join(parts)


def _sources_from_passages(context: dict[str, Any]) -> list[dict[str, Any]]:
    sources = []
    seen: set[tuple[Any, Any]] = set()
    for p in context.get("rag_passages") or []:
        key = (p.get("filename"), p.get("page"))
        if key in seen:
            continue
        seen.add(key)
        sources.append(
            {
                "title": p.get("title") or p.get("filename"),
                "file": p.get("filename"),
                "page": p.get("page"),
                "source": p.get("source"),
                "source_type": p.get("source_type"),
            }
        )
    return sources


def _extractive_fallback(question: str, context: dict[str, Any]) -> dict[str, Any]:
    """Réponse déterministe sans LLM à partir des passages + métriques."""
    passages = context.get("rag_passages") or []
    business = context.get("business") or {}
    dc = business.get("data_center") or business

    lines: list[str] = []
    name = (dc.get("name") or dc.get("nom") or "votre Data Center") if dc else "votre Data Center"

    # Réponse directe orientée question (PV / PUE / cooling…)
    q = question.lower()
    if "pv" in q or "photo" in q or "panneau" in q or "solaire" in q:
        cov = dc.get("pv_coverage_pct") if dc else None
        if cov is not None:
            verdict = (
                "insuffisante par rapport aux objectifs internes (≥ 50 %)"
                if float(cov) < 50
                else "correcte selon les seuils internes"
            )
            lines.append(
                f"Pour **{name}**, la couverture photovoltaïque calculée est de "
                f"**{cov} %** — elle est considérée comme **{verdict}**."
            )
        else:
            lines.append(f"Aucun dimensionnement PV n'est disponible pour **{name}**.")
    elif "pue" in q:
        pue = dc.get("pue") if dc else None
        if pue is not None:
            lines.append(
                f"Le PUE calculé de **{name}** est **{pue}** "
                f"({dc.get('pue_label') or 'non classé'}). "
                "Un PUE élevé indique souvent une part importante de cooling / infrastructure."
            )
        else:
            lines.append(f"Le PUE de **{name}** n'a pas pu être calculé (données IT insuffisantes).")
    else:
        lines.append(f"Voici une synthèse déterministe pour **{name}** (LLM indisponible).")

    lines.append("")
    if dc:
        lines.append("**Faits (calculateurs)**")
        if dc.get("pue") is not None:
            lines.append(f"- PUE : **{dc.get('pue')}** ({dc.get('pue_label') or ''})")
        if dc.get("total_energy_kwh") is not None:
            lines.append(f"- Consommation totale : **{dc.get('total_energy_kwh')} kWh/an**")
        if dc.get("it_energy_kwh") is not None:
            lines.append(f"- Énergie IT : **{dc.get('it_energy_kwh')} kWh/an**")
        if dc.get("cooling_energy_kwh") is not None:
            lines.append(
                f"- Cooling : **{dc.get('cooling_energy_kwh')} kWh/an** "
                f"({dc.get('cooling_share_pct', '?')} %)"
            )
        if dc.get("pv_coverage_pct") is not None:
            lines.append(f"- Couverture PV : **{dc.get('pv_coverage_pct')} %**")
        lines.append("")

    problems = business.get("detected_problems") or []
    if problems:
        lines.append("**Problèmes détectés (règles métiers)**")
        for p in problems[:6]:
            lines.append(f"- {p.get('title', p.get('message', p))}")
        lines.append("")

    sources = _sources_from_passages(context)
    if passages:
        lines.append("**Repères documentaires (RAG)**")
        for i, p in enumerate(passages[:3], 1):
            title = p.get("title") or p.get("filename")
            page = p.get("page")
            page_str = f" — p.{page}" if page else ""
            excerpt = re.sub(r"\s+", " ", (p.get("text") or ""))[:220]
            lines.append(f"{i}. {title}{page_str} : {excerpt}…")
        lines.append("")

    lines.append(
        "_Mode dégradé : configurez Ollama ou une clé LLM dans `.env`, puis redémarrez l'API RAG._"
    )

    return {
        "answer": "\n".join(lines),
        "sources": sources,
        "llm_used": False,
        "recommendations": business.get("rule_recommendations") or [],
    }


def generate_answer(
    question: str,
    context: dict[str, Any],
    history: list[dict[str, str]] | None = None,
) -> dict[str, Any]:
    """Génère une réponse via LLM ou fallback extractif."""
    if not is_llm_configured():
        logger.info("LLM non configuré — fallback extractif")
        return _extractive_fallback(question, context)

    user_content = _build_user_message(question, context, history)
    base = (config.LLM_BASE_URL or "http://127.0.0.1:11434/v1").rstrip("/")
    url = f"{base}/chat/completions"
    timeout = float(config.LLM_TIMEOUT_SECONDS)

    payload: dict[str, Any] = {
        "model": config.LLM_MODEL,
        "temperature": 0.2,
        "messages": [
            {"role": "system", "content": SYSTEM_PROMPT},
            {"role": "user", "content": user_content},
        ],
    }

    headers = {"Content-Type": "application/json"}
    # Ollama accepte une clé factice ; les APIs cloud exigent la vraie clé
    api_key = config.LLM_API_KEY or "ollama"
    headers["Authorization"] = f"Bearer {api_key}"

    try:
        logger.info(
            "Appel LLM provider=%s model=%s",
            llm_provider_label(),
            config.LLM_MODEL,
        )
        with httpx.Client(timeout=timeout) as client:
            resp = client.post(url, headers=headers, json=payload)
            resp.raise_for_status()
            data = resp.json()
        answer = (
            data.get("choices", [{}])[0]
            .get("message", {})
            .get("content", "")
            .strip()
        )
        if not answer:
            logger.warning("LLM réponse vide — fallback")
            return _extractive_fallback(question, context)

        return {
            "answer": answer,
            "sources": _sources_from_passages(context),
            "llm_used": True,
            "recommendations": context.get("business", {}).get("rule_recommendations") or [],
        }
    except Exception:
        logger.exception("Erreur LLM — fallback extractif")
        result = _extractive_fallback(question, context)
        result["llm_error"] = True
        return result
