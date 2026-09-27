"""Découpage des documents en chunks avec métadonnées."""

from __future__ import annotations

import hashlib
import re
from dataclasses import dataclass
from typing import Any

from .config import CHUNK_OVERLAP, CHUNK_SIZE
from .document_loader import LoadedDocument


@dataclass
class Chunk:
    """Fragment indexable pour ChromaDB."""

    chunk_id: str
    text: str
    metadata: dict[str, Any]


def _stable_chunk_id(filename: str, page: int | None, index: int, text: str) -> str:
    digest = hashlib.sha1(text.encode("utf-8")).hexdigest()[:10]
    page_part = page if page is not None else 0
    safe_name = re.sub(r"[^a-zA-Z0-9._-]+", "_", filename)[:80]
    return f"{safe_name}__p{page_part}__c{index}__{digest}"


def _split_text(text: str, chunk_size: int, overlap: int) -> list[str]:
    """Découpe par fenêtres avec overlap, en privilégiant les frontières de paragraphe."""
    text = text.strip()
    if not text:
        return []
    if len(text) <= chunk_size:
        return [text]

    chunks: list[str] = []
    start = 0
    length = len(text)

    while start < length:
        end = min(start + chunk_size, length)
        if end < length:
            # Cherche une coupure « douce » (paragraphe, phrase, espace)
            window = text[start:end]
            soft = max(
                window.rfind("\n\n"),
                window.rfind(". "),
                window.rfind("。"),
                window.rfind(" "),
            )
            if soft > chunk_size * 0.4:
                end = start + soft + 1

        piece = text[start:end].strip()
        if piece:
            chunks.append(piece)

        if end >= length:
            break
        start = max(0, end - overlap)
        # Évite une boucle infinie si overlap trop grand
        if start >= end:
            start = end

    return chunks


def chunk_document(
    document: LoadedDocument,
    chunk_size: int = CHUNK_SIZE,
    overlap: int = CHUNK_OVERLAP,
) -> list[Chunk]:
    """Transforme un document chargé en liste de chunks métadonnés."""
    results: list[Chunk] = []
    global_index = 0

    for page in document.pages:
        pieces = _split_text(page.text, chunk_size, overlap)
        for piece in pieces:
            chunk_id = _stable_chunk_id(document.filename, page.page, global_index, piece)
            metadata: dict[str, Any] = {
                "filename": document.filename,
                "source_type": document.source_type,
                "source": document.source,
                "title": document.title,
                "category": document.category,
                "chunk_id": chunk_id,
                "chunk_index": global_index,
            }
            if document.year is not None:
                metadata["year"] = document.year
            if page.page is not None:
                metadata["page"] = page.page

            results.append(Chunk(chunk_id=chunk_id, text=piece, metadata=metadata))
            global_index += 1

    return results


def chunk_documents(documents: list[LoadedDocument]) -> list[Chunk]:
    """Chunking batch."""
    out: list[Chunk] = []
    for doc in documents:
        out.extend(chunk_document(doc))
    return out
