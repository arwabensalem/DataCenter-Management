<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Construction d'URLs relatives à l'application.
 */
final class Url
{
    public static function base(): string
    {
        $app = require dirname(__DIR__) . '/config/app.php';
        return rtrim((string) $app['url'], '/');
    }

    public static function to(string $path = ''): string
    {
        $path = trim($path, '/');
        return self::base() . ($path !== '' ? '/' . $path : '');
    }

    /**
     * Indique si l'URI courante commence par le chemin donné.
     */
    public static function is(string $path): bool
    {
        $uri = trim((string) ($_GET['url'] ?? ''), '/');
        $path = trim($path, '/');

        if ($path === '') {
            return $uri === '';
        }

        return $uri === $path || str_starts_with($uri, $path . '/');
    }
}
