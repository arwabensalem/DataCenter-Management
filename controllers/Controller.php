<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;

/**
 * Contrôleur de base.
 */
abstract class Controller
{
    /**
     * Affiche une vue avec layout.
     *
     * @param array<string, mixed> $data
     */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        View::render($view, $data, $layout);
    }

    /**
     * Indique si la requête est en POST.
     */
    protected function isPost(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}
