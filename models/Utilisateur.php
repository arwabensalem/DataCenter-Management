<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Modèle Utilisateur — authentification et gestion des comptes.
 */
class Utilisateur extends Model
{
    /**
     * Recherche un utilisateur actif par e-mail.
     *
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT id, nom, prenom, email, mot_de_passe, role, telephone, statut
                FROM utilisateurs
                WHERE email = :email
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Recherche un utilisateur par identifiant.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT id, nom, prenom, email, role, telephone, statut, date_creation
                FROM utilisateurs
                WHERE id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Vérifie les identifiants et retourne l'utilisateur si valides.
     *
     * @return array<string, mixed>|null
     */
    public function attemptLogin(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if ($user === null) {
            return null;
        }

        if ($user['statut'] !== 'actif') {
            return null;
        }

        if (!password_verify($password, $user['mot_de_passe'])) {
            return null;
        }

        // Ne jamais exposer le hash hors du modèle
        unset($user['mot_de_passe']);

        return $user;
    }
}
