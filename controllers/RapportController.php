<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\DashboardAggregator;
use App\Helpers\DecisionEngine;
use App\Helpers\EnergyCalculator;
use App\Helpers\Security;
use App\Helpers\SimulationEngine;
use App\Models\DataCenter;
use App\Models\Entreprise;
use App\Models\Equipement;
use App\Models\InstallationPv;
use App\Models\Recommandation;
use App\Models\Simulation;

/**
 * Rapports décisionnels imprimables.
 */
class RapportController extends Controller
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

    /**
     * Liste des Data Centers pour lesquels générer un rapport.
     */
    public function index(): void
    {
        Auth::requireLogin();

        $this->view('rapports/index', [
            'title'       => 'Rapports',
            'dataCenters' => $this->dataCentersAccessibles(),
            'success'     => Security::flash('success'),
            'error'       => Security::flash('error'),
        ]);
    }

    /**
     * Rapport complet imprimable pour un Data Center.
     */
    public function show(string $dataCenterId): void
    {
        Auth::requireLogin();
        $dc = $this->authorizeDataCenter((int) $dataCenterId);

        $entreprise = $this->entrepriseModel->findById((int) $dc['entreprise_id']);
        $equipementsRaw = $this->equipementModel->findByDataCenterId((int) $dc['id']);
        $equipements = array_map(
            static fn(array $eq): array => EnergyCalculator::enrich($eq),
            $equipementsRaw
        );
        $totaux = EnergyCalculator::totaux($equipements);
        $installation = $this->installationModel->findByDataCenterId((int) $dc['id']);
        $recommandations = $this->recommandationModel->findByDataCenterId((int) $dc['id']);

        // Simulations liées à ce DC
        $allSims = Auth::isAdmin()
            ? $this->simulationModel->allWithContext()
            : $this->simulationModel->allWithContext((int) Auth::id());
        $simulations = array_values(array_filter(
            $allSims,
            static fn(array $s): bool => (int) $s['data_center_id'] === (int) $dc['id']
        ));

        $app = require dirname(__DIR__) . '/config/app.php';
        $co2 = $installation !== null
            ? round((float) $installation['energie_pv_kwh'] * (float) $app['co2_kg_per_kwh'], 2)
            : 0.0;
        $economie = $installation !== null
            ? EnergyCalculator::coutAnnuel(
                (float) $installation['energie_pv_kwh'],
                (float) $dc['prix_kwh_steg']
            )
            : 0.0;
        $coutSteg = EnergyCalculator::coutAnnuel(
            $installation !== null ? (float) $installation['energie_steg_kwh'] : (float) $totaux['annuelle'],
            (float) $dc['prix_kwh_steg']
        );

        // Données graphiques (1 DC)
        $charts = DashboardAggregator::build(
            [$dc],
            fn(int $id): array => $this->equipementModel->findByDataCenterId($id),
            fn(int $id): ?array => $this->installationModel->findByDataCenterId($id)
        )['charts'];

        // Diagnostic décisionnel pour la conclusion
        $avant = SimulationEngine::snapshotAvant($equipementsRaw, $installation, $dc);
        $type = $avant['type_panneau'];
        $scenarios = [
            'A' => DecisionEngine::metriquesScenario($avant, $type),
            'B' => DecisionEngine::metriquesScenario(
                SimulationEngine::appliquerModifications($avant, ['ajouter_panneaux' => 50]),
                $type
            ),
        ];
        foreach ($avant['equipements'] as $eq) {
            if ($eq['categorie'] === 'serveur') {
                $apresC = SimulationEngine::appliquerModifications($avant, [
                    'ajouter_panneaux' => 50,
                    'remplacer_equipement_id' => (int) $eq['id'],
                    'nouvelle_puissance' => max(200, (float) $eq['puissance_watts'] * 0.7),
                    'nouveau_taux' => max(40, (float) $eq['taux_utilisation'] - 10),
                ]);
                $scenarios['C'] = DecisionEngine::metriquesScenario($apresC, $type);
                break;
            }
        }
        $decision = DecisionEngine::diagnostiquer($scenarios);

        $this->view('rapports/show', [
            'title'           => 'Rapport — ' . $dc['nom'],
            'entreprise'      => $entreprise,
            'dataCenter'      => $dc,
            'equipements'     => $equipements,
            'totaux'          => $totaux,
            'installation'    => $installation,
            'simulations'     => $simulations,
            'recommandations' => $recommandations,
            'co2Evite'        => $co2,
            'economie'        => $economie,
            'coutSteg'        => $coutSteg,
            'charts'          => $charts,
            'decision'        => $decision,
            'scenarios'       => $scenarios,
            'generatedAt'     => date('d/m/Y H:i'),
            'auteur'          => Auth::fullName(),
        ], 'layouts/print');
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizeDataCenter(int $id): array
    {
        $dc = $this->dataCenterModel->findById($id);

        if ($dc === null) {
            Security::flash('error', 'Data Center introuvable.');
            Security::redirect('rapports');
        }

        if (Auth::isClient() && (int) $dc['entreprise_utilisateur_id'] !== (int) Auth::id()) {
            Security::flash('error', 'Accès non autorisé.');
            Security::redirect('rapports');
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
}
