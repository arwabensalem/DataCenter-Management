# GreenDC Advisor — Plan d'intégration RAG + IA

**Phase :** 1 — Audit uniquement  
**Date :** 2026-09-27  
**Statut :** En attente de validation avant PHASE 2  
**Règle :** Aucune modification fonctionnelle du code applicatif n'a été effectuée pour cet audit.

---

## 1. Architecture actuelle

### 1.1 Stack

| Élément | Réalité constatée |
|--------|-------------------|
| Langage | PHP **8.2.12** (XAMPP), exigence documentée PHP 8.0+ |
| BDD | MySQL `greendc_advisor` via PDO |
| Frontend | HTML5, Bootstrap 5 (CDN), Chart.js, Leaflet, Font Awesome |
| Architecture | MVC custom (sans Composer / sans Laravel) |
| Autoload | `config/bootstrap.php` (namespaces `App\Controllers`, `App\Models`, `App\Helpers`, `App\Config`) |
| Routing | `config/Router.php` + `index.php` + `.htaccess` |
| Python | **3.14.4** présent sur la machine ; stubs vides dans `rag/` |
| Composer | **Absent** (`composer.json` / `vendor/` inexistants) |
| `.env` | **Absent** (`.gitignore` prévoit `.env` / `.env.local`, mais pas de `.env.example`) |

### 1.2 Structure des dossiers

```text
Cert/
├── index.php                 # Front controller
├── .htaccess
├── README.md
├── config/                   # app.php, db.php, database.php, bootstrap.php, Router.php
├── controllers/              # 14 contrôleurs (+ base Controller)
├── models/                   # Accès PDO aux tables
├── helpers/                  # Calculateurs + moteurs métier (pas de dossier services/)
├── views/                    # Templates PHP + layouts
├── assets/                   # css/, js/
├── database/                 # greendc_advisor.sql, migration_modules_avances.sql, mcd/mld
└── rag/                      # Corpus documents + stubs Python vides
    ├── api.py                # vide
    ├── ingest.py             # vide
    ├── rag.py                # vide
    └── documents/
        ├── official/         # 16 PDF (sources primaires)
        └── generated/        # 12 Markdown (sources secondaires)
```

### 1.3 Flux actuel

```text
Navigateur
    ↓
index.php → Router → Controller
    ↓                    ↓
  View (HTML)      Helpers (calculs déterministes)
    ↓                    ↓
  assets/JS           Models → MySQL
```

**Pas de couche IA externe.** Les « intelligences » actuelles sont des moteurs de règles PHP.

### 1.4 Contrôleurs

| Contrôleur | Rôle |
|-----------|------|
| `AuthController` | Login / logout |
| `DashboardController` | KPIs, alertes, graphiques JSON |
| `EntrepriseController` | CRUD entreprises |
| `DataCenterController` | CRUD data centers |
| `EquipementController` | CRUD équipements |
| `PhotovoltaiqueController` | Dimensionnement PV |
| `SimulationController` | Simulations avant/après |
| `RecommandationController` | Génération / affichage recommandations |
| `RapportController` | Rapports imprimables |
| `CarteController` | Energy Map + API gouvernorat |
| `ScenarioController` | Comparaison multi-scénarios |
| `DecisionController` | Diagnostic décisionnel A/B/C |
| `ExportController` | Excel / PDF-style |

### 1.5 Helpers / calculateurs (déterministes)

| Helper | Rôle | Type de logique |
|--------|------|-----------------|
| `EnergyCalculator` | kWh jour/mois/an, totaux, catégories | Formules |
| `PvCalculator` | Production, nb panneaux, couverture, ROI | Formules |
| `PueCalculator` | PUE + classification | Formules + seuils |
| `SimulationEngine` | Snapshots + modifications + comparaison | Formules |
| `DecisionEngine` | Score pondéré + diagnostic textuel | Score + template |
| `RecommendationEngine` | Règles métiers → messages FR | if/else + sprintf |
| `AlertEngine` | Alertes dashboard | if/else + seuils |
| `ImpactEnvironnemental` | CO₂ via `co2_kg_per_kwh` | Formule |
| `DashboardAggregator` | Agrégats KPIs / charts | Agrégation |
| `ExcelExport` | Export SpreadsheetML | Export |
| `Auth`, `Security`, `View`, `Url` | Session, CSRF, vues, URLs | Infra |

### 1.6 Routes principales

