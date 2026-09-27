"""Configuration du service RAG GreenDC Advisor."""

from __future__ import annotations

import os
from pathlib import Path

from dotenv import load_dotenv

RAG_DIR = Path(__file__).resolve().parent
PROJECT_ROOT = RAG_DIR.parent

# Charge .env à la racine du projet, puis rag/.env (sans écraser)
load_dotenv(PROJECT_ROOT / ".env")
load_dotenv(RAG_DIR / ".env")

DOCUMENTS_DIR = RAG_DIR / "documents"
OFFICIAL_DIR = DOCUMENTS_DIR / "official"
GENERATED_DIR = DOCUMENTS_DIR / "generated"

STORAGE_DIR = Path(os.getenv("CHROMA_PATH", str(RAG_DIR / "storage" / "chroma")))
LOG_DIR = RAG_DIR / "logs"

COLLECTION_NAME = os.getenv("CHROMA_COLLECTION", "greendc_docs")

# Modèle léger multilingue FR/EN (~120M params)
EMBEDDING_MODEL = os.getenv(
    "EMBEDDING_MODEL",
    "sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2",
)

CHUNK_SIZE = int(os.getenv("RAG_CHUNK_SIZE", "800"))
CHUNK_OVERLAP = int(os.getenv("RAG_CHUNK_OVERLAP", "150"))

TOP_K = int(os.getenv("RAG_TOP_K", "5"))

LLM_API_KEY = os.getenv("LLM_API_KEY", "")
LLM_MODEL = os.getenv("LLM_MODEL", "llama3.2:latest")
LLM_BASE_URL = os.getenv("LLM_BASE_URL", "http://127.0.0.1:11434/v1")
LLM_TIMEOUT_SECONDS = int(os.getenv("LLM_TIMEOUT_SECONDS", "120"))

RAG_API_HOST = os.getenv("RAG_API_HOST", "127.0.0.1")
RAG_API_PORT = int(os.getenv("RAG_API_PORT", "8000"))

# Boost relatif pour les sources officielles lors du ranking
OFFICIAL_SOURCE_BOOST = float(os.getenv("RAG_OFFICIAL_BOOST", "0.05"))


def ensure_dirs() -> None:
    """Crée les répertoires de stockage et de logs si besoin."""
    STORAGE_DIR.mkdir(parents=True, exist_ok=True)
    LOG_DIR.mkdir(parents=True, exist_ok=True)
