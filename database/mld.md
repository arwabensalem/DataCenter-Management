# GreenDC Advisor — Modèle Logique de Données (MLD)

## Conventions

- SGBD : MySQL 8 / MariaDB (compatible XAMPP)
- Clés primaires : `id` (INT UNSIGNED AUTO_INCREMENT)
- Clés étrangères : `nom_table_id`
- Dates : `DATETIME` / `TIMESTAMP`
- Montants et mesures : `DECIMAL`
- Soft delete éventuel via `statut` sur les utilisateurs

---

## Tables relationnelles

### utilisateurs

| Colonne          | Type              | Contraintes                          |
|------------------|-------------------|--------------------------------------|
| id               | INT UNSIGNED      | PK, AI                               |
| nom              | VARCHAR(100)      | NOT NULL                             |
| prenom           | VARCHAR(100)      | NOT NULL                             |
| email            | VARCHAR(191)      | NOT NULL, UNIQUE                     |
| mot_de_passe     | VARCHAR(255)      | NOT NULL (hash bcrypt/argon2)        |
| role             | ENUM('admin','client') | NOT NULL, DEFAULT 'client'      |
| telephone        | VARCHAR(30)       | NULL                                 |
| statut           | ENUM('actif','inactif') | NOT NULL, DEFAULT 'actif'      |
| date_creation    | DATETIME          | NOT NULL, DEFAULT CURRENT_TIMESTAMP  |
| date_modification| DATETIME          | NULL ON UPDATE CURRENT_TIMESTAMP     |

### entreprises

| Colonne          | Type              | Contraintes                          |
|------------------|-------------------|--------------------------------------|
| id               | INT UNSIGNED      | PK, AI                               |
| utilisateur_id   | INT UNSIGNED      | FK → utilisateurs(id), UNIQUE, NOT NULL |
| nom              | VARCHAR(150)      | NOT NULL                             |
| adresse          | VARCHAR(255)      | NOT NULL                             |
| ville            | VARCHAR(100)      | NOT NULL                             |
| telephone        | VARCHAR(30)       | NOT NULL                             |
| email            | VARCHAR(191)      | NOT NULL                             |
| date_creation    | DATETIME          | NOT NULL, DEFAULT CURRENT_TIMESTAMP  |

> Relation 1–1 : un compte **client** ↔ une **entreprise**.

### data_centers

| Colonne                  | Type              | Contraintes                          |
|--------------------------|-------------------|--------------------------------------|
| id                       | INT UNSIGNED      | PK, AI                               |
| entreprise_id            | INT UNSIGNED      | FK → entreprises(id), NOT NULL       |
| nom                      | VARCHAR(150)      | NOT NULL                             |
| localisation             | VARCHAR(255)      | NOT NULL                             |
| surface_totale           | DECIMAL(12,2)     | NOT NULL                             |
| surface_disponible_pv    | DECIMAL(12,2)     | NOT NULL                             |
| prix_kwh_steg            | DECIMAL(10,4)     | NOT NULL                             |
| heures_fonctionnement    | DECIMAL(5,2)      | NOT NULL                             |
| date_creation            | DATETIME          | NOT NULL, DEFAULT CURRENT_TIMESTAMP  |

### equipements

| Colonne                  | Type              | Contraintes                          |
|--------------------------|-------------------|--------------------------------------|
| id                       | INT UNSIGNED      | PK, AI                               |
| data_center_id           | INT UNSIGNED      | FK → data_centers(id), NOT NULL      |
| nom                      | VARCHAR(150)      | NOT NULL                             |
| categorie                | ENUM(...)         | NOT NULL                             |
| fabricant                | VARCHAR(100)      | NOT NULL                             |
| modele                   | VARCHAR(100)      | NOT NULL                             |
| quantite                 | INT UNSIGNED      | NOT NULL, DEFAULT 1                  |
| puissance_watts          | DECIMAL(12,2)     | NOT NULL                             |
| taux_utilisation         | DECIMAL(5,2)      | NOT NULL (0–100)                     |
| heures_fonctionnement    | DECIMAL(5,2)      | NOT NULL                             |
| date_creation            | DATETIME          | NOT NULL, DEFAULT CURRENT_TIMESTAMP  |

