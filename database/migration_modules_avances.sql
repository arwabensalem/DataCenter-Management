-- ============================================================
-- GreenDC Advisor — Migration modules avancés (11–18)
-- ============================================================
USE greendc_advisor;

-- ------------------------------------------------------------
-- Table : gouvernorats (carte énergétique Tunisie)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gouvernorats (
    id                          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code                        VARCHAR(10) NOT NULL,
    nom                         VARCHAR(100) NOT NULL,
    latitude                    DECIMAL(10,6) NOT NULL,
    longitude                   DECIMAL(10,6) NOT NULL,
    irradiation_kwh_m2_an       DECIMAL(8,2) NOT NULL COMMENT 'Irradiation moyenne annuelle',
    heures_ensoleillement       DECIMAL(5,2) NOT NULL COMMENT 'Heures moyennes / jour',
    potentiel_pv                ENUM('faible','moyen','eleve','excellent') NOT NULL DEFAULT 'moyen',
    temperature_moyenne         DECIMAL(5,2) NULL COMMENT 'Température moyenne annuelle °C',
    UNIQUE KEY uq_gouvernorats_code (code),
    UNIQUE KEY uq_gouvernorats_nom (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lien Data Center → gouvernorat
SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'greendc_advisor'
      AND TABLE_NAME = 'data_centers'
      AND COLUMN_NAME = 'gouvernorat_id'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE data_centers ADD COLUMN gouvernorat_id INT UNSIGNED NULL AFTER localisation,
     ADD CONSTRAINT fk_dc_gouvernorat FOREIGN KEY (gouvernorat_id) REFERENCES gouvernorats(id)
     ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Seed 24 gouvernorats (valeurs indicatives / littératures solaires Tunisie)
DELETE FROM gouvernorats;
INSERT INTO gouvernorats
(code, nom, latitude, longitude, irradiation_kwh_m2_an, heures_ensoleillement, potentiel_pv, temperature_moyenne)
VALUES
('TN-11', 'Tunis',      36.806500, 10.181500, 1750.00, 5.50, 'moyen',     18.5),
('TN-12', 'Ariana',     36.862500, 10.195600, 1760.00, 5.50, 'moyen',     18.3),
('TN-13', 'Ben Arous',  36.747300, 10.230000, 1770.00, 5.55, 'moyen',     18.6),
('TN-14', 'Manouba',    36.808100, 10.097200, 1765.00, 5.50, 'moyen',     18.4),
('TN-21', 'Nabeul',     36.456100, 10.737600, 1820.00, 5.60, 'eleve',     19.0),
('TN-22', 'Zaghouan',   36.402900, 10.142900, 1800.00, 5.55, 'eleve',     18.2),
('TN-23', 'Bizerte',    37.274400,  9.873900, 1680.00, 5.20, 'moyen',     17.8),
('TN-31', 'Béja',       36.725600,  9.181700, 1720.00, 5.35, 'moyen',     18.0),
('TN-32', 'Jendouba',   36.501100,  8.780200, 1700.00, 5.30, 'moyen',     17.5),
('TN-33', 'Le Kef',     36.174200,  8.704900, 1780.00, 5.50, 'moyen',     17.2),
('TN-34', 'Siliana',    36.084970,  9.370820, 1790.00, 5.55, 'eleve',     17.8),
('TN-41', 'Kairouan',   35.678100, 10.096300, 1950.00, 6.10, 'eleve',     20.5),
('TN-42', 'Kasserine',  35.167600,  8.836500, 2000.00, 6.30, 'excellent', 18.8),
('TN-43', 'Sidi Bouzid',35.038200,  9.485800, 1980.00, 6.20, 'excellent', 19.5),
('TN-51', 'Sousse',     35.825600, 10.641100, 1880.00, 5.80, 'eleve',     19.8),
('TN-52', 'Monastir',   35.777000, 10.826200, 1900.00, 5.90, 'eleve',     20.0),
('TN-53', 'Mahdia',     35.504700, 11.062200, 1920.00, 5.95, 'eleve',     20.2),
('TN-61', 'Sfax',       34.739800, 10.760000, 1980.00, 6.00, 'excellent', 20.5),
('TN-71', 'Gafsa',      34.425000,  8.784200, 2100.00, 6.50, 'excellent', 20.8),
('TN-72', 'Tozeur',     33.919700,  8.133500, 2200.00, 6.80, 'excellent', 22.5),
('TN-73', 'Kébili',     33.704400,  8.969000, 2180.00, 6.70, 'excellent', 22.0),
('TN-81', 'Gabès',      33.881500, 10.098200, 2050.00, 6.20, 'excellent', 21.0),
('TN-82', 'Médenine',   33.354900, 10.505500, 2080.00, 6.40, 'excellent', 21.5),
('TN-83', 'Tataouine',  32.929700, 10.451800, 2150.00, 6.60, 'excellent', 21.8);

-- Associer le DC existant (Tunis) au gouvernorat Tunis si présent
UPDATE data_centers dc
INNER JOIN gouvernorats g ON g.nom = 'Tunis'
SET dc.gouvernorat_id = g.id
WHERE dc.gouvernorat_id IS NULL AND (dc.localisation LIKE '%Tunis%' OR dc.localisation LIKE '%Lac%');
