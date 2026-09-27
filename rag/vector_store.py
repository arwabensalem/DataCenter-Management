"""Stockage vectoriel ChromaDB (persistant)."""

from __future__ import annotations

import logging
from typing import Any

import chromadb
from chromadb.config import Settings

from . import config
from .chunker import Chunk
from .embeddings import embed_texts

logger = logging.getLogger(__name__)


def get_client() -> chromadb.PersistentClient:
    config.ensure_dirs()
    return chromadb.PersistentClient(
        path=str(config.STORAGE_DIR),
        settings=Settings(anonymized_telemetry=False),
    )


def get_collection(reset: bool = False):
    client = get_client()
    if reset:
        try:
            client.delete_collection(config.COLLECTION_NAME)
            logger.info("Collection ChromaDB réinitialisée: %s", config.COLLECTION_NAME)
        except Exception:
            pass
    return client.get_or_create_collection(
        name=config.COLLECTION_NAME,
        metadata={"hnsw:space": "cosine"},
    )


def _sanitize_metadata(meta: dict[str, Any]) -> dict[str, Any]:
    """Chroma n'accepte que str, int, float, bool."""
    clean: dict[str, Any] = {}
    for key, value in meta.items():
        if value is None:
            continue
        if isinstance(value, (str, int, float, bool)):
            clean[key] = value
        else:
            clean[key] = str(value)
    return clean


def upsert_chunks(chunks: list[Chunk], batch_size: int = 64) -> int:
    """Indexe (ou met à jour) les chunks dans ChromaDB. Retourne le nombre indexé."""
    if not chunks:
        return 0

    collection = get_collection(reset=False)
    total = 0

    for i in range(0, len(chunks), batch_size):
        batch = chunks[i : i + batch_size]
        ids = [c.chunk_id for c in batch]
        documents = [c.text for c in batch]
        metadatas = [_sanitize_metadata(c.metadata) for c in batch]
        embeddings = embed_texts(documents)

        collection.upsert(
            ids=ids,
            documents=documents,
            metadatas=metadatas,
            embeddings=embeddings,
        )
        total += len(batch)
        logger.info("Indexé %d / %d chunks", total, len(chunks))

    return total


def rebuild_index(chunks: list[Chunk], batch_size: int = 64) -> int:
    """Supprime la collection puis réindexe entièrement."""
    get_collection(reset=True)
    # Recréer après reset
    return upsert_chunks(chunks, batch_size=batch_size)


def collection_stats() -> dict[str, Any]:
    """Statistiques de la collection."""
    try:
        collection = get_collection(reset=False)
        count = collection.count()
        return {
            "collection": config.COLLECTION_NAME,
            "path": str(config.STORAGE_DIR),
            "document_chunks": count,
            "embedding_model": config.EMBEDDING_MODEL,
        }
    except Exception as exc:
        return {
            "collection": config.COLLECTION_NAME,
            "path": str(config.STORAGE_DIR),
            "document_chunks": 0,
            "error": str(exc),
        }
