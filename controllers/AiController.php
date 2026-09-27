<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AiClient;
use App\Helpers\AiContextBuilder;
use App\Helpers\AiFallback;
use App\Helpers\Auth;
use App\Helpers\Security;
use App\Models\DataCenter;
use App\Models\Entreprise;
use App\Models\Equipement;
use App\Models\InstallationPv;
use App\Models\Recommandation;
use App\Models\Simulation;

/**
 * GreenDC AI Advisor — chatbot, analyse énergétique, insights.
 */
class AiController extends Controller
{
    private const HISTORY_LIMIT = 10;

    private DataCenter $dataCenterModel;
    private Entreprise $entrepriseModel;
    private Equipement $equipementModel;
    private InstallationPv $installationModel;
    private Recommandation $recommandationModel;
    private Simulation $simulationModel;

    public function __construct()
    {
        $this->dataCenterModel = new DataCenter();
        $this->entrepriseModel = new Entreprise();
        $this->equipementModel = new Equipement();
        $this->installationModel = new InstallationPv();
        $this->recommandationModel = new Recommandation();
        $this->simulationModel = new Simulation();
    }

    public function index(): void
    {
        Auth::requireLogin();
        $dataCenters = $this->dataCentersAccessibles();
        $selectedId = isset($_GET['dc']) ? (int) $_GET['dc'] : (int) ($dataCenters[0]['id'] ?? 0);

        $health = AiClient::health();
        $ragOnline = $health['ok'] ?? false;
        $healthData = $health['data'] ?? [];
        $llmOnline = !empty($healthData['llm_configured']);

        $this->view('ai/chat', [
            'title'        => 'GreenDC AI Advisor',
            'dataCenters'  => $dataCenters,
            'selectedId'   => $selectedId,
            'ragOnline'    => $ragOnline,
            'llmOnline'    => $llmOnline,
            'llmModel'     => (string) ($healthData['llm_model'] ?? ''),
            'llmProvider'  => (string) ($healthData['llm_provider'] ?? ''),
            'ragHealth'    => $healthData,
            'csrf'         => Security::generateCsrfToken(),
        ]);
    }

    /**
     * POST JSON — message chatbot.
     */
    public function chat(): void
    {
        Auth::requireLogin();
        $this->requireJsonPost();

        $input = $this->jsonInput();
        if (!Security::verifyCsrf($input['_csrf'] ?? null)) {
            $this->jsonResponse(['error' => 'CSRF invalide'], 403);
        }

        $question = Security::clean((string) ($input['question'] ?? ''));
        $dcId = (int) ($input['data_center_id'] ?? 0);
        if ($dcId <= 0 || $question === '') {
            $this->jsonResponse(['error' => 'Paramètres manquants'], 422);
        }

        $dc = $this->authorizeDataCenter($dcId);
        $ctx = $this->buildContextForDc($dc);
        $history = $this->getHistory($dcId);
        $historyForApi = array_map(
            static fn(array $m): array => ['role' => $m['role'], 'content' => $m['content']],
            $history
        );

        $result = AiClient::ask(
            $question,
            $ctx,
            $dcId,
            (int) Auth::id(),
            $historyForApi,
            'chat'
        );

        $this->pushHistory($dcId, 'user', $question);
        $this->pushHistory($dcId, 'assistant', (string) ($result['answer'] ?? ''));

        $this->jsonResponse([
            'answer'          => $result['answer'] ?? '',
            'sources'         => $result['sources'] ?? [],
            'metrics'         => $result['metrics'] ?? [],
            'recommendations' => $result['recommendations'] ?? [],
            'fallback'        => (bool) ($result['fallback'] ?? false),
            'data_center'     => [
                'id'   => (int) $dc['id'],
                'name' => (string) $dc['nom'],
            ],
        ]);
    }

    /**
     * Analyse énergétique AI d'un DC.
     */
    public function analyze(string $dataCenterId): void
    {
        Auth::requireLogin();
        $dc = $this->authorizeDataCenter((int) $dataCenterId);
        $ctx = $this->buildContextForDc($dc);

        $question = 'Produis une analyse énergétique structurée de ce Data Center : '
            . 'Résumé, Problèmes détectés, Causes possibles, Actions recommandées, '
            . 'Impact attendu (qualitatif sauf si chiffré dans le contexte), Sources. '
            . 'Utilise les données fournies et les documents RAG. Ne invente pas de chiffres.';

        $result = AiClient::ask(
            $question,
            $ctx,
            (int) $dc['id'],
            (int) Auth::id(),
            [],
            'analysis'
        );

        if ($this->wantsJson()) {
            $this->jsonResponse([
                'answer'          => $result['answer'] ?? '',
                'sources'         => $result['sources'] ?? [],
                'metrics'         => $result['metrics'] ?? [],
                'recommendations' => $result['recommendations'] ?? [],
                'fallback'        => (bool) ($result['fallback'] ?? false),
                'context_summary' => $ctx['data_center'] ?? [],
            ]);
        }

        $this->view('ai/analyze', [
            'title'           => 'AI Energy Analysis — ' . $dc['nom'],
            'dataCenter'      => $dc,
            'context'         => $ctx,
            'result'          => $result,
            'insights'        => AiFallback::insightsCard($ctx),
        ]);
    }

