"""
Ingestion du corpus RAG GreenDC Advisor.

Pipeline: DOCUMENTS → extraction → nettoyage → chunking → embeddings → ChromaDB

Usage:
    python -m rag.ingest
    python -m rag.ingest --reset
    python -m rag.ingest --smoke-query "Qu'est-ce que le PUE ?"
"""

from __future__ import annotations

import argparse
import logging
import sys
import time
from pathlib import Path

# Permet `python rag/ingest.py` depuis la racine
_ROOT = Path(__file__).resolve().parent.parent
if str(_ROOT) not in sys.path:
    sys.path.insert(0, str(_ROOT))

from rag import config  # noqa: E402
from rag.chunker import chunk_documents  # noqa: E402
from rag.document_loader import load_all_documents  # noqa: E402
from rag.logging_setup import setup_logging  # noqa: E402
from rag.retriever import retrieve  # noqa: E402
from rag.vector_store import collection_stats, rebuild_index, upsert_chunks  # noqa: E402

logger = logging.getLogger(__name__)


def run_ingest(reset: bool = True) -> dict:
    """Exécute l'ingestion complète. Par défaut reconstruit l'index."""
    config.ensure_dirs()
    started = time.perf_counter()

    logger.info("=== Ingestion RAG GreenDC — démarrage ===")
    documents = load_all_documents()
    if not documents:
        raise RuntimeError("Aucun document trouvé dans rag/documents/")

    chunks = chunk_documents(documents)
    logger.info(
        "%d document(s) → %d chunk(s) (size=%d, overlap=%d)",
        len(documents),
        len(chunks),
        config.CHUNK_SIZE,
        config.CHUNK_OVERLAP,
    )

    if reset:
        indexed = rebuild_index(chunks)
    else:
        indexed = upsert_chunks(chunks)

    elapsed = time.perf_counter() - started
    stats = collection_stats()
    summary = {
        "documents": len(documents),
        "chunks": len(chunks),
        "indexed": indexed,
        "elapsed_seconds": round(elapsed, 2),
        "stats": stats,
        "by_source_type": {
            "official": sum(1 for d in documents if d.source_type == "official"),
            "generated": sum(1 for d in documents if d.source_type == "generated"),
        },
    }
    logger.info("=== Ingestion terminée: %s ===", summary)
    return summary


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(description="Ingestion corpus RAG GreenDC")
    parser.add_argument(
        "--reset",
        action="store_true",
        default=True,
        help="Reconstruire l'index (défaut: oui)",
    )
    parser.add_argument(
        "--no-reset",
        action="store_true",
        help="Upsert sans supprimer la collection",
    )
    parser.add_argument(
        "--smoke-query",
        type=str,
        default="",
        help="Après ingestion, tester une requête de retrieval",
    )
    args = parser.parse_args(argv)

    setup_logging()
    reset = not args.no_reset

    try:
        summary = run_ingest(reset=reset)
    except Exception:
        logger.exception("Ingestion échouée")
        return 1

    print("\n--- Résumé ingestion ---")
    print(f"Documents : {summary['documents']} "
          f"(official={summary['by_source_type']['official']}, "
          f"generated={summary['by_source_type']['generated']})")
    print(f"Chunks    : {summary['chunks']}")
    print(f"Indexés   : {summary['indexed']}")
    print(f"Durée     : {summary['elapsed_seconds']} s")
    print(f"Chroma    : {summary['stats']}")

    query = args.smoke_query or "Qu'est-ce que le PUE et comment l'améliorer ?"
    print(f"\n--- Smoke retrieval: {query!r} ---")
    hits = retrieve(query, top_k=3)
    if not hits:
        print("Aucun résultat (collection vide ?)")
        return 1
    for i, hit in enumerate(hits, 1):
        page = hit.get("page")
        page_str = f"p.{page}" if page else "n/a"
        print(
            f"{i}. [{hit.get('source_type')}] {hit.get('title')} — {page_str} "
            f"(score={hit.get('score')}, file={hit.get('filename')})"
        )
        preview = (hit.get("text") or "")[:180].replace("\n", " ")
        print(f"   {preview}...")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
