"""Embeddings locaux via Sentence Transformers (modèle léger multilingue)."""

from __future__ import annotations

import logging
from functools import lru_cache
from typing import Sequence

from .config import EMBEDDING_MODEL

logger = logging.getLogger(__name__)


@lru_cache(maxsize=1)
def get_model():
    """Charge le modèle une seule fois (cache processus)."""
    from sentence_transformers import SentenceTransformer

    logger.info("Chargement du modèle d'embeddings: %s", EMBEDDING_MODEL)
    model = SentenceTransformer(EMBEDDING_MODEL)
    return model


def embed_texts(texts: Sequence[str], batch_size: int = 32) -> list[list[float]]:
    """Génère les embeddings pour une liste de textes."""
    if not texts:
        return []
    model = get_model()
    vectors = model.encode(
        list(texts),
        batch_size=batch_size,
        show_progress_bar=len(texts) > 20,
        convert_to_numpy=True,
        normalize_embeddings=True,
    )
    return [v.tolist() for v in vectors]


def embed_query(text: str) -> list[float]:
    """Embedding d'une requête utilisateur."""
    return embed_texts([text])[0]
