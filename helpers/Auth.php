<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Gestion de l'authentification et des rôles (session).
 */
final class Auth
{
    private const SESSION_KEY = 'auth_user';

    /**
     * Connecte un utilisateur en session.
     *
     * @param array<string, mixed> $user
     */
    public static function login(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = [
            'id'     => (int) $user['id'],
            'nom'    => (string) $user['nom'],
            'prenom' => (string) $user['prenom'],
            'email'  => (string) $user['email'],
            'role'   => (string) $user['role'],
        ];
    }

    /**
     * Déconnecte l'utilisateur courant.
     */
    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }

    /**
     * Indique si un utilisateur est authentifié.
     */
    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]['id']);
    }

    /**
     * Force la connexion : redirige vers login si non authentifié.
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            Security::flash('error', 'Veuillez vous connecter pour continuer.');
            Security::redirect('login');
        }
    }

    /**
     * Exige un rôle précis (admin ou client).
     */
    public static function requireRole(string $role): void
    {
        self::requireLogin();

        if (self::role() !== $role) {
            Security::flash('error', 'Accès non autorisé pour votre profil.');
            Security::redirect('dashboard');
        }
    }

    /**
     * Retourne l'utilisateur connecté ou null.
     *
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    /**
     * Identifiant de l'utilisateur connecté.
     */
    public static function id(): ?int
    {
        $user = self::user();
        return $user !== null ? (int) $user['id'] : null;
    }

    /**
     * Rôle de l'utilisateur connecté.
     */
    public static function role(): ?string
    {
        $user = self::user();
        return $user['role'] ?? null;
    }

    /**
     * Vérifie si l'utilisateur est administrateur.
     */
    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    /**
     * Vérifie si l'utilisateur est client.
     */
    public static function isClient(): bool
    {
        return self::role() === 'client';
    }

    /**
     * Nom complet pour l'affichage.
     */
    public static function fullName(): string
    {
        $user = self::user();
        if ($user === null) {
            return '';
        }

        return trim($user['prenom'] . ' ' . $user['nom']);
    }
}
