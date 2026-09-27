"""
API FastAPI RAG GreenDC Advisor.

Endpoints :
  GET  /api/rag/health
  POST /api/rag/ingest
  POST /api/rag/query
"""

from __future__ import annotations

import logging
import sys
import time
from pathlib import Path
from typing import Any

from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field

_ROOT = Path(__file__).resolve().parent.parent
if str(_ROOT) not in sys.path:
    sys.path.insert(0, str(_ROOT))

from rag import config  # noqa: E402
from rag.context_builder import build_prompt_context  # noqa: E402
from rag.ingest import run_ingest  # noqa: E402
from rag.llm import generate_answer, is_llm_configured, llm_provider_label  # noqa: E402
from rag.logging_setup import setup_logging  # noqa: E402
from rag.retriever import retrieve  # noqa: E402
from rag.vector_store import collection_stats  # noqa: E402

setup_logging()
logger = logging.getLogger(__name__)

app = FastAPI(
    title="GreenDC RAG API",
    version="0.2.0",
    description="Service RAG local pour GreenDC Advisor",
)


class HistoryMessage(BaseModel):
    role: str = Field(..., pattern="^(user|assistant|system)$")
    content: str = Field(..., min_length=1, max_length=4000)


class QueryRequest(BaseModel):
    question: str = Field(..., min_length=3, max_length=2000)
    data_center_id: int | None = None
    user_id: int | None = None
    top_k: int | None = Field(default=None, ge=1, le=20)
    business_context: dict[str, Any] | None = None
    history: list[HistoryMessage] | None = None
    mode: str | None = Field(default="chat", pattern="^(chat|analysis|explain)$")


class IngestRequest(BaseModel):
    reset: bool = True


@app.get("/api/rag/health")
def health() -> dict[str, Any]:
    stats = collection_stats()
    ready = int(stats.get("document_chunks") or 0) > 0
    return {
        "status": "ok" if ready else "degraded",
        "service": "greendc-rag",
        "ready": ready,
        "llm_configured": is_llm_configured(),
        "llm_provider": llm_provider_label(),
        "llm_model": config.LLM_MODEL if is_llm_configured() else None,
        "index": stats,
    }


@app.post("/api/rag/ingest")
def ingest(body: IngestRequest | None = None) -> dict[str, Any]:
    reset = True if body is None else body.reset
    try:
        started = time.perf_counter()
        summary = run_ingest(reset=reset)
        summary["api_elapsed_seconds"] = round(time.perf_counter() - started, 2)
        return summary
    except Exception as exc:
        logger.exception("Ingest API error")
        raise HTTPException(status_code=500, detail=str(exc)) from exc


@app.post("/api/rag/query")
def query(body: QueryRequest) -> dict[str, Any]:
    started = time.perf_counter()
    question = body.question.strip()

    try:
        passages = retrieve(question, top_k=body.top_k)
    except Exception as exc:
        logger.exception("Retrieval error")
        raise HTTPException(status_code=500, detail=f"Retrieval error: {exc}") from exc

    business = body.business_context or {}
    if body.data_center_id is not None:
        business.setdefault("data_center_id", body.data_center_id)
    if body.user_id is not None:
        business.setdefault("user_id", body.user_id)
    if body.mode:
        business.setdefault("mode", body.mode)

    context = build_prompt_context(
        question=question,
        rag_passages=passages,
        business_context=business,
    )

    history = (
        [{"role": m.role, "content": m.content} for m in body.history]
        if body.history
        else None
    )

    llm_result = generate_answer(question, context, history=history)
    elapsed_ms = round((time.perf_counter() - started) * 1000, 1)

    logger.info(
        "query ok | dc=%s | passages=%d | llm=%s | %sms",
        body.data_center_id,
        len(passages),
        bool(llm_result.get("llm_used")),
        elapsed_ms,
    )

    # Sources dédupliquées (fichier + page)
    sources = []
    seen: set[tuple[Any, Any]] = set()
    for s in llm_result.get("sources") or []:
        key = (s.get("file"), s.get("page"))
        if key in seen:
            continue
        seen.add(key)
        sources.append(s)

    return {
        "answer": llm_result.get("answer", ""),
        "sources": sources,
        "metrics": {
            "elapsed_ms": elapsed_ms,
            "passages_retrieved": len(passages),
            "llm_used": bool(llm_result.get("llm_used")),
            "llm_error": bool(llm_result.get("llm_error")),
            "data_center_id": body.data_center_id,
            "mode": body.mode,
        },
        "recommendations": llm_result.get("recommendations") or [],
        "passages": passages,
    }


def main() -> None:
    import uvicorn

    uvicorn.run(
        "rag.api:app",
        host=config.RAG_API_HOST,
        port=config.RAG_API_PORT,
        reload=False,
    )


if __name__ == "__main__":
    main()
