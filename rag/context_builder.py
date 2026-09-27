"""Construction du contexte hybride (métier + RAG) pour le LLM."""

from __future__ import annotations

from typing import Any


def build_prompt_context(
    question: str,
    rag_passages: list[dict[str, Any]],
    business_context: dict[str, Any] | None = None,
) -> dict[str, Any]:
    """Assemble un contexte structuré (pas de dump massif)."""
    business = business_context or {}

    # Ne garder que des clés utiles / déjà filtrées côté PHP
    allowed_top = {
        "data_center",
        "data_center_id",
        "user_id",
        "mode",
        "detected_problems",
        "rule_recommendations",
        "alerts",
        "latest_simulation",
        "pv",
        "co2",
        "equipments_top",
        "energy_by_category",
        "note",
    }
    slim_business = {k: v for k, v in business.items() if k in allowed_top or k == "data_center"}

    return {
        "question": question,
        "business": slim_business,
        "rag_passages": [
            {
                "text": (p.get("text") or "")[:1500],
                "title": p.get("title"),
                "filename": p.get("filename"),
                "page": p.get("page"),
                "source": p.get("source"),
                "source_type": p.get("source_type"),
                "category": p.get("category"),
                "score": p.get("score"),
            }
            for p in rag_passages
        ],
    }
