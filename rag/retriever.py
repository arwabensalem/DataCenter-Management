"""Retrieval sémantique depuis ChromaDB."""

from __future__ import annotations

import logging
from typing import Any

from . import config
from .embeddings import embed_query
from .vector_store import get_collection

logger = logging.getLogger(__name__)


def retrieve(
    question: str,
    top_k: int | None = None,
    where: dict[str, Any] | None = None,
) -> list[dict[str, Any]]:
    """
    Recherche les passages les plus pertinents.

    Retourne une liste de dicts:
      text, score, filename, source_type, source, title, category, year, page, chunk_id
    """
    k = top_k or config.TOP_K
    collection = get_collection(reset=False)
    if collection.count() == 0:
        logger.warning("Collection vide — lancer l'ingestion d'abord")
        return []

    query_embedding = embed_query(question)
    kwargs: dict[str, Any] = {
        "query_embeddings": [query_embedding],
        "n_results": k,
        "include": ["documents", "metadatas", "distances"],
    }
    if where:
        kwargs["where"] = where

    raw = collection.query(**kwargs)

    results: list[dict[str, Any]] = []
    documents = (raw.get("documents") or [[]])[0]
    metadatas = (raw.get("metadatas") or [[]])[0]
    distances = (raw.get("distances") or [[]])[0]

    for text, meta, dist in zip(documents, metadatas, distances):
        meta = meta or {}
        # Distance cosine Chroma → score similarité approximatif
        score = 1.0 - float(dist) if dist is not None else 0.0
        if meta.get("source_type") == "official":
            score += config.OFFICIAL_SOURCE_BOOST

        results.append(
            {
                "text": text or "",
                "score": round(score, 4),
                "filename": meta.get("filename"),
                "source_type": meta.get("source_type"),
                "source": meta.get("source"),
                "title": meta.get("title"),
                "category": meta.get("category"),
                "year": meta.get("year"),
                "page": meta.get("page"),
                "chunk_id": meta.get("chunk_id"),
            }
        )

    results.sort(key=lambda r: r["score"], reverse=True)
    return results
