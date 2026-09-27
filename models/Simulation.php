<?php

declare(strict_types=1);

namespace App\Models;

use PDOException;
use RuntimeException;

/**
 * Scénarios de simulation AVANT / APRÈS.
 */
class Simulation extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function allWithContext(?int $utilisateurId = null): array
    {
        $sql = 'SELECT s.*,
                       dc.nom AS data_center_nom,
                       e.nom AS entreprise_nom,
                       e.utilisateur_id AS entreprise_utilisateur_id,
                       u.prenom AS auteur_prenom,
                       u.nom AS auteur_nom
                FROM simulations s
                INNER JOIN data_centers dc ON dc.id = s.data_center_id
                INNER JOIN entreprises e ON e.id = dc.entreprise_id
                INNER JOIN utilisateurs u ON u.id = s.utilisateur_id';

        if ($utilisateurId !== null) {
            $sql .= ' WHERE e.utilisateur_id = :utilisateur_id OR s.utilisateur_id = :utilisateur_id2';
        }

        $sql .= ' ORDER BY s.date_simulation DESC';

        if ($utilisateurId !== null) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'utilisateur_id'  => $utilisateurId,
                'utilisateur_id2' => $utilisateurId,
            ]);
            return $stmt->fetchAll();
        }

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT s.*,
                       dc.nom AS data_center_nom,
                       dc.entreprise_id,
                       e.nom AS entreprise_nom,
                       e.utilisateur_id AS entreprise_utilisateur_id,
                       u.prenom AS auteur_prenom,
                       u.nom AS auteur_nom
                FROM simulations s
                INNER JOIN data_centers dc ON dc.id = s.data_center_id
                INNER JOIN entreprises e ON e.id = dc.entreprise_id
                INNER JOIN utilisateurs u ON u.id = s.utilisateur_id
                WHERE s.id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        $row['parametres_avant'] = json_decode((string) $row['parametres_avant'], true) ?? [];
        $row['parametres_apres'] = json_decode((string) $row['parametres_apres'], true) ?? [];

        return $row;
    }

    /**
     * Simulations d'un Data Center (plus récentes en premier).
     *
     * @return list<array<string, mixed>>
     */
    public function findByDataCenterId(int $dataCenterId): array
    {
        $sql = 'SELECT s.*,
                       dc.nom AS data_center_nom
                FROM simulations s
                INNER JOIN data_centers dc ON dc.id = s.data_center_id
                WHERE s.data_center_id = :dc_id
                ORDER BY s.date_simulation DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['dc_id' => $dataCenterId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['parametres_avant'] = json_decode((string) ($row['parametres_avant'] ?? '{}'), true) ?? [];
            $row['parametres_apres'] = json_decode((string) ($row['parametres_apres'] ?? '{}'), true) ?? [];
        }
        unset($row);

        return $rows;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO simulations
                (data_center_id, utilisateur_id, nom, description,
                 parametres_avant, parametres_apres,
                 energie_economisee, pourcentage_reduction, cout_economise,
                 reduction_dependance_steg, co2_evite)
                VALUES
                (:data_center_id, :utilisateur_id, :nom, :description,
                 :parametres_avant, :parametres_apres,
                 :energie_economisee, :pourcentage_reduction, :cout_economise,
                 :reduction_dependance_steg, :co2_evite)';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'data_center_id'            => $data['data_center_id'],
                'utilisateur_id'            => $data['utilisateur_id'],
                'nom'                       => $data['nom'],
                'description'               => $data['description'],
                'parametres_avant'          => json_encode($data['parametres_avant'], JSON_UNESCAPED_UNICODE),
                'parametres_apres'          => json_encode($data['parametres_apres'], JSON_UNESCAPED_UNICODE),
                'energie_economisee'        => $data['energie_economisee'],
                'pourcentage_reduction'     => $data['pourcentage_reduction'],
                'cout_economise'            => $data['cout_economise'],
                'reduction_dependance_steg' => $data['reduction_dependance_steg'],
                'co2_evite'                 => $data['co2_evite'],
            ]);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new RuntimeException('Enregistrement simulation impossible : ' . $e->getMessage(), 0, $e);
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM simulations WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
