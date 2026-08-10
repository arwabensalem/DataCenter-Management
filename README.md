# GreenDC Advisor

**Plateforme intelligente d'aide à la décision** pour le dimensionnement photovoltaïque et l'optimisation énergétique des Data Centers.

Application web professionnelle développée en **PHP 8 (OOP)**, **MySQL**, **HTML5**, **CSS3**, **JavaScript**, **Bootstrap 5** et **Chart.js**, selon une architecture **MVC**.

**Dépôt :** [github.com/arwabensalem/DataCenter-Management](https://github.com/arwabensalem/DataCenter-Management)

---

## Table des matières

1. [Présentation](#1-présentation)
2. [Fonctionnalités](#2-fonctionnalités)
3. [Prérequis](#3-prérequis)
4. [Installation](#4-installation)
5. [Connexion (identifiants)](#5-connexion-identifiants)
6. [Guide détaillé des interfaces](#6-guide-détaillé-des-interfaces)
7. [Architecture du projet](#7-architecture-du-projet)
8. [Base de données](#8-base-de-données)
9. [Rôles et droits](#9-rôles-et-droits)
10. [Formules métier](#10-formules-métier)
11. [Sécurité](#11-sécurité)
12. [Dépannage](#12-dépannage)

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
- produire des rapports et exports (PDF / Excel).

Les consommations, le ROI, le PUE, le CO₂ évité, etc. sont **toujours calculés automatiquement** — l'utilisateur ne les saisit jamais.

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
| Recommandations | Moteur de règles métiers (sans IA externe) |
| Tableau de bord | KPI + graphiques Chart.js + alertes + PUE |
| Rapports | Rapport imprimable / PDF |
| Carte énergétique | 24 gouvernorats tunisiens (Leaflet) |
| Scénarios | Comparaison A/B/C + meilleur scénario |
| Aide à la décision | Diagnostic global + impact CO₂ |
| Exports | Excel multi-feuilles + PDF |

---

## 3. Prérequis

- **XAMPP** (ou équivalent) avec :
  - Apache
  - MySQL / MariaDB
  - PHP **8.0+** (extensions : `pdo_mysql`, `mbstring`, `json`)
- Navigateur moderne (Chrome, Edge, Firefox)
- Module Apache `mod_rewrite` activé (pour les URLs propres)

---

## 4. Installation

### 4.1 Placer le projet

Le projet doit se trouver dans :

```text
C:\xampp\htdocs\Cert
```

URL de base prévue : `http://localhost/Cert`

> Si vous changez le dossier, modifiez `config/app.php` → clé `'url'`.

### 4.2 Démarrer XAMPP

1. Ouvrir **XAMPP Control Panel**
2. Démarrer **Apache** et **MySQL**

### 4.3 Créer la base de données

**Option A — phpMyAdmin**

1. Aller sur [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Importer dans l'ordre :
   1. `database/greendc_advisor.sql` (schéma + données de base)
   2. `database/migration_modules_avances.sql` (gouvernorats + modules avancés)

**Option B — ligne de commande**

```bash
mysql -u root < C:\xampp\htdocs\Cert\database\greendc_advisor.sql
mysql -u root greendc_advisor < C:\xampp\htdocs\Cert\database\migration_modules_avances.sql
```

### 4.4 Configurer MySQL (si besoin)

Fichier : `config/db.php`

```php
'host'   => '127.0.0.1',
'dbname' => 'greendc_advisor',
'user'   => 'root',
'pass'   => '',          // mot de passe MySQL XAMPP (souvent vide)
```

### 4.5 Lancer l'application

Ouvrir :

```text
http://localhost/Cert/login
```

---

## 5. Connexion (identifiants)

### Compte Administrateur

| Champ | Valeur |
|-------|--------|
| **URL** | [http://localhost/Cert/login](http://localhost/Cert/login) |
| **E-mail** | `admin@greendc.tn` |
| **Mot de passe** | `Admin@2026` |
| **Rôle** | Administrateur |

L'admin gère **toutes** les entreprises, Data Centers, équipements, etc.

### Compte Client (démo)

| Champ | Valeur |
|-------|--------|
| **E-mail** | `client@techdata.tn` |
| **Mot de passe** | `Client@2026` |
| **Rôle** | Client (entreprise **TechData Tunisia**) |

Le client ne voit et ne modifie que **ses** données (son entreprise et ses Data Centers).

### Créer un nouveau client

1. Se connecter en **admin**
2. Menu **Entreprises** → **Nouvelle entreprise**
3. Remplir les infos entreprise **et** le compte client (e-mail + mot de passe)
4. Un compte `role = client` est créé automatiquement et lié à l'entreprise

---

## 6. Guide détaillé des interfaces

Toutes les URLs ci-dessous sont relatives à `http://localhost/Cert`.  
Exemple : `/dashboard` → `http://localhost/Cert/dashboard`.

### Navigation globale

Après connexion, le layout principal (`views/layouts/main.php`) affiche :

- **Menu latéral** : Tableau de bord, Entreprises / Mon entreprise, Data Centers, Équipements, Photovoltaïque, Simulations, Recommandations, Rapports, puis la section **Avancé** (Carte énergétique, Scénarios, Aide à la décision, Exports).
- **En-tête** : titre de la page, badge de rôle (Administrateur / Client), nom de l'utilisateur connecté.
- **Pied de menu** : bouton **Déconnexion** (`GET /logout`).

L'**admin** voit toutes les données ; le **client** uniquement son entreprise et ses Data Centers.

### Parcours recommandé

```text
1. Connexion
2. Créer / vérifier l'entreprise
3. Créer un Data Center (+ choisir le gouvernorat sur la carte)
4. Ajouter les équipements
5. Dimensionner le photovoltaïque
6. Lancer des simulations / comparer les scénarios
7. Générer les recommandations & le diagnostic
8. Consulter le dashboard
9. Exporter le rapport PDF / Excel
```

---

### 6.1 Connexion

| | |
|---|---|
| **Routes** | `GET /`, `GET /login` · `POST /login` |
| **Accès** | Public (déjà connecté → redirection vers le dashboard) |
| **Layout** | Invité (`layouts/guest`) |

**Contenu de l'écran**

- Logo et titre **GreenDC Advisor**
- Sous-titre : aide à la décision énergétique pour Data Centers
- Messages d'erreur ou de succès (identifiants invalides, déconnexion, etc.)

**Champs du formulaire**

| Champ | Obligatoire | Description |
|-------|-------------|-------------|
| E-mail | Oui | Identifiant de connexion |
| Mot de passe | Oui | Mot de passe du compte |
| Jeton CSRF | Automatique | Protection anti-falsification |

**Actions**

- **Se connecter** → validation → redirection vers `/dashboard`
- Accès refusé si e-mail / mot de passe incorrects

---

### 6.2 Tableau de bord

| | |
|---|---|
| **Route** | `GET /dashboard` |
| **API graphiques** | `GET /dashboard/charts` |
| **Accès** | Admin & Client (données filtrées selon le rôle) |

**Bannière d'accueil**

- Salutation personnalisée (prénom / nom)
- Raccourcis : **Diagnostic** (`/decision`) et **Recommandations** (`/recommandations`)

**Alertes intelligentes** (`AlertEngine`)

Cartes colorées (danger / warning) signalant par Data Center :

- consommation annuelle élevée ;
- serveur particulièrement gourmand ;
- absence de dimensionnement PV ;
- couverture solaire trop faible ;
- PUE dégradé ;
- autres anomalies détectées automatiquement.

**Indicateurs PUE** (`PueCalculator`)

Pour chaque Data Center : valeur PUE + badge de qualité :

| PUE | Niveau |
|-----|--------|
| ≤ 1,2 | Excellent |
| ≤ 1,5 | Bon |
| ≤ 2,0 | Moyen |
| > 2,0 | Faible |

**Cartes KPI** (`DashboardAggregator`)

| Indicateur | Unité / remarque |
|------------|------------------|
| Nombre de Data Centers | — |
| Consommation annuelle | kWh |
| Production PV annuelle | kWh |
| Couverture PV | % |
| Énergie STEG annuelle | kWh |
| Coût annuel STEG | TND |
| ROI moyen | % |
| Amortissement | années |
| CO₂ évité / an | kg |
| Entreprises *(admin)* ou Équipements *(client)* | — |
| Simulations | nombre |
| Recommandations | nombre |

**Graphiques Chart.js**

1. **Consommation & production mensuelles** — saisonnalité typique Tunisie  
2. **Mix énergétique** — part Solaire vs STEG  
3. **Production vs consommation** — comparaison par Data Center  
4. **Répartition par catégorie** — serveurs, clim, UPS, etc.

**Liste**

- Dernières recommandations (priorité + message) avec lien « Tout voir »

---

### 6.3 Entreprises

#### Liste — `GET /entreprises`

| Rôle | Comportement |
|------|----------------|
| **Admin** | Tableau de toutes les entreprises |
| **Client** | Redirection automatique vers la fiche « Mon entreprise » |

**Colonnes (admin)** : Nom, Ville, Contact client, Téléphone, nombre de Data Centers.

**Actions** : Voir · Modifier · Supprimer · bouton **Nouvelle entreprise**.

#### Création — `GET /entreprises/create` · `POST /entreprises/store`

**Accès : admin uniquement.**

Deux blocs dans le même formulaire :

**1. Entreprise**

| Champ | Obligatoire |
|-------|-------------|
| Nom | Oui |
| Adresse | Oui |
| Ville | Oui |
| Téléphone | Oui |
| E-mail | Oui |

**2. Compte client lié** (créé automatiquement)

| Champ | Obligatoire |
|-------|-------------|
| Prénom | Oui |
| Nom | Oui |
| E-mail de login | Oui |
| Téléphone | Non |
| Mot de passe (≥ 8 caractères) | Oui |
| Confirmation mot de passe | Oui |

#### Fiche — `GET /entreprises/{id}`

- Coordonnées de l'entreprise
- Informations du compte client (contact, login, téléphone, statut **Actif / Inactif**)
- Boutons **Modifier** ; **Supprimer** (admin uniquement)
- Lien vers les Data Centers de l'entreprise

#### Édition — `GET /entreprises/{id}/edit` · `POST …/update`

- Même formulaire entreprise (sans recreation du compte client)
- Le client peut modifier sa propre fiche
- Suppression réservée à l'admin (`POST …/delete`)

---

### 6.4 Data Centers

#### Liste — `GET /data-centers`

**Colonnes** : Nom, Entreprise *(admin)*, Localisation, Surface totale, Surface PV, Prix kWh, heures/jour, nombre d'équipements.

**Actions** : Voir · Modifier · Supprimer · **Nouveau Data Center**.

#### Formulaire — `…/create` · `…/edit`

| Champ | Défaut / note |
|-------|----------------|
| Entreprise * | Select (admin) ou figée (client) |
| Nom * | — |
| Localisation * | — |
| **Gouvernorat** * | Détermine heures d'ensoleillement + irradiation ; lien vers la carte |
| Surface totale (m²) * | — |
| Surface PV disponible (m²) * | — |
| Prix kWh STEG * | Défaut `0,2500` TND |
| Heures de fonctionnement / jour * | Défaut `24` |

Aide contextuelle affichée à droite du formulaire.

#### Fiche — `GET /data-centers/{id}`

- KPI : surfaces, prix kWh, heures de fonctionnement
- Pourcentage de surface dédiée au PV + barre de potentiel
- Lien **Gérer les équipements** (`/equipements?data_center_id=…`)
- Lien vers l'entreprise (admin)

---

### 6.5 Équipements

#### Liste — `GET /equipements` (filtre optionnel `?data_center_id=`)

**KPI en tête de page** (totaux calculés) :

- Consommation journalière (kWh)
- Consommation mensuelle (kWh)
- Consommation annuelle (kWh)

**Filtre** : liste déroulante par Data Center.

**Tableau** : Équipement (fabricant · modèle), Catégorie, Data Center, Quantité, Puissance (W), Taux d'utilisation (%), kWh/jour, kWh/an.

**Catégories disponibles** : Serveur, Switch, Routeur, Firewall, Stockage, UPS, Climatisation.

**Formule appliquée automatiquement** :

```text
kWh/jour = (Puissance_W × taux%/100 × quantité × heures) / 1000
kWh/mois = kWh/jour × 30
kWh/an   = kWh/jour × 365
```

#### Formulaire de création / édition

| Champ | Défaut |
|-------|--------|
| Data Center * | — |
| Nom * | — |
| Catégorie * | — |
| Fabricant * | — |
| Modèle * | — |
| Quantité * | — |
| Puissance (W) * | — |
| Taux d'utilisation (%) * | 70 |
| Heures / jour * | 24 |

**Aperçu en direct (non saisissable)** : consommation jour / mois / an mise à jour pendant la saisie.

#### Fiche détail — `GET /equipements/{id}`

- KPI de consommation
- **Coût annuel STEG** estimé (conso × prix kWh du DC)
- Fiche technique complète + rappel de la formule
- Liens vers le Data Center et le prix kWh

---

### 6.6 Photovoltaïque

#### Liste — `GET /photovoltaique`

Pour chaque Data Center :

| Information | Description |
|-------------|-------------|
| Surface PV | m² disponibles |
| Statut | **Dimensionné** ou **À dimensionner** |
| Couverture | % de la conso couverte par le solaire |
| ROI | % annuel |

Actions : **Résultats** / **Dimensionner** / **Recalculer**.

#### Dimensionnement — `GET /photovoltaique/dimensionner/{id}` · `POST …/calculer/{id}`

**Contexte affiché** : consommation annuelle du DC, surface PV, prix kWh STEG.

**Champs**

| Champ | Description |
|-------|-------------|
| Type de panneau * | Catalogue (Wc, prix unitaire, rendement) |
| Ville / région | Préremplit les heures d'ensoleillement |
| Heures d'ensoleillement / jour * | Modifiable manuellement |

**Aperçu JavaScript en temps réel**

- Nombre de panneaux (limité par la surface)
- Puissance installée (kWc)
- Surface occupée
- Production annuelle
- Couverture %
- Énergie STEG restante
- Coût d'installation
- ROI / an et durée d'amortissement
- Alerte si la surface est insuffisante

**Calculs serveur** (`PvCalculator`) : panneaux, production, couverture, mix PV/STEG, coût, économie, ROI, amortissement, CO₂ évité.

#### Résultats — `GET /photovoltaique/resultats/{id}`

**KPI** : nombre de panneaux, puissance kWc, couverture %, CO₂ évité.

**Bilan énergétique** : barre visuelle Solaire / STEG.

**Indicateurs financiers** : coût d'installation, économie annuelle, ROI, amortissement, prix du panneau, rendement, date du calcul.

**Actions** : Recalculer · Supprimer l'installation (`POST …/delete/{id}`).

---

### 6.7 Simulations AVANT / APRÈS

#### Liste — `GET /simulations`

**Colonnes** : Nom du scénario, Data Center, Entreprise *(admin)*, Énergie économisée, Réduction %, Coût économisé, CO₂ évité, Date.

**Actions** : Voir · Supprimer · **Nouvelle simulation**.

#### Création — `GET /simulations/create` · `POST /simulations/store`

Déroulement en étapes :

1. **Sélection du Data Center**
2. **Nom** * et **Description** du scénario
3. Panneau **AVANT** (snapshot automatique) : conso, STEG, couverture, panneaux, dépendance STEG, coût
4. Panneau **APRÈS** — leviers modifiables :
   - ajouter des serveurs (qté, W, taux, heures) ;
   - supprimer ou réduire un équipement ;
   - remplacer (nouvelle puissance / taux) ;
   - ajouter ou fixer le nombre de panneaux ;
   - changer la ville / les heures d'ensoleillement.

**Lancer la simulation** (`SimulationEngine`) → redirection vers la fiche détail.

#### Détail — `GET /simulations/{id}`

**KPI** : énergie économisée, % de réduction, coût économisé, baisse de dépendance STEG (points), CO₂ évité.

**Tableaux comparatifs AVANT vs APRÈS** : consommation, production PV, énergie solaire / STEG, couverture, dépendance, panneaux, ensoleillement, coût STEG.

---

### 6.8 Recommandations

#### Index — `GET /recommandations`

- Bouton **Analyser tous les Data Centers** (`POST /recommandations/generer-tout`)
- Cartes par DC : **Voir** · **Générer** (`POST …/generer/{id}`)
- Tableau des dernières recommandations : Priorité (haute / moyenne / basse), DC, Entreprise *(admin)*, Message, Date

#### Par Data Center — `GET /recommandations/{id}`

- Compteurs : priorité haute · moyenne · total
- Cartes : message + type + date
- Bouton **Régénérer**

**Règles du moteur** (`RecommendationEngine`) — exemples :

- serveurs sous-utilisés (&lt; 40 %) ;
- équipements énergivores (≥ 25 % de la conso) ;
- climatisation trop lourde (≥ 30 %) ;
- surface PV sous-exploitée ;
- couverture solaire insuffisante ;
- remplacement de serveurs conseillé ;
- puissance PV à augmenter / dépendance STEG trop élevée.

---

### 6.9 Rapports

#### Liste — `GET /rapports`

Tableau des Data Centers (+ entreprise pour l'admin) avec bouton **Générer le rapport** (ouverture dans un nouvel onglet).

#### Rapport imprimable — `GET /rapports/{id}`

Layout dédié à l'impression (`layouts/print`).

**Barre d'outils** : Retour · **Imprimer / PDF** (impression navigateur).

**Sections du document**

1. Entreprise  
2. Data Center  
3. Consommation (KPI + tableau équipements + coût STEG)  
4. Installation photovoltaïque  
5. Simulations  
6. Recommandations  
7. Graphiques (les 4 charts du dashboard)  
8. Conclusion (`DecisionEngine` : scénario recommandé + diagnostic textuel)

---

### 6.10 Carte énergétique

| | |
|---|---|
| **Route** | `GET /carte` |
| **API détail** | `GET /carte/api/{id}` |
| **Accès** | Admin & Client |

**Contenu**

- Carte interactive **Leaflet** centrée sur la Tunisie (24 gouvernorats)
- Panneau latéral de détail au clic : irradiation, heures d'ensoleillement, potentiel PV, température moyenne
- Bouton **Créer un Data Center ici** → `/data-centers/create?gouvernorat_id=…`
- Tableau des gouvernorats cliquable (h/j, kWh/m², potentiel : Excellent / Élevé / Moyen / Faible)

> Nécessite une connexion Internet pour les tuiles OpenStreetMap et le CDN Leaflet.

---

### 6.11 Scénarios (comparaison A / B / C)

#### Liste — `GET /scenarios`

Liste des Data Centers (+ gouvernorat) avec bouton **Comparer**.

#### Comparaison — `GET /scenarios/{id}`

Scénarios générés automatiquement (`DecisionEngine` + `SimulationEngine`) :

| Scénario | Contenu |
|----------|---------|
| **A** | Infrastructure actuelle |
| **B** | Ajout de panneaux PV |
| **C** | Serveurs économes + PV |
| **D–F** | Jusqu'à 3 simulations sauvegardées (si existantes) |

**Tableau comparatif** : consommation, production PV, couverture, énergie STEG, coût, ROI, amortissement, CO₂, **score décisionnel**.

Le meilleur scénario est mis en surbrillance, avec classement et diagnostic textuel.

---

### 6.12 Aide à la décision

#### Index — `GET /decision`

Cartes Data Center avec bouton **Lancer le diagnostic**.

#### Diagnostic — `GET /decision/{id}`

- **Bannière de conclusion** : synthèse du diagnostic
- **PUE** : valeur + explication pédagogique
- **Impact CO₂** (`ImpactEnvironnemental`) :
  - émissions avant / après / évitées (en tonnes)
  - % de réduction
  - graphique en barres
- **Alertes** actives pour ce DC
- **Tableau des scénarios** (couverture, CO₂, score)
- **Top 8 recommandations** priorisées

---

### 6.13 Exports

| | |
|---|---|
| **Route** | `GET /exports` |
| **Excel** | `GET /exports/excel/{id}` |
| **PDF** | Redirige vers le rapport imprimable `GET /rapports/{id}` |

**Liste** des Data Centers avec deux boutons par ligne :

- **Excel** — export multi-feuilles (équipements, PV, simulations, etc.)
- **PDF** — ouvre le rapport prêt à imprimer / enregistrer en PDF

---

### Données de démonstration

Après import SQL + migration, vous disposez typiquement de :

- Entreprise **TechData Tunisia**
- Data Center **DC Tunis Lac** (gouvernorat Tunis)
- Équipements (serveurs Dell, climatisation, …)
- Dimensionnement PV + simulation + recommandations (si générés lors des tests)

---

## 7. Architecture du projet

```text
Cert/
├── index.php                 # Front Controller
├── .htaccess                 # Rewrite vers index.php
├── config/
│   ├── app.php               # Paramètres applicatifs
│   ├── db.php                # Identifiants MySQL
│   ├── Database.php          # Singleton PDO
│   ├── bootstrap.php         # Session + autoload
│   └── Router.php            # Routes HTTP
├── controllers/              # Contrôleurs MVC
├── models/                   # Accès données (PDO)
├── views/                    # Vues PHP + layouts
├── helpers/                  # Calculs métier & sécurité
├── assets/
│   ├── css/                  # Styles (app + print)
│   ├── js/                   # Chart.js, carte Leaflet, UI
│   └── images/
└── database/
    ├── greendc_advisor.sql   # Script principal
    ├── migration_modules_avances.sql
    ├── mcd.md                # Modèle conceptuel
    └── mld.md                # Modèle logique
```

### Stack technique

- PHP 8 orienté objet, namespaces `App\`
- PDO + requêtes préparées
- Bootstrap 5 + Font Awesome
- Chart.js (graphiques)
- Leaflet (carte Tunisie)

### Moteurs de calcul

| Helper | Rôle |
|--------|------|
| `EnergyCalculator` | Consommations équipements, totaux, coût STEG |
| `PvCalculator` | Dimensionnement PV, ROI, amortissement |
| `PueCalculator` | PUE (énergie totale / énergie IT) |
| `SimulationEngine` | Snapshots AVANT / APRÈS |
| `DecisionEngine` | Scores de scénarios + diagnostic |
| `RecommendationEngine` | Règles métiers de recommandations |
| `AlertEngine` | Alertes dashboard / diagnostic |
| `ImpactEnvironnemental` | CO₂ (tonnes / %) |
| `DashboardAggregator` | Agrégation KPI + séries graphiques |
| `ExcelExport` | Export Excel multi-feuilles |

---

## 8. Base de données

**Nom :** `greendc_advisor`

### Tables principales

| Table | Contenu |
|-------|---------|
| `utilisateurs` | Comptes admin / client |
| `entreprises` | 1 entreprise ↔ 1 compte client |
| `data_centers` | Infrastructures (+ `gouvernorat_id`) |
| `equipements` | Matériel énergétique |
| `types_panneaux` | Catalogue PV |
| `installations_pv` | Dimensionnement (1 par DC) |
| `regions_ensoleillement` | Villes (référentiel historique) |
| `gouvernorats` | Carte énergétique Tunisie |
| `simulations` | Scénarios AVANT/APRÈS (JSON) |
| `recommandations` | Sorties du moteur de règles |

Documentation détaillée : `database/mcd.md` et `database/mld.md`.

---

## 9. Rôles et droits

| Action | Admin | Client |
|--------|-------|--------|
| Voir toutes les entreprises | Oui | Non (la sienne) |
| Créer entreprise + compte client | Oui | Non |
| Gérer tous les Data Centers | Oui | Uniquement les siens |
| Équipements / PV / Simulations | Oui | Ses DC uniquement |
| Carte / Scénarios / Diagnostic / Exports | Oui | Ses DC uniquement |
| Supprimer une entreprise | Oui | Non |

---

## 10. Formules métier

### Consommation équipement

```text
kWh/jour = (Puissance_W × taux%/100 × quantité × heures) / 1000
kWh/mois = kWh/jour × 30
kWh/an   = kWh/jour × 365
```

### Production photovoltaïque

```text
Production panneau/an =
  (Wc × heures_ensoleillement × 365 × rendement%) / 100000

Couverture % = min(100, Production / Conso_an × 100)
Énergie PV   = min(Production, Conso_an)
Énergie STEG = max(0, Conso_an − Énergie PV)
ROI %        = (Économie_annuelle / Coût_install) × 100
Amortissement (ans) = Coût_install / Économie_annuelle
```

### PUE

```text
PUE = Énergie totale / Énergie IT
```

- **IT** : serveurs, stockage, switch, routeur, firewall  
- **Total** : IT + climatisation + UPS + …

| PUE | Niveau |
|-----|--------|
| ≤ 1,2 | Excellent |
| ≤ 1,5 | Bon |
| ≤ 2,0 | Moyen |
| > 2,0 | Faible |

### CO₂

Facteur par défaut (Tunisie, indicatif) : **0,55 kg CO₂ / kWh**  
Configurable dans `config/app.php` → `co2_kg_per_kwh`.

---

## 11. Sécurité

- Mots de passe hashés (`password_hash` / `password_verify`)
- Sessions PHP sécurisées + régénération d'ID à la connexion
- Protection **CSRF** sur les formulaires POST
- Protection **XSS** (`htmlspecialchars`)
- PDO + requêtes préparées (anti-injection SQL)
- Accès HTTP direct bloqué sur `config/`, `models/`, `controllers/`, `helpers/`, `database/`

---

## 12. Dépannage

| Problème | Solution |
|----------|----------|
| Page blanche / erreur BDD | Vérifier MySQL démarré + `config/db.php` |
| 404 sur `/Cert/login` | Activer `mod_rewrite` Apache ; vérifier `.htaccess` |
| CSS/JS introuvables | Vérifier `config/app.php` → `'url' => '/Cert'` |
| Gouvernorats absents | Exécuter `migration_modules_avances.sql` |
| Mot de passe admin oublié | Réimporter le SQL ou regénérer un hash bcrypt en PHP |
| Carte vide | Connexion Internet (tuiles OpenStreetMap + CDN Leaflet) |

### Regénérer le mot de passe admin

```bash
php -r "echo password_hash('NouveauMotDePasse', PASSWORD_BCRYPT);"
```

Puis mettre à jour la colonne `mot_de_passe` de `utilisateurs` pour `admin@greendc.tn`.

---

## Comptes récapitulatifs

```text
Admin  →  admin@greendc.tn   /  Admin@2026
Client →  client@techdata.tn /  Client@2026
URL    →  http://localhost/Cert/login
```

---

## Licence & contexte

Projet pédagogique / professionnel — **GreenDC Advisor**  
Aide à la décision énergétique pour Data Centers en Tunisie (réseau STEG).

Les valeurs d'irradiation, d'ensoleillement et de facteur CO₂ sont **indicatives** et destinées à la démonstration et à l'aide à la décision ; elles ne remplacent pas une étude d'ingénierie détaillée.
