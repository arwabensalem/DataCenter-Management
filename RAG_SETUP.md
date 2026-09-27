# GreenDC Advisor — Setup du service RAG

## 1. Prérequis

- PHP 8.0+ (testé 8.2) avec `pdo_mysql`, `mbstring`, `json`, `curl`
- Python **3.10–3.12 recommandé** (3.14 OK si les wheels torch/chromadb s’installent)
- MySQL (base `greendc_advisor` déjà utilisée par l’app)

## 2. Environnement virtuel Python

```powershell
cd c:\xampp\htdocs\Cert
python -m venv rag\.venv
.\rag\.venv\Scripts\Activate.ps1
python -m pip install --upgrade pip
pip install -r rag\requirements.txt
```

## 3. Configuration `.env`

Copier le modèle (ne jamais committer `.env`) :

```powershell
copy .env.example .env
```

### Option recommandée : Ollama (local)

Si Ollama est installé (`ollama --version`) :

```env
LLM_API_KEY=ollama
LLM_MODEL=llama3.2:latest
LLM_BASE_URL=http://127.0.0.1:11434/v1
LLM_TIMEOUT_SECONDS=120
```

Vérifier qu'Ollama tourne et que le modèle est présent :

```powershell
ollama list
ollama serve   # si le service n'est pas déjà démarré
```

### Option cloud (OpenAI-compatible)

```env
LLM_API_KEY=sk-...
LLM_MODEL=gpt-4o-mini
LLM_BASE_URL=https://api.openai.com/v1
```

Sans LLM joignable, le service répond quand même via retrieval + analyse déterministe PHP.

## 4. Ingestion des documents

Les PDF officiels et Markdown générés sont dans `rag/documents/`. **Ne pas les supprimer.**

```powershell
cd c:\xampp\htdocs\Cert
.\rag\.venv\Scripts\python.exe -m rag.ingest
```

Résultat attendu : ~28 documents → ~2300+ chunks dans ChromaDB (`rag/storage/chroma`).

Réingestion (reconstruction) :

```powershell
.\rag\.venv\Scripts\python.exe -m rag.ingest --reset
```

## 5. Démarrage de l’API

```powershell
cd c:\xampp\htdocs\Cert
.\rag\.venv\Scripts\python.exe -m rag.api
```

Vérification :

```powershell
curl http://127.0.0.1:8000/api/rag/health
```

Endpoints :

| Méthode | URL | Rôle |
|---------|-----|------|
| GET | `/api/rag/health` | Santé + stats index |
| POST | `/api/rag/ingest` | Rebuild index |
| POST | `/api/rag/query` | Question + contexte métier |

Exemple query :

```json
{
  "question": "Pourquoi mon PUE est élevé ?",
  "data_center_id": 1,
  "business_context": {
    "data_center": { "name": "DC1", "pue": 1.89, "total_energy_kwh": 8500 }
  }
}
```

## 6. Connexion PHP

L’application charge `.env` via `App\Helpers\Env` au bootstrap.

Routes UI :

- `/ai` — chatbot GreenDC AI Advisor
- `/ai/analyze/{id}` — AI Energy Analysis
- `/ai/insights/{id}` — JSON insights
- Dashboard — bloc **AI Insights**

Le bridge PHP (`AiClient`) envoie le contexte métier (calculateurs + MySQL) au service Python. Si l’API est down → `AiFallback` (règles déterministes).

## 7. Tests

```powershell
# Python
cd c:\xampp\htdocs\Cert
.\rag\.venv\Scripts\python.exe -m pytest rag\tests -q

# PHP smoke (calculateurs + fallback)
php rag\tests\php_smoke.php
```

## 8. Dépannage

| Symptôme | Action |
|----------|--------|
| `Collection vide` | Lancer `python -m rag.ingest` |
| PHP en mode fallback | Vérifier que l’API tourne sur `RAG_API_URL` |
| Erreur torch / sentence-transformers | Utiliser Python 3.11/3.12 en venv |
| Première ingestion lente | Normal (téléchargement modèle HF + embed PDFs) |
| Sources inventées | Ne doit pas arriver : seules métadonnées Chroma sont renvoyées |
| LLM timeout | Augmenter `RAG_TIMEOUT_SECONDS` ou laisser la clé vide |

## 9. Sécurité

- Jamais d’API key dans le JS ou les vues PHP
- Pas de mots de passe utilisateurs dans les prompts
- Logs : `rag/logs/rag.log`, `rag/logs/php_ai.log` (sans secrets)