**ENUM categorie** :
`serveur`, `switch`, `routeur`, `firewall`, `stockage`, `ups`, `climatisation`

### types_panneaux

| Colonne          | Type              | Contraintes                          |
|------------------|-------------------|--------------------------------------|
| id               | INT UNSIGNED      | PK, AI                               |
| nom              | VARCHAR(150)      | NOT NULL                             |
| puissance_wc     | DECIMAL(10,2)     | NOT NULL                             |
| rendement        | DECIMAL(5,2)      | NOT NULL                             |
| prix_unitaire    | DECIMAL(12,2)     | NOT NULL                             |
| surface_m2       | DECIMAL(8,4)      | NOT NULL                             |
| statut           | ENUM('actif','inactif') | NOT NULL, DEFAULT 'actif'      |

### installations_pv

| Colonne                  | Type              | Contraintes                          |
|--------------------------|-------------------|--------------------------------------|
| id                       | INT UNSIGNED      | PK, AI                               |
| data_center_id           | INT UNSIGNED      | FK → data_centers(id), UNIQUE, NOT NULL |
| type_panneau_id          | INT UNSIGNED      | FK → types_panneaux(id), NOT NULL    |
| heures_ensoleillement    | DECIMAL(5,2)      | NOT NULL                             |
| puissance_necessaire_kwc | DECIMAL(12,4)     | NOT NULL                             |
| nombre_panneaux          | INT UNSIGNED      | NOT NULL                             |
| surface_necessaire       | DECIMAL(12,2)     | NOT NULL                             |
| production_annuelle_kwh  | DECIMAL(14,2)     | NOT NULL                             |
| taux_couverture          | DECIMAL(6,2)      | NOT NULL                             |
| energie_pv_kwh           | DECIMAL(14,2)     | NOT NULL                             |
| energie_steg_kwh         | DECIMAL(14,2)     | NOT NULL                             |
| cout_installation        | DECIMAL(14,2)     | NOT NULL                             |
| roi_pourcentage          | DECIMAL(10,2)     | NOT NULL                             |
| temps_amortissement      | DECIMAL(8,2)      | NOT NULL                             |
| date_calcul              | DATETIME          | NOT NULL, DEFAULT CURRENT_TIMESTAMP  |

> Un Data Center possède **au plus une** installation PV courante (UNIQUE sur `data_center_id`).

### regions_ensoleillement

| Colonne                  | Type              | Contraintes                          |
|--------------------------|-------------------|--------------------------------------|
| id                       | INT UNSIGNED      | PK, AI                               |
| ville                    | VARCHAR(100)      | NOT NULL, UNIQUE                     |
| heures_ensoleillement    | DECIMAL(5,2)      | NOT NULL                             |

### simulations

| Colonne                     | Type           | Contraintes                          |
|-----------------------------|----------------|--------------------------------------|
| id                          | INT UNSIGNED   | PK, AI                               |
| data_center_id              | INT UNSIGNED   | FK → data_centers(id), NOT NULL      |
| utilisateur_id              | INT UNSIGNED   | FK → utilisateurs(id), NOT NULL      |
| nom                         | VARCHAR(150)   | NOT NULL                             |
| description                 | TEXT           | NULL                                 |
| parametres_avant            | JSON           | NOT NULL                             |
| parametres_apres            | JSON           | NOT NULL                             |
| energie_economisee          | DECIMAL(14,2)  | NOT NULL                             |
| pourcentage_reduction       | DECIMAL(6,2)   | NOT NULL                             |
| cout_economise              | DECIMAL(14,2)  | NOT NULL                             |
| reduction_dependance_steg   | DECIMAL(6,2)   | NOT NULL                             |
| co2_evite                   | DECIMAL(14,2)  | NOT NULL                             |
| date_simulation             | DATETIME       | NOT NULL, DEFAULT CURRENT_TIMESTAMP  |

