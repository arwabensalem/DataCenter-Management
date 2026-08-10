<?php

declare(strict_types=1);

namespace App\Models;

use PDOException;
use RuntimeException;

/**
 * Modèle Data Center — CRUD lié à une entreprise.
 */
class DataCenter extends Model
{
    /**
     * Liste tous les Data Centers (admin) avec nom d'entreprise.
     *
     * @return list<array<string, mixed>>
     */
    public function allWithEntreprise(): array
    {
        $sql = 'SELECT dc.*,
                       e.nom AS entreprise_nom,
                       g.nom AS gouvernorat_nom,
                       g.heures_ensoleillement AS gouvernorat_heures,
                       g.irradiation_kwh_m2_an,
                       g.potentiel_pv,
                       g.temperature_moyenne,
                       (SELECT COUNT(*) FROM equipements eq WHERE eq.data_center_id = dc.id) AS nb_equipements
                FROM data_centers dc
                INNER JOIN entreprises e ON e.id = dc.entreprise_id
                LEFT JOIN gouvernorats g ON g.id = dc.gouvernorat_id
                ORDER BY e.nom ASC, dc.nom ASC';

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Liste les Data Centers d'une entreprise.
     *
     * @return list<array<string, mixed>>
     */
    public function findByEntrepriseId(int $entrepriseId): array
    {
        $sql = 'SELECT dc.*,
                       e.nom AS entreprise_nom,
                       g.nom AS gouvernorat_nom,
                       g.heures_ensoleillement AS gouvernorat_heures,
                       g.irradiation_kwh_m2_an,
                       g.potentiel_pv,
                       (SELECT COUNT(*) FROM equipements eq WHERE eq.data_center_id = dc.id) AS nb_equipements
                FROM data_centers dc
                INNER JOIN entreprises e ON e.id = dc.entreprise_id
                LEFT JOIN gouvernorats g ON g.id = dc.gouvernorat_id
                WHERE dc.entreprise_id = :entreprise_id
                ORDER BY dc.nom ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['entreprise_id' => $entrepriseId]);

        return $stmt->fetchAll();
    }

    /**
     * Trouve un Data Center par ID.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT dc.*,
                       e.nom AS entreprise_nom,
                       e.utilisateur_id AS entreprise_utilisateur_id,
                       e.ville AS entreprise_ville,
                       g.nom AS gouvernorat_nom,
                       g.heures_ensoleillement AS gouvernorat_heures,
                       g.irradiation_kwh_m2_an,
                       g.potentiel_pv,
                       g.temperature_moyenne,
                       (SELECT COUNT(*) FROM equipements eq WHERE eq.data_center_id = dc.id) AS nb_equipements
                FROM data_centers dc
                INNER JOIN entreprises e ON e.id = dc.entreprise_id
                LEFT JOIN gouvernorats g ON g.id = dc.gouvernorat_id
                WHERE dc.id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Crée un Data Center.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO data_centers
                (entreprise_id, nom, localisation, gouvernorat_id, surface_totale, surface_disponible_pv, prix_kwh_steg, heures_fonctionnement)
                VALUES
                (:entreprise_id, :nom, :localisation, :gouvernorat_id, :surface_totale, :surface_disponible_pv, :prix_kwh_steg, :heures_fonctionnement)';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'entreprise_id'         => $data['entreprise_id'],
                'nom'                   => $data['nom'],
                'localisation'          => $data['localisation'],
                'gouvernorat_id'        => $data['gouvernorat_id'],
                'surface_totale'        => $data['surface_totale'],
                'surface_disponible_pv' => $data['surface_disponible_pv'],
                'prix_kwh_steg'         => $data['prix_kwh_steg'],
                'heures_fonctionnement' => $data['heures_fonctionnement'],
            ]);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new RuntimeException('Création du Data Center impossible : ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Met à jour un Data Center.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE data_centers
                SET entreprise_id = :entreprise_id,
                    nom = :nom,
                    localisation = :localisation,
                    gouvernorat_id = :gouvernorat_id,
                    surface_totale = :surface_totale,
                    surface_disponible_pv = :surface_disponible_pv,
                    prix_kwh_steg = :prix_kwh_steg,
                    heures_fonctionnement = :heures_fonctionnement
                WHERE id = :id';

        try {
            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                'id'                    => $id,
                'entreprise_id'         => $data['entreprise_id'],
                'nom'                   => $data['nom'],
                'localisation'          => $data['localisation'],
                'gouvernorat_id'        => $data['gouvernorat_id'],
                'surface_totale'        => $data['surface_totale'],
                'surface_disponible_pv' => $data['surface_disponible_pv'],
                'prix_kwh_steg'         => $data['prix_kwh_steg'],
                'heures_fonctionnement' => $data['heures_fonctionnement'],
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Mise à jour impossible : ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Supprime un Data Center (cascade équipements via FK).
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM data_centers WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
