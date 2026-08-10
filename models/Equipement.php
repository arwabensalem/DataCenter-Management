<?php

declare(strict_types=1);

namespace App\Models;

use PDOException;
use RuntimeException;

/**
 * Modèle Équipement — CRUD lié à un Data Center.
 * Les consommations sont calculées hors BDD (EnergyCalculator).
 */
class Equipement extends Model
{
    /**
     * Liste tous les équipements (admin) avec contexte DC / entreprise.
     *
     * @return list<array<string, mixed>>
     */
    public function allWithContext(): array
    {
        $sql = 'SELECT eq.*,
                       dc.nom AS data_center_nom,
                       dc.entreprise_id,
                       e.nom AS entreprise_nom,
                       e.utilisateur_id AS entreprise_utilisateur_id
                FROM equipements eq
                INNER JOIN data_centers dc ON dc.id = eq.data_center_id
                INNER JOIN entreprises e ON e.id = dc.entreprise_id
                ORDER BY e.nom ASC, dc.nom ASC, eq.nom ASC';

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Équipements d'un Data Center.
     *
     * @return list<array<string, mixed>>
     */
    public function findByDataCenterId(int $dataCenterId): array
    {
        $sql = 'SELECT eq.*,
                       dc.nom AS data_center_nom,
                       dc.entreprise_id,
                       e.nom AS entreprise_nom,
                       e.utilisateur_id AS entreprise_utilisateur_id
                FROM equipements eq
                INNER JOIN data_centers dc ON dc.id = eq.data_center_id
                INNER JOIN entreprises e ON e.id = dc.entreprise_id
                WHERE eq.data_center_id = :data_center_id
                ORDER BY eq.categorie ASC, eq.nom ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['data_center_id' => $dataCenterId]);

        return $stmt->fetchAll();
    }

    /**
     * Équipements accessibles à un client (via son entreprise).
     *
     * @return list<array<string, mixed>>
     */
    public function findByUtilisateurId(int $utilisateurId): array
    {
        $sql = 'SELECT eq.*,
                       dc.nom AS data_center_nom,
                       dc.entreprise_id,
                       e.nom AS entreprise_nom,
                       e.utilisateur_id AS entreprise_utilisateur_id
                FROM equipements eq
                INNER JOIN data_centers dc ON dc.id = eq.data_center_id
                INNER JOIN entreprises e ON e.id = dc.entreprise_id
                WHERE e.utilisateur_id = :utilisateur_id
                ORDER BY dc.nom ASC, eq.nom ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['utilisateur_id' => $utilisateurId]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT eq.*,
                       dc.nom AS data_center_nom,
                       dc.entreprise_id,
                       dc.prix_kwh_steg,
                       e.nom AS entreprise_nom,
                       e.utilisateur_id AS entreprise_utilisateur_id
                FROM equipements eq
                INNER JOIN data_centers dc ON dc.id = eq.data_center_id
                INNER JOIN entreprises e ON e.id = dc.entreprise_id
                WHERE eq.id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO equipements
                (data_center_id, nom, categorie, fabricant, modele, quantite,
                 puissance_watts, taux_utilisation, heures_fonctionnement)
                VALUES
                (:data_center_id, :nom, :categorie, :fabricant, :modele, :quantite,
                 :puissance_watts, :taux_utilisation, :heures_fonctionnement)';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'data_center_id'        => $data['data_center_id'],
                'nom'                   => $data['nom'],
                'categorie'             => $data['categorie'],
                'fabricant'             => $data['fabricant'],
                'modele'                => $data['modele'],
                'quantite'              => $data['quantite'],
                'puissance_watts'       => $data['puissance_watts'],
                'taux_utilisation'      => $data['taux_utilisation'],
                'heures_fonctionnement' => $data['heures_fonctionnement'],
            ]);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new RuntimeException('Création équipement impossible : ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE equipements
                SET data_center_id = :data_center_id,
                    nom = :nom,
                    categorie = :categorie,
                    fabricant = :fabricant,
                    modele = :modele,
                    quantite = :quantite,
                    puissance_watts = :puissance_watts,
                    taux_utilisation = :taux_utilisation,
                    heures_fonctionnement = :heures_fonctionnement
                WHERE id = :id';

        try {
            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                'id'                    => $id,
                'data_center_id'        => $data['data_center_id'],
                'nom'                   => $data['nom'],
                'categorie'             => $data['categorie'],
                'fabricant'             => $data['fabricant'],
                'modele'                => $data['modele'],
                'quantite'              => $data['quantite'],
                'puissance_watts'       => $data['puissance_watts'],
                'taux_utilisation'      => $data['taux_utilisation'],
                'heures_fonctionnement' => $data['heures_fonctionnement'],
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Mise à jour équipement impossible : ' . $e->getMessage(), 0, $e);
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM equipements WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