### recommandations

| Colonne           | Type              | Contraintes                          |
|-------------------|-------------------|--------------------------------------|
| id                | INT UNSIGNED      | PK, AI                               |
| data_center_id    | INT UNSIGNED      | FK → data_centers(id), NOT NULL      |
| type              | VARCHAR(80)       | NOT NULL                             |
| priorite          | ENUM('basse','moyenne','haute') | NOT NULL, DEFAULT 'moyenne' |
| message           | TEXT              | NOT NULL                             |
| date_generation   | DATETIME          | NOT NULL, DEFAULT CURRENT_TIMESTAMP  |

---

## Schéma relationnel (MLD)

```
utilisateurs (id, nom, prenom, email, mot_de_passe, role, telephone, statut, date_creation, date_modification)

entreprises (id, #utilisateur_id, nom, adresse, ville, telephone, email, date_creation)

data_centers (id, #entreprise_id, nom, localisation, surface_totale, surface_disponible_pv,
              prix_kwh_steg, heures_fonctionnement, date_creation)

equipements (id, #data_center_id, nom, categorie, fabricant, modele, quantite,
             puissance_watts, taux_utilisation, heures_fonctionnement, date_creation)

types_panneaux (id, nom, puissance_wc, rendement, prix_unitaire, surface_m2, statut)

installations_pv (id, #data_center_id, #type_panneau_id, heures_ensoleillement,
                  puissance_necessaire_kwc, nombre_panneaux, surface_necessaire,
                  production_annuelle_kwh, taux_couverture, energie_pv_kwh, energie_steg_kwh,
                  cout_installation, roi_pourcentage, temps_amortissement, date_calcul)

regions_ensoleillement (id, ville, heures_ensoleillement)

simulations (id, #data_center_id, #utilisateur_id, nom, description, parametres_avant,
             parametres_apres, energie_economisee, pourcentage_reduction, cout_economise,
             reduction_dependance_steg, co2_evite, date_simulation)

recommandations (id, #data_center_id, type, priorite, message, date_generation)
```

---

## Cardinalités (résumé)

| Relation                         | Cardinalité | Règle FK / ON DELETE      |
|----------------------------------|-------------|---------------------------|
| utilisateurs → entreprises       | 1:1         | CASCADE                   |
| entreprises → data_centers       | 1:N         | CASCADE                   |
| data_centers → equipements       | 1:N         | CASCADE                   |
| data_centers → installations_pv  | 1:1         | CASCADE                   |
| types_panneaux → installations_pv| 1:N         | RESTRICT                  |
| data_centers → simulations       | 1:N         | CASCADE                   |
| utilisateurs → simulations       | 1:N         | RESTRICT                  |
| data_centers → recommandations   | 1:N         | CASCADE                   |

---

## Formules métier (hors BDD — helpers PHP)

Ces valeurs ne sont **jamais saisies** ; elles sont calculées côté application :

```
P_effective (W)     = puissance_watts × (taux_utilisation / 100)
Conso_jour (kWh)    = (P_effective × quantite × heures_fonctionnement) / 1000
Conso_mois (kWh)    = Conso_jour × 30
Conso_an (kWh)      = Conso_jour × 365

Production_an (kWh) = (nombre_panneaux × puissance_wc × heures_ensoleillement × 365 × rendement) / (1000 × 100)
Couverture (%)      = min(100, (Production_an / Conso_an) × 100)
Energie_PV          = min(Production_an, Conso_an)
Energie_STEG        = max(0, Conso_an − Energie_PV)
Cout_install        = nombre_panneaux × prix_unitaire
Economie_anuelle    = Energie_PV × prix_kwh_steg
ROI (%)             = (Economie_anuelle / Cout_install) × 100
Amortissement (ans) = Cout_install / Economie_anuelle   (si Economie > 0)
CO₂ évité (kg)      = Energie_PV × facteur_emission_kg_par_kwh
```
