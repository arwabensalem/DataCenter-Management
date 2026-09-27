<?php

declare(strict_types=1);

/**
 * Identifiants MySQL.
 * Priorité : variables d'environnement (.env / hébergeur) puis valeurs locales XAMPP.
 */
$env = static function (string $key, string $default = '') : string {
    $v = getenv($key);
    if ($v !== false && $v !== '') {
        return (string) $v;
    }
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return (string) $_SERVER[$key];
    }
    return $default;
};

return [
    'host'    => $env('DB_HOST', '127.0.0.1'),
    'port'    => $env('DB_PORT', '3306'),
    'dbname'  => $env('DB_NAME', 'greendc_advisor'),
    'charset' => 'utf8mb4',
    'user'    => $env('DB_USER', 'root'),
    'pass'    => $env('DB_PASS', ''),
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
