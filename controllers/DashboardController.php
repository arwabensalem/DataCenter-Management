<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AlertEngine;
use App\Helpers\AiContextBuilder;
use App\Helpers\AiFallback;
use App\Helpers\Auth;
use App\Helpers\DashboardAggregator;
use App\Helpers\PueCalculator;
use App\Helpers\Security;
use App\Models\DataCenter;
use App\Models\Entreprise;
use App\Models\Equipement;
use App\Models\InstallationPv;
use App\Models\Recommandation;
use App\Models\Simulation;

/**
 * Tableau de bord décisionnel — KPI + alertes + PUE + Chart.js.
 */
class DashboardController extends Controller
{
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
        $dashboard = DashboardAggregator::build(
            $dataCenters,
            fn(int $id): array => $this->equipementModel->findByDataCenterId($id),
            fn(int $id): ?array => $this->installationModel->findByDataCenterId($id)
        );

        $nbEntreprises = Auth::isAdmin()
            ? count($this->entrepriseModel->allWithDetails())
            : ($this->entrepriseModel->findByUtilisateurId((int) Auth::id()) ? 1 : 0);

        $recommandations = Auth::isAdmin()
            ? $this->recommandationModel->latestByAccessibleDcs()
            : $this->recommandationModel->latestByAccessibleDcs((int) Auth::id());

        $simulations = Auth::isAdmin()
            ? $this->simulationModel->allWithContext()
            : $this->simulationModel->allWithContext((int) Auth::id());

        // Alertes + PUE agrégés
        $allAlerts = [];
        $pueList = [];
        foreach ($dataCenters as $dc) {
            $eq = $this->equipementModel->findByDataCenterId((int) $dc['id']);
            $ipv = $this->installationModel->findByDataCenterId((int) $dc['id']);
            $pue = PueCalculator::calculer($eq);
            $pueList[] = ['dc' => $dc['nom'], 'pue' => $pue];
            foreach (AlertEngine::generate($dc, $eq, $ipv, $pue) as $alert) {
                $alert['data_center'] = $dc['nom'];
                $allAlerts[] = $alert;
            }
        }

        // AI Insights (déterministe rapide — pas d'appel RAG bloquant sur le dashboard)
        $aiInsights = null;
        if ($dataCenters !== []) {
            $focus = $dataCenters[0];
            $eq = $this->equipementModel->findByDataCenterId((int) $focus['id']);
            $ipv = $this->installationModel->findByDataCenterId((int) $focus['id']);
            $recosDc = $this->recommandationModel->findByDataCenterId((int) $focus['id']);
            $ctx = AiContextBuilder::build($focus, $eq, $ipv, $recosDc, null);
            $aiInsights = AiFallback::insightsCard($ctx);
            $aiInsights['data_center_id'] = (int) $focus['id'];
        }

        $this->view('dashboard/index', [
            'title'           => 'Tableau de bord',
            'user'            => Auth::user(),
            'kpis'            => $dashboard['kpis'],
            'charts'          => $dashboard['charts'],
            'nbEntreprises'   => $nbEntreprises,
            'topRecos'        => array_slice($recommandations, 0, 5),
            'nbSimulations'   => count($simulations),
            'nbRecos'         => count($recommandations),
            'alerts'          => array_slice($allAlerts, 0, 8),
            'pueList'         => $pueList,
            'aiInsights'      => $aiInsights,
            'success'         => Security::flash('success'),
            'error'           => Security::flash('error'),
        ]);
    }

    public function chartsData(): void
    {
        Auth::requireLogin();

        $dataCenters = $this->dataCentersAccessibles();
        $dashboard = DashboardAggregator::build(
            $dataCenters,
            fn(int $id): array => $this->equipementModel->findByDataCenterId($id),
            fn(int $id): ?array => $this->installationModel->findByDataCenterId($id)
        );

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dashboard['charts'], JSON_UNESCAPED_UNICODE);
        exit;
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
}
