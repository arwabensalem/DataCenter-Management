<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Charge un fichier .env (KEY=VALUE) dans $_ENV / putenv.
 * Ne journalise jamais les valeurs sensibles.
 */
final class Env
{
    private static bool $loaded = false;

    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }

        $path = $path ?? dirname(__DIR__) . '/.env';
        self::$loaded = true;

        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            if ($key === '') {
                continue;
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key . '=' . $value);
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        self::load();

        if (array_key_exists($key, $_ENV)) {
            return (string) $_ENV[$key];
        }

        $fromEnv = getenv($key);
        if ($fromEnv !== false) {
            return (string) $fromEnv;
        }

        return $default;
    }

    public static function getInt(string $key, int $default): int
    {
        $v = self::get($key);
        return $v !== null && $v !== '' ? (int) $v : $default;
    }

    public static function getFloat(string $key, float $default): float
    {
        $v = self::get($key);
        return $v !== null && $v !== '' ? (float) $v : $default;
    }
}
