<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Charge une vue PHP avec des variables injectées.
 */
final class View
{
    /**
     * Affiche une vue (chemin relatif à /views, sans extension).
     *
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $viewsPath = dirname(__DIR__) . '/views/';
        $file = $viewsPath . str_replace('.', '/', $view) . '.php';

        if (!is_file($file)) {
            http_response_code(500);
            echo 'Vue introuvable : ' . Security::e($view);
            exit;
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = $viewsPath . str_replace('.', '/', $layout) . '.php';

        if (!is_file($layoutFile)) {
            echo $content;
            return;
        }

        require $layoutFile;
    }
}
