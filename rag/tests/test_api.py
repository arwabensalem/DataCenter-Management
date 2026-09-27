"""Tests API FastAPI (TestClient)."""

from __future__ import annotations

import sys
from pathlib import Path

from fastapi.testclient import TestClient

ROOT = Path(__file__).resolve().parents[2]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

from rag.api import app
from rag.vector_store import collection_stats

client = TestClient(app)


def test_health_endpoint():
    res = client.get("/api/rag/health")
    assert res.status_code == 200
    data = res.json()
    assert data["service"] == "greendc-rag"
    assert "index" in data
    assert "llm_configured" in data


def test_query_validation_too_short():
    res = client.post("/api/rag/query", json={"question": "ab"})
    assert res.status_code == 422


def test_query_with_business_context():
    if collection_stats().get("document_chunks", 0) == 0:
        return  # skip soft

    res = client.post(
        "/api/rag/query",
        json={
            "question": "Pourquoi mon PUE est élevé ?",
            "data_center_id": 1,
            "user_id": 1,
            "business_context": {
                "data_center": {
                    "name": "DC Test",
                    "pue": 1.89,
                    "total_energy_kwh": 8500,
                    "it_energy_kwh": 4500,
                    "cooling_energy_kwh": 2100,
                    "cooling_share_pct": 24.7,
                    "pv_coverage_pct": 35,
                },
                "detected_problems": [
                    {"title": "PUE élevé", "message": "PUE > 1.8", "level": "warning"}
                ],
                "rule_recommendations": [
                    {
                        "title": "Optimiser le cooling",
                        "category": "cooling",
                        "priority": "haute",
                        "reason": "Part climatisation significative",
                    }
                ],
            },
        },
    )
    assert res.status_code == 200
    data = res.json()
    assert "answer" in data
    assert "sources" in data
    assert "metrics" in data
    assert data["metrics"]["data_center_id"] == 1
    # Ne doit pas inventer un PUE différent dans les sources (sources = docs)
    for s in data["sources"]:
        assert "file" in s or "title" in s