- Auth : `/`, `/login`, `/logout`
- Dashboard : `/dashboard`, `/dashboard/charts` (JSON)
- CRUD : `/entreprises`, `/data-centers`, `/equipements`
- PV : `/photovoltaique/...`
- Simulations : `/simulations/...`
- Recommandations : `/recommandations/...` (+ `generer` / `generer-tout`)
- Rapports / Carte / Scénarios / Decision / Exports
- API JSON existantes : `GET /dashboard/charts`, `GET /carte/api/{id}`

### 1.7 Configuration actuelle

- `config/app.php` : nom, `url=/Cert`, timezone `Africa/Tunis`, **`co2_kg_per_kwh = 0.55`**
- `config/db.php` : MySQL local `root` / mot de passe vide (démo XAMPP)
- Aucune clé LLM, aucune URL RAG, aucun `.env` actif

---

## 2. Fonctionnalités IA / « intelligentes » existantes

**Verdict :** aucune IA générative. Tout est **heuristique locale**.

| Fonctionnalité | Fichier(s) | Nature actuelle |
|----------------|------------|-----------------|
| Recommandations | `helpers/RecommendationEngine.php`, `RecommandationController`, vues `recommandations/` | Règles locales + textes prédéfinis ; commenté « sans IA externe » |
| Alertes dashboard | `helpers/AlertEngine.php`, `DashboardController` | Seuils fixes (conso, PV, PUE, ROI…) |
| Diagnostic décision | `helpers/DecisionEngine.php`, `DecisionController`, `ScenarioController` | Score pondéré + phrase template |
| Classification PUE | `helpers/PueCalculator.php` | Bandes excellent/bon/moyen/faible |
| Scénarios A/B/C | `DecisionController`, `ScenarioController` | Scénarios hardcodés (actuel / +PV / serveurs efficients+PV) |
| Aperçus live JS | `assets/js/app.js` | Formules client (équipement / PV) |
| Corpus RAG | `rag/documents/**` | Documents prêts ; **pipeline non implémentée** |
| Stubs Python | `rag/api.py`, `ingest.py`, `rag.py` | Fichiers **vides (0 octets)** |

### 2.1 Règles locales identifiées (RecommendationEngine)

| Règle | Seuil / condition | Priorité typique |
|-------|-------------------|------------------|
| Aucun équipement | inventaire vide | haute |
| Serveurs sous-utilisés | taux utilisation &lt; 40 % | — |
| Équipements énergivores | part conso ≥ 25 % | — |
| Climatisation élevée | part clim ≥ 30 % | — |
| Couverture PV | &lt; 25 % / &lt; 50 % | — |
| Remplacement serveurs | ≥ 450 W et util ≥ 60 % | — |
| Dépendance STEG | ≥ 70 % | — |
| Surface / puissance PV | contraintes surface / sizing | — |

### 2.2 Règles locales (AlertEngine) — exemples

- Conso annuelle &gt; 500 000 kWh
- Serveur ≥ 20 % de la conso
- Pas de PV / couverture &lt; 30 %
- Amortissement &gt; 12 ans
- PUE &gt; 2.0

---

## 3. Fonctionnalités à migrer / enrichir par le RAG

