# GreenDC Advisor

**Plateforme d'aide à la décision** pour le dimensionnement photovoltaïque et l'optimisation énergétique des Data Centers, enrichie d'un **assistant AI + RAG**.

Stack : **PHP 8 (MVC OOP)** · **MySQL** · **Bootstrap 5** · **Chart.js** · **Python FastAPI** (RAG) · **Ollama** (LLM local optionnel)

**Dépôt :** [github.com/arwabensalem/DataCenter-Management](https://github.com/arwabensalem/DataCenter-Management)

[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/arwabensalem/DataCenter-Management)

---

## Table des matières

1. [Présentation](#1-présentation)
2. [Fonctionnalités](#2-fonctionnalités)
3. [Prérequis](#3-prérequis)
4. [Installation locale (XAMPP)](#4-installation-locale-xampp)
5. [GreenDC AI + RAG](#5-greendc-ai--rag)
6. [Déploiement gratuit (Render)](#6-déploiement-gratuit-render)
7. [Connexion (identifiants)](#7-connexion-identifiants)
8. [Guide des interfaces](#8-guide-des-interfaces)
9. [Architecture](#9-architecture)
10. [Base de données](#10-base-de-données)
11. [Rôles et droits](#11-rôles-et-droits)
12. [Formules métier](#12-formules-métier)
13. [Sécurité](#13-sécurité)
14. [Dépannage](#14-dépannage)

---

## 1. Présentation

GreenDC Advisor s'adresse aux entreprises possédant un ou plusieurs Data Centers. Ce n'est **pas un simple tableau de bord** : c'est un **système d'aide à la décision** qui permet de :

- collecter les caractéristiques techniques des Data Centers ;
- estimer automatiquement leur consommation énergétique ;
- dimensionner une installation photovoltaïque adaptée ;
- comparer production solaire et consommation électrique ;
- estimer la dépendance au réseau **STEG** ;
- simuler des scénarios d'optimisation ;
- générer des recommandations et un diagnostic global ;
- produire des rapports et exports (PDF / Excel) ;
- **dialoguer avec GreenDC AI Advisor** (données métier + documents RAG).

Les consommations, le ROI, le PUE, le CO₂ évité, etc. sont **toujours calculés automatiquement** — l'IA **explique** et contextualise, elle ne remplace pas les calculateurs.

---

## 2. Fonctionnalités

| Module | Description |
|--------|-------------|
| Authentification | Connexion / déconnexion, rôles Admin & Client |
| Entreprises | CRUD entreprises + création du compte client lié |
| Data Centers | Surfaces, prix kWh STEG, heures, **gouvernorat** |
| Équipements | Inventaire + conso jour/mois/an calculées |
| Photovoltaïque | Dimensionnement, couverture, ROI, amortissement |
| Simulations | Scénarios AVANT / APRÈS personnalisés |
| Recommandations | Moteur de règles métiers + explications IA |
| **AI Advisor** | Chatbot contextuel + analyse énergétique + sources RAG |
| Tableau de bord | KPI + Chart.js + alertes + PUE + **AI Insights** |
| Rapports | Rapport imprimable / PDF |
| Carte énergétique | 24 gouvernorats tunisiens (Leaflet) |
| Scénarios | Comparaison A/B/C + meilleur scénario |
| Aide à la décision | Diagnostic global + impact CO₂ |
| Exports | Excel multi-feuilles + PDF |

---

## 3. Prérequis

### Application web

- **XAMPP** (Apache + MySQL + PHP **8.0+** : `pdo_mysql`, `mbstring`, `json`, `curl`)
- `mod_rewrite` Apache
- Navigateur moderne

### AI / RAG (optionnel en local)

- Python **3.10+** (venv dans `rag/.venv`)
- Ollama (recommandé) avec un modèle léger, ex. `llama3.2:latest`
- Voir aussi [`RAG_SETUP.md`](RAG_SETUP.md) et [`AI_ARCHITECTURE.md`](AI_ARCHITECTURE.md)

---

## 4. Installation locale (XAMPP)

### 4.1 Placer le projet

```text
C:\xampp\htdocs\Cert
```

URL de base : `http://localhost/Cert`  
(si autre dossier → `config/app.php` ou variable `APP_URL`)

### 4.2 Démarrer XAMPP

Apache + MySQL.

### 4.3 Créer la base

Importer dans l'ordre :

1. `database/greendc_advisor.sql`
2. `database/migration_modules_avances.sql`

```bash
mysql -u root < database/greendc_advisor.sql
mysql -u root greendc_advisor < database/migration_modules_avances.sql
```

### 4.4 Configuration

- MySQL : `config/db.php` **ou** fichier `.env` (copier `.env.example`)
- Variables utiles : `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `APP_URL`, `RAG_API_URL`

### 4.5 Docker local (alternative)

```bash
docker compose up --build
```

Application : [http://localhost:8080](http://localhost:8080)

---

## 5. GreenDC AI + RAG

Architecture hybride :

```text
PHP (calculateurs + MySQL)  →  AiClient  →  FastAPI RAG  →  ChromaDB + LLM (Ollama)
                                     ↘ fallback déterministe si RAG/LLM down
```

### Démarrage local du service RAG

```powershell
cd C:\xampp\htdocs\Cert
python -m venv rag\.venv
.\rag\.venv\Scripts\pip.exe install -r rag\requirements.txt
.\rag\.venv\Scripts\python.exe -m rag.ingest
.\rag\.venv\Scripts\python.exe -m rag.api
```

Dans `.env` :

```env
RAG_API_URL=http://127.0.0.1:8000
LLM_API_KEY=ollama
LLM_MODEL=llama3.2:latest
LLM_BASE_URL=http://127.0.0.1:11434/v1
```

Interface : menu **AI Advisor** (`/ai`).

Sur le **plan gratuit Render**, le RAG Python (embeddings) est trop lourd pour 512 Mo : l'app déploie l'UI complète et l'AI Advisor fonctionne en **mode fallback** (règles + métriques). Branchez `RAG_API_URL` vers une instance RAG séparée si besoin.

---

## 6. Déploiement gratuit (Render)

Le fichier [`render.yaml`](render.yaml) décrit un service **Web Docker** (plan **Free**) :

- PHP 8.2 + Apache  
- MariaDB embarquée (données réinitialisées à chaque redéploiement free)  
- Import automatique du schéma SQL au premier démarrage  

### Déployer en 1 clic

1. Pousser ce dépôt sur GitHub (déjà le cas).  
2. Ouvrir :  
   **[Deploy to Render](https://render.com/deploy?repo=https://github.com/arwabensalem/DataCenter-Management)**  
3. Créer un compte Render (gratuit) → **Apply** le Blueprint.  
4. Attendre le build (plusieurs minutes).  
5. Ouvrir l'URL `https://greendc-advisor-xxxx.onrender.com`.

> Les services gratuits **s'endorment** après ~15 min d'inactivité : le premier chargement suivant peut prendre 30–60 s.

### Comptes après déploiement

```text
Admin  →  admin@greendc.tn   /  Admin@2026
Client →  client@techdata.tn /  Client@2026
```

### Limites du free tier

| Point | Comportement |
|-------|----------------|
| Cold start | Oui (service endormi) |
| Disque | Éphémère (re-import SQL au reboot) |
| RAG / Ollama | Non inclus (mémoire) → fallback AI |
| SSL | Fourni par Render |

Documentation d'architecture IA : [`AI_ARCHITECTURE.md`](AI_ARCHITECTURE.md).

---

## 7. Connexion (identifiants)

```text
Admin  →  admin@greendc.tn   /  Admin@2026
Client →  client@techdata.tn /  Client@2026
Local  →  http://localhost/Cert/login
```

---

## 8. Guide des interfaces

Voir la section détaillée historique ci-dessous et les écrans : Dashboard, Data Centers, Équipements, PV, Simulations, Recommandations, **AI Advisor**, Carte, Scénarios, Decision, Exports.

### AI Advisor

- Sélection du Data Center  
- Questions en français (PUE, cooling, PV, CO₂…)  
- Sources documentaires affichées sous la réponse  
- Analyse complète : `/ai/analyze/{id}`  
- Bloc **AI Insights** sur le dashboard  

---

## 9. Architecture

```text
index.php → Router → Controllers → Models (PDO) / Helpers (calculateurs)
                 ↘ Views (Bootstrap)
helpers/Ai* → HTTP → rag/api.py (optionnel)
```

Autoload : namespaces `App\Controllers`, `App\Models`, `App\Helpers`, `App\Config`.

Schéma AI : [`AI_ARCHITECTURE.md`](AI_ARCHITECTURE.md) · Plan : [`RAG_INTEGRATION_PLAN.md`](RAG_INTEGRATION_PLAN.md)

---

## 10. Base de données

**Nom :** `greendc_advisor`

| Table | Contenu |
|-------|---------|
| `utilisateurs` | Comptes admin / client |
| `entreprises` | 1 entreprise ↔ 1 compte client |
| `data_centers` | Infrastructures (+ `gouvernorat_id`) |
| `equipements` | Matériel énergétique |
| `types_panneaux` | Catalogue PV |
| `installations_pv` | Dimensionnement (1 par DC) |
| `regions_ensoleillement` | Villes |
| `gouvernorats` | Carte énergétique Tunisie |
| `simulations` | Scénarios AVANT/APRÈS (JSON) |
| `recommandations` | Sorties du moteur de règles |

Docs : `database/mcd.md`, `database/mld.md`.

---

## 11. Rôles et droits

| Action | Admin | Client |
|--------|-------|--------|
| Toutes les entreprises | Oui | La sienne |
| Créer entreprise + compte | Oui | Non |
| Tous les Data Centers | Oui | Les siens |
| AI Advisor / analyse | Oui | Ses DC |
| Supprimer une entreprise | Oui | Non |

---

## 12. Formules métier

### Consommation

```text
kWh/jour = (Puissance_W × taux%/100 × quantité × heures) / 1000
kWh/an   = kWh/jour × 365
```

### Photovoltaïque

```text
Production panneau/an = (Wc × heures_ensoleillement × 365 × rendement%) / 100000
Couverture % = min(100, Production / Conso_an × 100)
ROI % = (Économie_annuelle / Coût_install) × 100
```

### PUE

```text
PUE = Énergie totale / Énergie IT
```

| PUE | Niveau |
|-----|--------|
| ≤ 1,2 | Excellent |
| ≤ 1,5 | Bon |
| ≤ 2,0 | Moyen |
| > 2,0 | Faible |

### CO₂

Facteur par défaut : **0,55 kg CO₂ / kWh** (`CO2_KG_PER_KWH` / `config/app.php`).

---

## 13. Sécurité

- Mots de passe hashés (`password_hash`)
- Sessions sécurisées + CSRF + XSS escaping
- PDO préparé
- Secrets via `.env` (jamais commités) — clés LLM hors JS
- Accès HTTP bloqué sur `config/`, `models/`, `controllers/`, `helpers/`, `database/`

---

## 14. Dépannage

| Problème | Solution |
|----------|----------|
| Page blanche / BDD | MySQL démarré + `DB_*` / `config/db.php` |
| 404 `/Cert/login` | `mod_rewrite` + `.htaccess` |
| CSS absents | `APP_URL` / `config/app.php` → `'url'` |
| AI en fallback | Démarrer `python -m rag.api` + Ollama ; vérifier `RAG_API_URL` |
| Render cold start | Attendre 30–60 s au réveil |
| Build Render OOM | Image web seule (sans venv RAG) — déjà le cas |

### Regénérer le mot de passe admin

```bash
php -r "echo password_hash('NouveauMotDePasse', PASSWORD_BCRYPT);"
```

---

## Licence & contexte

Projet pédagogique / professionnel — **GreenDC Advisor**  
Aide à la décision énergétique pour Data Centers en Tunisie (réseau STEG).

Les valeurs d'irradiation, d'ensoleillement et de facteur CO₂ sont **indicatives**.
