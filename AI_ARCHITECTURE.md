# GreenDC Advisor — Architecture AI / RAG

## Schéma global

```text
┌──────────────────────────────────────────────────────────────┐
│  Navigateur (Bootstrap 5)                                    │
│  Dashboard AI Insights │ Chatbot │ Analyse │ Liens modules   │
└────────────────────────────┬─────────────────────────────────┘
                             │ HTTP (session PHP)
                             ▼
┌──────────────────────────────────────────────────────────────┐
│  PHP MVC GreenDC Advisor                                     │
│  AiController → AiClient → AiContextBuilder / AiFallback     │
│  Calculateurs : Energy / PUE / PV / Simulation / Decision /  │
│                 Recommendation / Alert / CO₂                 │
│                         │                                    │
│                         ├── MySQL (données DC réelles)       │
│                         └── HTTP JSON (timeout + fallback)   │
└────────────────────────────┬─────────────────────────────────┘
                             ▼
┌──────────────────────────────────────────────────────────────┐
│  Python FastAPI  (rag/api.py)  :8000                         │
│  /health  /ingest  /query                                    │
│       │                                                      │
│       ├─ retriever ← embeddings ← ChromaDB                   │
│       ├─ context_builder (métier + passages)                 │
│       └─ llm.py (OpenAI-compatible ou fallback extractif)    │
└──────────────────────────────────────────────────────────────┘
                             ▲
                             │ ingest one-shot
┌────────────────────────────┴─────────────────────────────────┐
│  Corpus                                                      │
│  rag/documents/official/*.pdf   (sources primaires)          │
│  rag/documents/generated/*.md   (synthèses secondaires)      │
└──────────────────────────────────────────────────────────────┘
```

## Séparation des responsabilités

| Couche | Responsabilité |
|--------|----------------|
| Calculateurs PHP | Chiffres déterministes (PUE, kWh, PV, CO₂, scores) |
| RecommendationEngine / AlertEngine | Détection de problèmes + priorités (seuils) |
| RAG (Chroma) | Passages documentaires + métadonnées / pages |
| LLM | Explication, contextualisation, rédaction — **pas** de calcul inventé |
| AiFallback | UX continue si RAG/LLM down |

## Flux d’une question chatbot

```text
1. Utilisateur choisit un Data Center
2. PHP charge équipements, PV, recos, dernière simulation
3. AiContextBuilder compacte les métriques (pas de dump BDD)
4. AiClient POST /api/rag/query { question, business_context, history }
5. Python : embed question → top-k Chroma → prompt hybride → LLM
6. Réponse + sources (filename, page) → UI
7. Si erreur/timeout → AiFallback (règles + métriques)
```

## Mémoire conversationnelle

- Stockage session PHP (`$_SESSION['ai_chat'][data_center_id]`)
- Limite : 10 messages / DC
- Envoi au LLM : 8 derniers messages max

## Fichiers clés

### Python
- `rag/config.py`, `document_loader.py`, `chunker.py`, `embeddings.py`
- `rag/vector_store.py`, `retriever.py`, `context_builder.py`, `llm.py`
- `rag/ingest.py`, `rag/api.py`

### PHP
- `helpers/Env.php`, `AiClient.php`, `AiContextBuilder.php`, `AiFallback.php`
- `controllers/AiController.php`
- `views/ai/chat.php`, `views/ai/analyze.php`, `views/partials/ai_insights.php`

## Fallback

```text
LLM / API indisponible
  → AiFallback::analyze / insightsCard
  → Affichage déterministe + badge « fallback »
  → Jamais de page blanche
```
