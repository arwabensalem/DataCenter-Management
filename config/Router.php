<?php

declare(strict_types=1);

namespace App\Config;

use App\Controllers\AuthController;
use App\Controllers\AiController;
use App\Controllers\CarteController;
use App\Controllers\DashboardController;
use App\Controllers\DataCenterController;
use App\Controllers\DecisionController;
use App\Controllers\EntrepriseController;
use App\Controllers\EquipementController;
use App\Controllers\ExportController;
use App\Controllers\LangController;
use App\Controllers\PhotovoltaiqueController;
use App\Controllers\RapportController;
use App\Controllers\RecommandationController;
use App\Controllers\ScenarioController;
use App\Controllers\SimulationController;

/**
 * Routeur HTTP simple avec paramètres dynamiques ({id}).
 */
final class Router
{
    /** @var list<array{method: string, pattern: string, action: array{0: class-string, 1: string}}> */
    private array $routes = [];

    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
        $this->registerRoutes();
    }

    /**
     * Déclare les routes de l'application.
     */
    private function registerRoutes(): void
    {
        // Authentification
        $this->get('', [AuthController::class, 'showLogin']);
        $this->get('login', [AuthController::class, 'showLogin']);
        $this->post('login', [AuthController::class, 'login']);
        $this->get('logout', [AuthController::class, 'logout']);

        // Langue FR / EN
        $this->get('lang/{code}', [LangController::class, 'set']);

        // Tableau de bord
        $this->get('dashboard', [DashboardController::class, 'index']);
        $this->get('dashboard/charts', [DashboardController::class, 'chartsData']);

        // Entreprises
        $this->get('entreprises', [EntrepriseController::class, 'index']);
        $this->get('entreprises/create', [EntrepriseController::class, 'create']);
        $this->post('entreprises/store', [EntrepriseController::class, 'store']);
        $this->get('entreprises/{id}', [EntrepriseController::class, 'show']);
        $this->get('entreprises/{id}/edit', [EntrepriseController::class, 'edit']);
        $this->post('entreprises/{id}/update', [EntrepriseController::class, 'update']);
        $this->post('entreprises/{id}/delete', [EntrepriseController::class, 'delete']);

        // Data Centers
        $this->get('data-centers', [DataCenterController::class, 'index']);
        $this->get('data-centers/create', [DataCenterController::class, 'create']);
        $this->post('data-centers/store', [DataCenterController::class, 'store']);
        $this->get('data-centers/{id}', [DataCenterController::class, 'show']);
        $this->get('data-centers/{id}/edit', [DataCenterController::class, 'edit']);
        $this->post('data-centers/{id}/update', [DataCenterController::class, 'update']);
        $this->post('data-centers/{id}/delete', [DataCenterController::class, 'delete']);

        // Équipements
        $this->get('equipements', [EquipementController::class, 'index']);
        $this->get('equipements/create', [EquipementController::class, 'create']);
        $this->post('equipements/store', [EquipementController::class, 'store']);
        $this->get('equipements/{id}', [EquipementController::class, 'show']);
        $this->get('equipements/{id}/edit', [EquipementController::class, 'edit']);
        $this->post('equipements/{id}/update', [EquipementController::class, 'update']);
        $this->post('equipements/{id}/delete', [EquipementController::class, 'delete']);

        // Photovoltaïque
        $this->get('photovoltaique', [PhotovoltaiqueController::class, 'index']);
        $this->get('photovoltaique/dimensionner/{id}', [PhotovoltaiqueController::class, 'dimensionner']);
        $this->post('photovoltaique/calculer/{id}', [PhotovoltaiqueController::class, 'calculer']);
        $this->get('photovoltaique/resultats/{id}', [PhotovoltaiqueController::class, 'resultats']);
        $this->post('photovoltaique/delete/{id}', [PhotovoltaiqueController::class, 'delete']);

        // Simulations
        $this->get('simulations', [SimulationController::class, 'index']);
        $this->get('simulations/create', [SimulationController::class, 'create']);
        $this->post('simulations/store', [SimulationController::class, 'store']);
        $this->get('simulations/{id}', [SimulationController::class, 'show']);
        $this->post('simulations/{id}/delete', [SimulationController::class, 'delete']);

        // Recommandations
        $this->get('recommandations', [RecommandationController::class, 'index']);
        $this->post('recommandations/generer-tout', [RecommandationController::class, 'genererTout']);
        $this->post('recommandations/generer/{id}', [RecommandationController::class, 'generer']);
        $this->get('recommandations/{id}', [RecommandationController::class, 'show']);

        // Rapports
        $this->get('rapports', [RapportController::class, 'index']);
        $this->get('rapports/{id}', [RapportController::class, 'show']);

        // Modules avancés
        $this->get('carte', [CarteController::class, 'index']);
        $this->get('carte/api/{id}', [CarteController::class, 'api']);

        $this->get('scenarios', [ScenarioController::class, 'index']);
        $this->get('scenarios/{id}', [ScenarioController::class, 'comparer']);

        $this->get('decision', [DecisionController::class, 'index']);
        $this->get('decision/{id}', [DecisionController::class, 'analyser']);

        $this->get('exports', [ExportController::class, 'index']);
        $this->get('exports/excel/{id}', [ExportController::class, 'excel']);
        $this->get('exports/pdf/{id}', [ExportController::class, 'pdf']);

        // GreenDC AI Advisor
        $this->get('ai', [AiController::class, 'index']);
        $this->post('ai/chat', [AiController::class, 'chat']);
        $this->post('ai/clear-history', [AiController::class, 'clearHistory']);
        $this->get('ai/analyze/{id}', [AiController::class, 'analyze']);
        $this->get('ai/insights/{id}', [AiController::class, 'insights']);
    }

    /**
     * @param array{0: class-string, 1: string} $action
     */
    private function get(string $path, array $action): void
    {
        $this->routes[] = [
            'method'  => 'GET',
            'pattern' => trim($path, '/'),
            'action'  => $action,
        ];
    }

    /**
     * @param array{0: class-string, 1: string} $action
     */
    private function post(string $path, array $action): void
    {
        $this->routes[] = [
            'method'  => 'POST',
            'pattern' => trim($path, '/'),
            'action'  => $action,
        ];
    }

    /**
     * Dispatch la requête courante.
     */
    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $this->resolveUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->match($route['pattern'], $uri);
            if ($params === null) {
                continue;
            }

            [$controllerClass, $methodName] = $route['action'];
            $controller = new $controllerClass();
            $controller->$methodName(...array_values($params));
            return;
        }

        http_response_code(404);
        require dirname(__DIR__) . '/views/errors/404.php';
    }

    /**
     * Compare un motif (ex: entreprises/{id}) à l'URI.
     *
     * @return array<string, string>|null
     */
    private function match(string $pattern, string $uri): ?array
    {
        if ($pattern === $uri) {
            return [];
        }

        $patternParts = $pattern === '' ? [] : explode('/', $pattern);
        $uriParts = $uri === '' ? [] : explode('/', $uri);

        if (count($patternParts) !== count($uriParts)) {
            return null;
        }

        $params = [];
        foreach ($patternParts as $i => $part) {
            if (preg_match('/^\{([a-zA-Z_]+)\}$/', $part, $m)) {
                $name = $m[1];
                // IDs numériques par défaut ; codes alphanumériques (ex. lang/{code})
                if ($name === 'code') {
                    if (!preg_match('/^[a-zA-Z]{2}$/', $uriParts[$i])) {
                        return null;
                    }
                } elseif (!ctype_digit($uriParts[$i])) {
                    return null;
                }
                $params[$name] = $uriParts[$i];
                continue;
            }

            if ($part !== $uriParts[$i]) {
                return null;
            }
        }

        return $params;
    }

    /**
     * Extrait le chemin relatif à l'application.
     */
    private function resolveUri(): string
    {
        $uri = $_GET['url'] ?? '';

        if ($uri === '' && isset($_SERVER['REQUEST_URI'])) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '';
            if ($this->basePath !== '' && str_starts_with($path, $this->basePath)) {
                $path = substr($path, strlen($this->basePath));
            }
            $uri = $path;
        }

        return trim((string) $uri, '/');
    }
}
