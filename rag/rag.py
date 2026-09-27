"""Point d'entrée historique — délègue à ingest / api.

Usage:
    python -m rag.ingest
    python -m rag.api
"""

from rag.ingest import main as ingest_main

if __name__ == "__main__":
    raise SystemExit(ingest_main())