| Priorité | Fonctionnalité | Migration prévue |
|----------|----------------|------------------|
| P0 | Chatbot **GreenDC AI Advisor** | Nouvelle capacité (n'existe pas) |
| P0 | Explication PUE / conso / cooling | Contexte MySQL + calculateurs + passages RAG |
| P1 | Recommandations enrichies | Seuils PHP conservés ; LLM explique / justifie / cite sources |
| P1 | AI Energy Analysis | Nouvelle analyse structurée (résumé, problèmes, actions, sources) |
| P1 | AI Insights (Dashboard) | Section dashboard alimentée par métriques réelles + résumé IA |
| P2 | Explication simulations | `SimulationEngine` calcule ; LLM explique |
| P2 | Explication PV | `PvCalculator` calcule ; LLM contextualise (ANME privilégié) |
| P2 | Analyse CO₂ | `ImpactEnvironnemental` calcule ; LLM explique leviers |
| P2 | Decision Support | Score déterministe ; LLM approfondit le diagnostic |
| P3 | Alertes dashboard | Seuils restent ; messages optionnellement enrichis |
| P3 | Mémoire conversationnelle | Nouvelle table / stockage limité |

**Principe :** ne pas remplacer les calculateurs ; **augmenter** les sorties textuelles et le raisonnement documentaire.

---

## 4. Données MySQL disponibles

### 4.1 Tables (schéma `database/greendc_advisor.sql` + migration)

| Table | Usage pour l'IA |
|-------|-----------------|
| `utilisateurs` | Auth uniquement — **ne jamais envoyer passwords / données perso inutiles au LLM** |
| `entreprises` | Contexte organisationnel minimal |
| `data_centers` | Contexte principal (surface, prix kWh STEG, heures, gouvernorat) |
| `equipements` | Consommation, catégories, top consommateurs |
| `types_panneaux` | Caractéristiques panneaux pour explications PV |
| `installations_pv` | Production, couverture, ROI, nb panneaux |
| `regions_ensoleillement` | Ensoleillement / dimensionnement |
| `gouvernorats` | Carte / irradiation Tunisie (migration) |
| `simulations` | Historique avant/après (JSON + KPIs) |
| `recommandations` | Recos existantes (`type`, `priorite`, `message`) |

### 4.2 Données calculées (non stockées systématiquement)

Issues des helpers au runtime :

- Consommation IT / cooling / totale
- PUE + classe
- CO₂ (`co2_kg_per_kwh` depuis `config/app.php`)
- Score scénario / diagnostic
- Alertes

### 4.3 Contexte métier à envoyer au LLM (exemple ciblé)

```json
{
  "data_center": {
    "id": 1,
    "name": "...",
    "total_energy_kwh": 8500,
    "it_energy_kwh": 4500,
    "cooling_energy_kwh": 2100,
    "pue": 1.89,
    "pv_production_kwh": 3000,
    "pv_coverage_pct": 35,
    "co2_kg": "...",
    "top_equipments": []
  },
  "active_recommendations": [],
  "latest_simulation": null
}
```

**Règle :** ne jamais dumper toute la base dans le prompt — sélectionner selon l'intent de la question.

---

## 5. Fichiers concernés

### 5.1 À créer (phases suivantes — non faits en Phase 1)

| Zone | Fichiers prévus |
|------|-----------------|
| Python RAG | `rag/config.py`, `document_loader.py`, `chunker.py`, `embeddings.py`, `retriever.py`, `context_builder.py`, `llm.py`, réécrire `api.py` / `ingest.py` |
| Dépendances | `rag/requirements.txt`, `rag/.env.example` (ou racine `.env.example`) |
| PHP bridge | ex. `helpers/AiClient.php`, `helpers/AiContextBuilder.php`, `helpers/AiFallback.php` |
| Contrôleurs | ex. `AiController.php` (chat, analysis, insights) |
| Vues / JS | widget chatbot, section AI Insights, panneau sources |
| DB | migration optionnelle `ai_conversations` / `ai_messages` |
| Docs | `RAG_SETUP.md`, `AI_ARCHITECTURE.md` (phases finales) |
| Tests | tests Python API + smoke PHP |

### 5.2 À réutiliser / étendre (ne pas dupliquer)

- Tous les calculateurs `helpers/*Calculator.php` et engines
- Modèles existants pour charger le contexte DC
- `views/layouts/main.php` (navbar / sidebar / Bootstrap)
- `RecommendationEngine` (seuils) + enrichissement LLM en aval
- `ImpactEnvironnemental` pour le facteur CO₂
- Routes existantes pour greffer des endpoints JSON AI

### 5.3 Corpus RAG (à conserver intact)

**Official (primaires) — 16 PDF :**  
DOE, DCOI, LBNL, Green Grid (PUE/ERE), ASHRAE/thermal, FEMP, liquid cooling, guides PV Tunisie (ANME/référentiel + installation), etc.

**Generated (secondaires) — 12 MD :**  
efficacité DC, PUE, cooling, IT, PV fondamentaux/sizing/Tunisie/maintenance, energy management, CO₂, métriques, décision.

Métadonnées à conserver à l'ingestion : `filename`, `source_type` (`official`|`generated`), `source`, `title`, `category`, `year`, `page`, `chunk_id`.

---

## 6. Architecture proposée

```text
┌─────────────────────────────────────────────────────────────┐
│  PHP MVC GreenDC Advisor (existant)                         │
│  Controllers / Models / Views / Helpers (calculateurs)      │
│         │                                                   │
│         ├── MySQL (données réelles DC)                      │
│         │                                                   │
│         └── GreenDC AI Service (nouveau bridge PHP)         │
│                   │  timeout, validation, fallback          │
│                   ▼                                         │
│         Python FastAPI RAG (rag/)                           │
│                   │                                         │
│         ┌─────────┴─────────┐                               │
│         ▼                   ▼                               │
│   ChromaDB + embeddings   LLM (via .env)                    │
│   documents official/     prompt système strict             │
│   + generated/            contexte hybride                  │
└─────────────────────────────────────────────────────────────┘
```

### 6.1 Pipeline RAG

```text
PDF/MD → extraction (PyMuPDF) → nettoyage → chunking
      → embeddings (Sentence Transformers léger)
      → ChromaDB (une fois à l'ingest)
      → query embedding → retrieval → context_builder
      → LLM → answer + sources + recommendations structurées
```

### 6.2 Endpoints Python (cible)

| Méthode | Endpoint | Rôle |
|---------|----------|------|
| `GET` | `/api/rag/health` | Santé service / index / LLM |
| `POST` | `/api/rag/ingest` | (Re)construire l'index |
| `POST` | `/api/rag/query` | Question + `data_center_id` / `user_id` → answer/sources/metrics/recommendations |

### 6.3 Endpoints PHP (cible, phases UI)

| Route | Rôle |
|-------|------|
| `POST /ai/chat` | Chatbot contextuel DC |
| `POST /ai/analyze/{id}` | AI Energy Analysis |
| `GET /ai/insights/{id}` | AI Insights dashboard (JSON ou fragment) |
| Optionnel | enrichissement reco / explication simulation / PV |

### 6.4 Fallback

```text
LLM / RAG indisponible
  → AiClient timeout/erreur
  → AiFallback utilise RecommendationEngine + AlertEngine + DecisionEngine + métriques calculées
  → UI affiche analyse déterministe + message discret « assistant IA indisponible »
  → jamais de page blanche
```

### 6.5 Sécurité

- Secrets uniquement via `.env` (jamais en PHP/JS hardcodés)
- Pas de mots de passe / tokens vers le LLM
- Validation taille question, auth session PHP, timeout HTTP
- Logs sans secrets

---

## 7. Étapes d'intégration (rappel ordonné)

| Phase | Contenu | Statut |
|-------|---------|--------|
| **1** | Audit + `RAG_INTEGRATION_PLAN.md` | **Fait** |
| **2** | Service Python RAG (loader, chunker, embeddings, ChromaDB, ingest) | **Fait** (28 docs → 2337 chunks) |
| **3** | `POST /api/rag/query` + test question simple | **Fait** |
| **4** | Connexion contexte MySQL / métriques DC | **Fait** (`AiContextBuilder`) |
| **5** | Connexion LLM + prompt système | **Fait** (`llm.py` + fallback) |
| **6** | Chatbot frontend (Bootstrap / style existant) | **Fait** (`/ai`) |
| **7** | Migration Analysis / Suggestions / Recos / Decision / Simu / PV / CO₂ | **Fait** (liens + analyse) |
| **8** | AI Insights Dashboard | **Fait** |
| **9** | Tests (RAG, API, métier, scénario PUE) | **Fait** (`rag/tests/`) |
| **10** | `RAG_SETUP.md` + `AI_ARCHITECTURE.md` | **Fait** |

---

## 8. Dépendances nécessaires

### 8.1 Python (prévu Phase 2)

- FastAPI + Uvicorn
- PyMuPDF (`fitz`)
- sentence-transformers (modèle **léger** multilingue FR/EN, ex. `paraphrase-multilingual-MiniLM-L12-v2` ou équivalent compact)
- ChromaDB
- python-dotenv, pydantic, httpx (si client LLM externe)
- pytest (tests)

### 8.2 PHP

- Extensions déjà requises : `pdo_mysql`, `mbstring`, `json`
- Client HTTP : `curl` / file_get_contents stream context (pas de Guzzle obligatoire si on évite Composer ; sinon Composer optionnel plus tard)
- Lecture `.env` légère (parser custom ou vlucas/phpdotenv si Composer accepté)

### 8.3 Environnement (exemple — à créer en Phase 2+, sans clé fictive réelle)

```env
LLM_API_KEY=
LLM_MODEL=
LLM_BASE_URL=
RAG_API_URL=http://127.0.0.1:8000
RAG_TIMEOUT_SECONDS=30
EMBEDDING_MODEL=paraphrase-multilingual-MiniLM-L12-v2
CHROMA_PATH=./rag/storage/chroma
```

### 8.4 Runtime constaté

- PHP 8.2.12 OK
- Python 3.14.4 OK (attention : vérifier compatibilité wheels sentence-transformers / torch sur Windows ; risque de compatibilité — voir §9)

---

## 9. Risques potentiels / compatibilité

| Risque | Impact | Mitigation |
|--------|--------|------------|
| Python 3.14 très récent vs libs ML | Install torch / sentence-transformers peut échouer | Tester tôt ; sinon Python 3.11/3.12 en venv dédié |
| Stubs `rag/*.py` vides | Aucun service à brancher aujourd'hui | Réécrire proprement en Phase 2 (sans supprimer documents) |
| Pas de Composer / pas de `.env` | Secrets et config LLM absents | Introduire `.env.example` + bridge PHP minimal |
| Double système de recommandations | Confusion UX | Garder seuils PHP ; LLM en couche d'explication / enrichissement |
| LLM invente chiffres | Perte de confiance | Prompt strict + injecter métriques calculateurs ; interdiction calcul LLM |
| LLM invente sources / pages | Non-conformité §11 | Ne retourner que métadonnées Chroma réelles |
| Timeout / service down | UX cassée | Fallback déterministe obligatoire |
| Contexte trop large | Coût / hallucination | Intent routing + contexte minimal |
| Mémoire chat infinie | Coût tokens | Limite N messages (ex. 10) |
| `db.php` credentials en clair | Déjà le cas en démo | Ne pas aggraver ; secrets LLM hors repo |
| Modification DB inutile | Régression | Migration SQL seulement si mémoire chat / logs AI nécessaires |
| Performance ingestion PDF | Première indexation lente | Ingest one-shot ; pas de re-embed à chaque query |

---

## 10. Fallback prévu (détail)

| Couche | Comportement si indisponible |
|--------|------------------------------|
| RAG retrieval | Répondre avec contexte métier seul + avertissement sources limitées |
| LLM | Afficher sortie `RecommendationEngine` / `AlertEngine` / métriques + message IA offline |
| Python API down | `AiClient` catch → fallback PHP local |
| DC non sélectionné | Demander sélection DC ; pas d'hallucination de données |
| Donnée manquante | Phrase explicite « information non disponible » |

---

## 11. Fonctionnalités qui doivent rester déterministes

À **ne pas** laisser le LLM remplacer :

1. `EnergyCalculator` — consommations
2. `PueCalculator` — PUE numérique
3. `PvCalculator` — sizing / production / ROI
4. `SimulationEngine` — résultats de scénarios
5. `ImpactEnvironnemental` — CO₂ et facteur d'émission
6. `DecisionEngine::score()` — score pondéré
7. Seuils de `RecommendationEngine` / `AlertEngine` (détection de problèmes)
8. Priorité des recommandations (règles explicables)
9. Auth / CSRF / ACL
10. Exports Excel / agrégats dashboard charts

Le LLM : **explique, contextualise, recommande qualitativement, cite**.

---

## 12. Prompt système (cible Phase 5)

Règles à encoder :

1. Utiliser les données DC fournies  
2. Utiliser les passages RAG fournis  
3. Ne pas inventer de données / sources / réglementations  
4. Distinguer faits vs recommandations  
5. Les chiffres numériques importants viennent des calculateurs  
6. Français par défaut ; anglais sur demande  
7. Indiquer clairement les manques d'information  
8. Citer les documents réellement récupérés  
9. Ne pas prétendre avoir calculé ce qui n'a pas été calculé  

---

## 13. Tests minimum (Phase 9)

- Ingestion PDF + Markdown + métadonnées  
- Retrieval FR  
- `/health`, `/query`, erreurs, timeout  
- Régression PUE / conso / PV / CO₂ / recos PHP  
- Scénario : « Pourquoi mon PUE est élevé ? » → utilise données DC + sources réelles + pas de valeurs inventées  

---

## 14. Synthèse exécutive

GreenDC Advisor est une application **MVC PHP mature** avec calculateurs fiables et moteurs de règles. Le dossier `rag/documents` est **prêt** ; le service Python est **à construire**. L'intégration doit être **hybride et additive** : PHP reste la source de vérité numérique ; le RAG/LLM apporte explication, justification documentaire et assistant conversationnel, avec fallback déterministe.

**Prochaine action :** validation utilisateur → démarrer **PHASE 2** (pipeline Python RAG locale).
)
