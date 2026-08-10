-- ============================================================
-- GreenDC Advisor — Script SQL complet
-- Base de données normalisée MySQL / MariaDB (XAMPP)
-- ============================================================

CREATE DATABASE IF NOT EXISTS greendc_advisor
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE greendc_advisor;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS recommandations;
DROP TABLE IF EXISTS simulations;
DROP TABLE IF EXISTS installations_pv;
DROP TABLE IF EXISTS equipements;
DROP TABLE IF EXISTS data_centers;
DROP TABLE IF EXISTS entreprises;
DROP TABLE IF EXISTS types_panneaux;
DROP TABLE IF EXISTS regions_ensoleillement;
DROP TABLE IF EXISTS utilisateurs;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Table : utilisateurs
-- ------------------------------------------------------------
CREATE TABLE utilisateurs (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom               VARCHAR(100) NOT NULL,
    prenom            VARCHAR(100) NOT NULL,
    email             VARCHAR(191) NOT NULL,
    mot_de_passe      VARCHAR(255) NOT NULL,
    role              ENUM('admin', 'client') NOT NULL DEFAULT 'client',
    telephone         VARCHAR(30) NULL,
    statut            ENUM('actif', 'inactif') NOT NULL DEFAULT 'actif',
    date_creation     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_utilisateurs_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : entreprises
-- ------------------------------------------------------------
CREATE TABLE entreprises (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id   INT UNSIGNED NOT NULL,
    nom              VARCHAR(150) NOT NULL,
    adresse          VARCHAR(255) NOT NULL,
    ville            VARCHAR(100) NOT NULL,
    telephone        VARCHAR(30) NOT NULL,
    email            VARCHAR(191) NOT NULL,
    date_creation    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_entreprises_utilisateur (utilisateur_id),
    CONSTRAINT fk_entreprises_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : data_centers
-- ------------------------------------------------------------
CREATE TABLE data_centers (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entreprise_id           INT UNSIGNED NOT NULL,
    nom                     VARCHAR(150) NOT NULL,
    localisation            VARCHAR(255) NOT NULL,
    surface_totale          DECIMAL(12,2) NOT NULL,
    surface_disponible_pv   DECIMAL(12,2) NOT NULL,
    prix_kwh_steg           DECIMAL(10,4) NOT NULL,
    heures_fonctionnement   DECIMAL(5,2) NOT NULL,
    date_creation           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_data_centers_entreprise
        FOREIGN KEY (entreprise_id) REFERENCES entreprises(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT chk_dc_surfaces
        CHECK (surface_disponible_pv >= 0 AND surface_totale >= surface_disponible_pv),
    CONSTRAINT chk_dc_prix
        CHECK (prix_kwh_steg > 0),
    CONSTRAINT chk_dc_heures
        CHECK (heures_fonctionnement > 0 AND heures_fonctionnement <= 24)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : equipements
-- ------------------------------------------------------------
CREATE TABLE equipements (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    data_center_id          INT UNSIGNED NOT NULL,
    nom                     VARCHAR(150) NOT NULL,
    categorie               ENUM(
                                'serveur',
                                'switch',
                                'routeur',
                                'firewall',
                                'stockage',
                                'ups',
                                'climatisation'
                            ) NOT NULL,
    fabricant               VARCHAR(100) NOT NULL,
    modele                  VARCHAR(100) NOT NULL,
    quantite                INT UNSIGNED NOT NULL DEFAULT 1,
    puissance_watts         DECIMAL(12,2) NOT NULL,
    taux_utilisation        DECIMAL(5,2) NOT NULL,
    heures_fonctionnement   DECIMAL(5,2) NOT NULL,
    date_creation           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_equipements_data_center
        FOREIGN KEY (data_center_id) REFERENCES data_centers(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT chk_eq_quantite
        CHECK (quantite >= 1),
    CONSTRAINT chk_eq_puissance
        CHECK (puissance_watts > 0),
    CONSTRAINT chk_eq_taux
        CHECK (taux_utilisation >= 0 AND taux_utilisation <= 100),
    CONSTRAINT chk_eq_heures
        CHECK (heures_fonctionnement > 0 AND heures_fonctionnement <= 24)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : types_panneaux (catalogue)
-- ------------------------------------------------------------
CREATE TABLE types_panneaux (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL,
    puissance_wc    DECIMAL(10,2) NOT NULL,
    rendement       DECIMAL(5,2) NOT NULL,
    prix_unitaire   DECIMAL(12,2) NOT NULL,
    surface_m2      DECIMAL(8,4) NOT NULL,
    statut          ENUM('actif', 'inactif') NOT NULL DEFAULT 'actif',
    CONSTRAINT chk_tp_puissance CHECK (puissance_wc > 0),
    CONSTRAINT chk_tp_rendement CHECK (rendement > 0 AND rendement <= 100),
    CONSTRAINT chk_tp_prix CHECK (prix_unitaire > 0),
    CONSTRAINT chk_tp_surface CHECK (surface_m2 > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : installations_pv
-- ------------------------------------------------------------
CREATE TABLE installations_pv (
    id                         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    data_center_id             INT UNSIGNED NOT NULL,
    type_panneau_id            INT UNSIGNED NOT NULL,
    heures_ensoleillement      DECIMAL(5,2) NOT NULL,
    puissance_necessaire_kwc   DECIMAL(12,4) NOT NULL,
    nombre_panneaux            INT UNSIGNED NOT NULL,
    surface_necessaire         DECIMAL(12,2) NOT NULL,
    production_annuelle_kwh    DECIMAL(14,2) NOT NULL,
    taux_couverture            DECIMAL(6,2) NOT NULL,
    energie_pv_kwh             DECIMAL(14,2) NOT NULL,
    energie_steg_kwh           DECIMAL(14,2) NOT NULL,
    cout_installation          DECIMAL(14,2) NOT NULL,
    roi_pourcentage            DECIMAL(10,2) NOT NULL,
    temps_amortissement        DECIMAL(8,2) NOT NULL,
    date_calcul                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_installations_pv_dc (data_center_id),
    CONSTRAINT fk_installations_pv_data_center
        FOREIGN KEY (data_center_id) REFERENCES data_centers(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_installations_pv_type_panneau
        FOREIGN KEY (type_panneau_id) REFERENCES types_panneaux(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT chk_ipv_ensoleillement
        CHECK (heures_ensoleillement > 0 AND heures_ensoleillement <= 24)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : regions_ensoleillement
-- ------------------------------------------------------------
CREATE TABLE regions_ensoleillement (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ville                   VARCHAR(100) NOT NULL,
    heures_ensoleillement   DECIMAL(5,2) NOT NULL,
    UNIQUE KEY uq_regions_ville (ville),
    CONSTRAINT chk_reg_heures
        CHECK (heures_ensoleillement > 0 AND heures_ensoleillement <= 24)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : simulations
-- ------------------------------------------------------------
CREATE TABLE simulations (
    id                          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    data_center_id              INT UNSIGNED NOT NULL,
    utilisateur_id              INT UNSIGNED NOT NULL,
    nom                         VARCHAR(150) NOT NULL,
    description                 TEXT NULL,
    parametres_avant            JSON NOT NULL,
    parametres_apres            JSON NOT NULL,
    energie_economisee          DECIMAL(14,2) NOT NULL,
    pourcentage_reduction       DECIMAL(6,2) NOT NULL,
    cout_economise              DECIMAL(14,2) NOT NULL,
    reduction_dependance_steg   DECIMAL(6,2) NOT NULL,
    co2_evite                   DECIMAL(14,2) NOT NULL,
    date_simulation             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_simulations_data_center
        FOREIGN KEY (data_center_id) REFERENCES data_centers(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_simulations_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : recommandations
-- ------------------------------------------------------------
CREATE TABLE recommandations (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    data_center_id    INT UNSIGNED NOT NULL,
    type              VARCHAR(80) NOT NULL,
    priorite          ENUM('basse', 'moyenne', 'haute') NOT NULL DEFAULT 'moyenne',
    message           TEXT NOT NULL,
    date_generation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recommandations_data_center
        FOREIGN KEY (data_center_id) REFERENCES data_centers(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    KEY idx_recommandations_dc_priorite (data_center_id, priorite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Données de référence (seed)
-- ============================================================

-- Admin par défaut
-- Email    : admin@greendc.tn
-- Mot de passe : Admin@2026
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, telephone, statut)
VALUES (
    'Administrateur',
    'GreenDC',
    'admin@greendc.tn',
    '$2y$10$C4Nwmku.QFNFS4HOyelUEuTfBhOTzShX42W4t46m08IbpH1iw2gkK',
    'admin',
    '71000000',
    'actif'
);

-- Types de panneaux (exemples Tunisie / marché standard)
INSERT INTO types_panneaux (nom, puissance_wc, rendement, prix_unitaire, surface_m2, statut) VALUES
('Monocristallin 400 Wc', 400.00, 20.50, 650.00, 1.9000, 'actif'),
('Monocristallin 450 Wc', 450.00, 21.00, 720.00, 2.1000, 'actif'),
('Polycristallin 330 Wc', 330.00, 17.50, 480.00, 1.9500, 'actif'),
('Bifacial 500 Wc',       500.00, 22.00, 890.00, 2.2000, 'actif');

-- Ensoleillement moyen journalier (Tunisie — valeurs de référence indicatives)
INSERT INTO regions_ensoleillement (ville, heures_ensoleillement) VALUES
('Tunis',      5.50),
('Sfax',       6.00),
('Sousse',     5.80),
('Gabès',      6.20),
('Gafsa',      6.50),
('Tozeur',     6.80),
('Bizerte',    5.20),
('Nabeul',     5.60),
('Kairouan',   6.10),
('Medenine',   6.40);

-- ============================================================
-- Fin du script
-- ============================================================