    /**
     * Insights JSON pour le dashboard / widgets.
     */
    public function insights(string $dataCenterId): void
    {
        Auth::requireLogin();
        $dc = $this->authorizeDataCenter((int) $dataCenterId);
        $ctx = $this->buildContextForDc($dc);
        $card = AiFallback::insightsCard($ctx);

        // Tentative d'enrichissement court via RAG (non bloquant longtemps)
        $health = AiClient::health();
        if ($health['ok'] ?? false) {
            $brief = AiClient::ask(
                'En 3 phrases maximum, résume le principal levier d\'optimisation de ce Data Center '
                . 'à partir des métriques fournies. Pas de chiffres inventés.',
                $ctx,
                (int) $dc['id'],
                (int) Auth::id(),
                [],
                'analysis'
            );
            if (empty($brief['fallback']) && !empty($brief['answer'])) {
                $card['analysis'] = mb_substr(strip_tags((string) $brief['answer']), 0, 500);
                $card['llm_used'] = (bool) (($brief['metrics']['llm_used'] ?? false));
                $card['fallback'] = false;
                $card['sources'] = $brief['sources'] ?? [];
            }
        }

        $this->jsonResponse($card);
    }

    /**
     * Efface l'historique de chat pour un DC.
     */
    public function clearHistory(): void
    {
        Auth::requireLogin();
        $this->requireJsonPost();
        $input = $this->jsonInput();
        if (!Security::verifyCsrf($input['_csrf'] ?? null)) {
            $this->jsonResponse(['error' => 'CSRF invalide'], 403);
        }
        $dcId = (int) ($input['data_center_id'] ?? 0);
        $this->authorizeDataCenter($dcId);
        unset($_SESSION['ai_chat'][$dcId]);
        $this->jsonResponse(['ok' => true]);
    }

    /**
     * @param array<string, mixed> $dc
     * @return array<string, mixed>
     */
    private function buildContextForDc(array $dc): array
    {
        $id = (int) $dc['id'];
        $equipements = $this->equipementModel->findByDataCenterId($id);
        $ipv = $this->installationModel->findByDataCenterId($id);
        $recos = $this->recommandationModel->findByDataCenterId($id);
        $sims = $this->simulationModel->findByDataCenterId($id);
        $latest = $sims[0] ?? null;

        return AiContextBuilder::build($dc, $equipements, $ipv, $recos, $latest);
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizeDataCenter(int $id): array
    {
        $dc = $this->dataCenterModel->findById($id);
        if ($dc === null) {
            if ($this->wantsJson()) {
                $this->jsonResponse(['error' => 'Data Center introuvable'], 404);
            }
            Security::flash('error', 'Data Center introuvable.');
            Security::redirect('ai');
        }

        if (Auth::isClient() && (int) $dc['entreprise_utilisateur_id'] !== (int) Auth::id()) {
            if ($this->wantsJson()) {
                $this->jsonResponse(['error' => 'Accès non autorisé'], 403);
            }
            Security::flash('error', 'Accès non autorisé.');
            Security::redirect('ai');
        }

        return $dc;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function dataCentersAccessibles(): array
    {
        if (Auth::isAdmin()) {
            return $this->dataCenterModel->allWithEntreprise();
        }
        $entreprise = $this->entrepriseModel->findByUtilisateurId((int) Auth::id());
        if ($entreprise === null) {
            return [];
        }
        return $this->dataCenterModel->findByEntrepriseId((int) $entreprise['id']);
    }

    /**
     * @return list<array{role: string, content: string, ts: int}>
     */
    private function getHistory(int $dcId): array
    {
        $all = $_SESSION['ai_chat'][$dcId] ?? [];
        return is_array($all) ? $all : [];
    }

    private function pushHistory(int $dcId, string $role, string $content): void
    {
        if (!isset($_SESSION['ai_chat']) || !is_array($_SESSION['ai_chat'])) {
            $_SESSION['ai_chat'] = [];
        }
        if (!isset($_SESSION['ai_chat'][$dcId]) || !is_array($_SESSION['ai_chat'][$dcId])) {
            $_SESSION['ai_chat'][$dcId] = [];
        }
        $_SESSION['ai_chat'][$dcId][] = [
            'role'    => $role,
            'content' => mb_substr($content, 0, 4000),
            'ts'      => time(),
        ];
        // Garde les N derniers messages
        $_SESSION['ai_chat'][$dcId] = array_slice(
            $_SESSION['ai_chat'][$dcId],
            -self::HISTORY_LIMIT
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonInput(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        if (is_array($data)) {
            return $data;
        }
        return $_POST;
    }

    private function requireJsonPost(): void
    {
        if (!$this->isPost()) {
            $this->jsonResponse(['error' => 'POST requis'], 405);
        }
    }

    private function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json')
            || (isset($_GET['format']) && $_GET['format'] === 'json');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function jsonResponse(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
