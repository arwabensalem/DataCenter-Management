<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Fonctions de sécurité : XSS, CSRF, validation, redirection.
 */
final class Security
{
    /**
     * Échappe une chaîne pour l'affichage HTML (protection XSS).
     */
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Génère et stocke un jeton CSRF en session.
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Champ HTML hidden contenant le jeton CSRF.
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();

        return '<input type="hidden" name="_csrf" value="' . self::e($token) . '">';
    }

    /**
     * Vérifie la validité du jeton CSRF soumis.
     */
    public static function verifyCsrf(?string $token): bool
    {
        if ($token === null || empty($_SESSION['_csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    /**
     * Nettoie une entrée texte (trim + suppression de balises).
     */
    public static function clean(string $value): string
    {
        return trim(strip_tags($value));
    }

    /**
     * Valide une adresse e-mail.
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Redirection HTTP.
     */
    public static function redirect(string $path): never
    {
        $app = require dirname(__DIR__) . '/config/app.php';
        $base = rtrim((string) $app['url'], '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            header('Location: ' . $path);
            exit;
        }

        header('Location: ' . $base . '/' . ltrim($path, '/'));
        exit;
    }

    /**
     * Message flash (une seule lecture).
     */
    public static function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }

        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }
}
