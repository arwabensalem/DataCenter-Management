# GreenDC Advisor — Modèle Conceptuel de Données (MCD)

## Entités

### UTILISATEUR
Compte d'accès à la plateforme.

| Attribut        | Description                          |
|-----------------|--------------------------------------|
| id_utilisateur  | Identifiant unique                   |
| nom             | Nom de famille                       |
| prenom          | Prénom                               |
| email           | Adresse e-mail (unique)              |
| mot_de_passe    | Mot de passe hashé                   |
| role            | Administrateur ou Client             |
| telephone       | Téléphone (optionnel)                |
| statut          | Actif / Inactif                      |
| date_creation   | Date de création du compte           |

### ENTREPRISE
Organisation cliente possédant un ou plusieurs Data Centers.

| Attribut        | Description                          |
|-----------------|--------------------------------------|
| id_entreprise   | Identifiant unique                   |
| nom             | Raison sociale                       |
| adresse         | Adresse postale                      |
| ville           | Ville                                |
| telephone       | Téléphone                            |
| email           | E-mail de contact                    |
| date_creation   | Date d'enregistrement                |

### DATA_CENTER
Infrastructure informatique d'une entreprise.

| Attribut                 | Description                                      |
|--------------------------|--------------------------------------------------|
| id_data_center           | Identifiant unique                               |
| nom                      | Nom du Data Center                               |
| localisation             | Adresse / site                                   |
| surface_totale           | Surface totale (m²)                              |
| surface_disponible_pv    | Surface disponible pour panneaux PV (m²)         |
| prix_kwh_steg            | Prix du kWh STEG (TND)                           |
| heures_fonctionnement    | Heures de fonctionnement par jour                |
| date_creation            | Date de création                                 |

### EQUIPEMENT
Matériel énergétique installé dans un Data Center.

| Attribut                 | Description                                      |
|--------------------------|--------------------------------------------------|
| id_equipement            | Identifiant unique                               |
| nom                      | Nom de l'équipement                              |
| categorie                | Serveur, Switch, Routeur, Firewall, Stockage, UPS, Climatisation |
| fabricant                | Fabricant                                        |
| modele                   | Modèle                                           |
| quantite                 | Nombre d'unités                                  |
| puissance_watts          | Puissance unitaire (W)                           |
| taux_utilisation         | Taux moyen d'utilisation (%)                     |
| heures_fonctionnement    | Heures de fonctionnement / jour                  |

> Les consommations quotidienne, mensuelle et annuelle sont **calculées** (jamais stockées en saisie).

### TYPE_PANNEAU
Catalogue des types de panneaux photovoltaïques.

| Attribut           | Description                         |
|--------------------|-------------------------------------|
| id_type_panneau    | Identifiant unique                  |
| nom                | Libellé commercial                  |
| puissance_wc       | Puissance crête (Wc)                |
| rendement          | Rendement (%)                       |
| prix_unitaire      | Prix unitaire (TND)                 |
| surface_m2         | Surface occupée par panneau (m²)    |

### INSTALLATION_PV
Dimensionnement photovoltaïque associé à un Data Center.

| Attribut                 | Description                                      |
|--------------------------|--------------------------------------------------|
| id_installation_pv       | Identifiant unique                               |
| heures_ensoleillement    | Heures moyennes d'ensoleillement / jour          |
| puissance_necessaire_kwc | Puissance PV nécessaire calculée                 |
| nombre_panneaux          | Nombre de panneaux calculé                       |
| surface_necessaire       | Surface nécessaire (m²)                          |
| production_annuelle_kwh  | Production annuelle estimée                      |
| taux_couverture          | % de couverture des besoins                      |
| energie_pv_kwh           | Énergie fournie par le PV                        |
| energie_steg_kwh         | Énergie restante STEG                            |
| cout_installation        | Coût estimatif                                   |
| roi_pourcentage          | Retour sur investissement (%)                    |
| temps_amortissement      | Temps d'amortissement (années)                   |
| date_calcul              | Date du calcul                                   |

### REGION_ENSOLEILLEMENT
Référentiel des heures d'ensoleillement par ville / région.

