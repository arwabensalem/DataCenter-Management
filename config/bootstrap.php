<?php

declare(strict_types=1);

/**
 * Bootstrap applicatif : timezone, session, autoload.
 */

$appConfig = require __DIR__ . '/app.php';

date_default_timezone_set($appConfig['timezone']);

/**
 * Autoload PSR-4 simplifié pour le namespace App\.
 *
 * App\Config\*      → /config/
 * App\Models\*      → /models/
 * App\Controllers\* → /controllers/
 * App\Helpers\*     → /helpers/
 */
spl_autoload_register(static function (string $class): void {
    $map = [
        'App\\Config\\'      => dirname(__DIR__) . '/config/',
        'App\\Models\\'      => dirname(__DIR__) . '/models/',
        'App\\Controllers\\' => dirname(__DIR__) . '/controllers/',
        'App\\Helpers\\'     => dirname(__DIR__) . '/helpers/',
    ];

    foreach ($map as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

        if (is_file($file)) {
            require_once $file;
        }
        return;
    }
});

// Chargement .env (RAG / LLM) — après autoload
\App\Helpers\Env::load(dirname(__DIR__) . '/.env');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', (string) $appConfig['session_lifetime']);
    session_start();
}

\App\Helpers\Lang::boot();