"""Tests minimum du pipeline RAG GreenDC."""

from __future__ import annotations

import sys
from pathlib import Path

import pytest

ROOT = Path(__file__).resolve().parents[2]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

from rag.chunker import chunk_document
from rag.document_loader import clean_text, load_markdown
from rag.vector_store import collection_stats


def test_clean_text_strips_noise():
    raw = "Hello\x00  world\r\n\n\n\nNext"
    cleaned = clean_text(raw)
    assert "\x00" not in cleaned
    assert "Hello world" in cleaned or "Hello  world" not in cleaned


def test_load_generated_markdown():
    path = ROOT / "rag" / "documents" / "generated" / "02_pue_power_usage_effectiveness.md"
    assert path.is_file()
    doc = load_markdown(path)
    assert doc.source_type == "generated"
    assert doc.category == "pue"
    assert len(doc.pages) >= 1
    assert "PUE" in doc.title or "PUE" in doc.full_text


def test_chunk_preserves_metadata():
    path = ROOT / "rag" / "documents" / "generated" / "02_pue_power_usage_effectiveness.md"
    doc = load_markdown(path)
    chunks = chunk_document(doc, chunk_size=400, overlap=50)
    assert len(chunks) >= 1
    meta = chunks[0].metadata
    assert meta["filename"] == path.name
    assert meta["source_type"] == "generated"
    assert "chunk_id" in meta
    assert meta["title"]


def test_chroma_index_exists_after_ingest():
    stats = collection_stats()
    # L'index peut être vide en CI fraîche — on vérifie juste la structure
    assert "collection" in stats
    assert "document_chunks" in stats


@pytest.mark.skipif(
    collection_stats().get("document_chunks", 0) == 0,
    reason="Index Chroma vide — lancer python -m rag.ingest",
)
def test_french_retrieval_pue():
    from rag.retriever import retrieve

    hits = retrieve("Qu'est-ce que le PUE et comment l'améliorer ?", top_k=3)
    assert len(hits) >= 1
    joined = " ".join((h.get("filename") or "") + " " + (h.get("text") or "") for h in hits).lower()
    assert "pue" in joined or "power" in joined
    # Métadonnées présentes
    assert hits[0].get("filename")
    assert hits[0].get("source_type") in ("official", "generated")