| Attribut                 | Description                                      |
|--------------------------|--------------------------------------------------|
| id_region                | Identifiant unique                               |
| ville                    | Nom de la ville                                  |
| heures_ensoleillement    | Heures moyennes / jour                           |

### SIMULATION
Scénario de comparaison AVANT / APRÈS pour un Data Center.

| Attribut           | Description                                          |
|--------------------|------------------------------------------------------|
| id_simulation      | Identifiant unique                                   |
| nom                | Nom du scénario                                      |
| description        | Description libre                                    |
| parametres_avant   | État de référence (JSON)                             |
| parametres_apres   | État simulé (JSON)                                   |
| energie_economisee | kWh économisés                                       |
| pourcentage_reduction | % de réduction                                    |
| cout_economise     | Coût économisé (TND)                                 |
| reduction_dependance_steg | Réduction dépendance STEG (%)                   |
| co2_evite          | Émissions CO₂ évitées (kg)                           |
| date_simulation    | Date de la simulation                                |

### RECOMMANDATION
Suggestion générée par le moteur de règles métiers.

| Attribut              | Description                                   |
|-----------------------|-----------------------------------------------|
| id_recommandation     | Identifiant unique                            |
| type                  | Code / catégorie de la règle                  |
| priorite              | basse / moyenne / haute                       |
| message               | Texte de la recommandation                    |
| date_generation       | Date de génération                            |

---

## Associations

```
UTILISATEUR (1,1) —— gère / appartient —— (0,1) ENTREPRISE
    Un client est lié à une entreprise.
    Un administrateur n'a pas d'entreprise.

ENTREPRISE (1,n) —— possède —— (1,1) DATA_CENTER
    Une entreprise possède 1 à n Data Centers.
    Un Data Center appartient à une seule entreprise.

DATA_CENTER (1,n) —— contient —— (1,1) EQUIPEMENT
    Un Data Center contient 0 à n équipements.
    Un équipement appartient à un seul Data Center.

DATA_CENTER (1,1) —— dimensionne —— (0,1) INSTALLATION_PV
    Un Data Center peut avoir une installation PV courante.
    Une installation PV concerne un seul Data Center.

TYPE_PANNEAU (1,n) —— utilisé —— (1,1) INSTALLATION_PV
    Un type de panneau peut être utilisé par plusieurs installations.
    Une installation utilise un seul type de panneau.

DATA_CENTER (1,n) —— fait l'objet de —— (1,1) SIMULATION
    Un Data Center peut avoir plusieurs simulations.
    Une simulation concerne un seul Data Center.

UTILISATEUR (1,n) —— réalise —— (1,1) SIMULATION
    Un utilisateur peut réaliser plusieurs simulations.

DATA_CENTER (1,n) —— génère —— (1,1) RECOMMANDATION
    Un Data Center peut avoir plusieurs recommandations.
```

---

## Diagramme textuel (MCD)

```
┌──────────────┐       gère        ┌──────────────┐
│ UTILISATEUR  │◄─────────────────►│  ENTREPRISE  │
└──────────────┘                   └──────┬───────┘
                                          │ possède
                                          ▼
                                   ┌──────────────┐
                                   │ DATA_CENTER  │
                                   └──────┬───────┘
            ┌─────────────┬───────────────┼───────────────┬─────────────┐
            │ contient    │ dimensionne   │ simule        │ génère      │
            ▼             ▼               ▼               ▼             │
     ┌────────────┐ ┌─────────────┐ ┌────────────┐ ┌────────────────┐   │
     │ EQUIPEMENT │ │INSTALLATION │ │ SIMULATION │ │ RECOMMANDATION │   │
     └────────────┘ │     _PV     │ └────────────┘ └────────────────┘   │
                    └──────┬──────┘                                     │
                           │ utilise                                    │
                           ▼                                            │
                    ┌─────────────┐         référence                   │
                    │TYPE_PANNEAU │◄────────────────────────────────────┘
                    └─────────────┘         (via ville / localisation)

                    ┌─────────────────────┐
                    │REGION_ENSOLEILLEMENT │  (référentiel)
                    └─────────────────────┘
```
