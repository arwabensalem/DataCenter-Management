<?php

declare(strict_types=1);

namespace App\Models;

use PDOException;
use RuntimeException;

/**
 * Modèle Entreprise — CRUD et lien 1–1 avec le compte client.
 */
class Entreprise extends Model
{
    /**
     * Liste toutes les entreprises (admin) avec infos client et nb de DC.
     *
     * @return list<array<string, mixed>>
     */
    public function allWithDetails(): array
    {
        $sql = 'SELECT e.*,
                       u.nom AS client_nom,
                       u.prenom AS client_prenom,
                       u.email AS client_email,
                       u.statut AS client_statut,
                       (SELECT COUNT(*) FROM data_centers dc WHERE dc.entreprise_id = e.id) AS nb_data_centers
                FROM entreprises e
                INNER JOIN utilisateurs u ON u.id = e.utilisateur_id
                ORDER BY e.nom ASC';

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Trouve une entreprise par ID avec détails client.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT e.*,
                       u.nom AS client_nom,
                       u.prenom AS client_prenom,
                       u.email AS client_email,
                       u.telephone AS client_telephone,
                       u.statut AS client_statut
                FROM entreprises e
                INNER JOIN utilisateurs u ON u.id = e.utilisateur_id
                WHERE e.id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Entreprise liée à un utilisateur client.
     *
     * @return array<string, mixed>|null
     */
    public function findByUtilisateurId(int $utilisateurId): ?array
    {
        $sql = 'SELECT e.*,
                       u.nom AS client_nom,
                       u.prenom AS client_prenom,
                       u.email AS client_email,
                       u.telephone AS client_telephone,
                       u.statut AS client_statut
                FROM entreprises e
                INNER JOIN utilisateurs u ON u.id = e.utilisateur_id
                WHERE e.utilisateur_id = :utilisateur_id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['utilisateur_id' => $utilisateurId]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Crée un compte client + son entreprise (transaction).
     *
     * @param array<string, string> $entreprise
     * @param array<string, string> $client
     */
    public function createWithClient(array $entreprise, array $client): int
    {
        try {
            $this->db->beginTransaction();

            $sqlUser = 'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, telephone, statut)
                        VALUES (:nom, :prenom, :email, :mot_de_passe, \'client\', :telephone, \'actif\')';

            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->execute([
                'nom'          => $client['nom'],
                'prenom'       => $client['prenom'],
                'email'        => $client['email'],
                'mot_de_passe' => password_hash($client['password'], PASSWORD_BCRYPT),
                'telephone'    => $client['telephone'] !== '' ? $client['telephone'] : null,
            ]);

            $utilisateurId = (int) $this->db->lastInsertId();

            $sqlEnt = 'INSERT INTO entreprises (utilisateur_id, nom, adresse, ville, telephone, email)
                       VALUES (:utilisateur_id, :nom, :adresse, :ville, :telephone, :email)';

            $stmtEnt = $this->db->prepare($sqlEnt);
            $stmtEnt->execute([
                'utilisateur_id' => $utilisateurId,
                'nom'            => $entreprise['nom'],
                'adresse'        => $entreprise['adresse'],
                'ville'          => $entreprise['ville'],
                'telephone'      => $entreprise['telephone'],
                'email'          => $entreprise['email'],
            ]);

            $entrepriseId = (int) $this->db->lastInsertId();
            $this->db->commit();

            return $entrepriseId;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new RuntimeException('Création impossible : ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Met à jour les informations de l'entreprise.
     *
     * @param array<string, string> $data
     */
    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE entreprises
                SET nom = :nom,
                    adresse = :adresse,
                    ville = :ville,
                    telephone = :telephone,
                    email = :email
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id'        => $id,
            'nom'       => $data['nom'],
            'adresse'   => $data['adresse'],
            'ville'     => $data['ville'],
            'telephone' => $data['telephone'],
            'email'     => $data['email'],
        ]);
    }

    /**
     * Supprime une entreprise (cascade DC / équipements via FK).
     * Supprime aussi le compte client associé.
     */
    public function delete(int $id): bool
    {
        $entreprise = $this->findById($id);
        if ($entreprise === null) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare('DELETE FROM entreprises WHERE id = :id');
            $stmt->execute(['id' => $id]);

            // Suppression du compte client lié
            $stmtUser = $this->db->prepare(
                'DELETE FROM utilisateurs WHERE id = :id AND role = \'client\''
            );
            $stmtUser->execute(['id' => (int) $entreprise['utilisateur_id']]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new RuntimeException('Suppression impossible : ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Vérifie si un e-mail utilisateur est déjà pris.
     */
    public function emailUtilisateurExists(string $email, ?int $exceptUserId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM utilisateurs WHERE email = :email';
        $params = ['email' => $email];

        if ($exceptUserId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptUserId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }
}
