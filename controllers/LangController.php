<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Lang;
use App\Helpers\Security;

/**
 * Changement de langue FR / EN.
 */
class LangController extends Controller
{
    public function set(string $code): void
    {
        Lang::set($code);

        $redirect = $_GET['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? 'dashboard';
        // Empêche les redirections externes
        if (str_starts_with((string) $redirect, 'http://') || str_starts_with((string) $redirect, 'https://')) {
            $app = require dirname(__DIR__) . '/config/app.php';
            $base = rtrim((string) $app['url'], '/');
            $host = parse_url((string) $redirect, PHP_URL_HOST);
            $self = $_SERVER['HTTP_HOST'] ?? '';
            if ($host !== null && $host !== $self) {
                Security::redirect('dashboard');
            }
            // Extraire le chemin relatif
            $path = parse_url((string) $redirect, PHP_URL_PATH) ?: '';
            if ($base !== '' && str_starts_with($path, $base)) {
                $path = substr($path, strlen($base));
            }
            $redirect = trim($path, '/') ?: 'dashboard';
        }

        $redirect = ltrim((string) $redirect, '/');
        if ($redirect === '' || str_contains($redirect, 'lang/')) {
            $redirect = 'dashboard';
        }

        Security::redirect($redirect);
    }
}
