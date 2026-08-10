<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\DecisionEngine;
use App\Helpers\EnergyCalculator;
use App\Helpers\ExcelExport;
use App\Helpers\ImpactEnvironnemental;
use App\Helpers\PueCalculator;
use App\Helpers\RecommendationEngine;
use App\Helpers\Security;
use App\Helpers\SimulationEngine;
use App\Models\DataCenter;
use App\Models\Entreprise;
use App\Models\Equipement;
use App\Models\InstallationPv;
use App\Models\Recommandation;
use App\Models\Simulation;

/**
 * Exports PDF (impression) et Excel (Module 14).
 */
class ExportController extends Controller
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
        $this->view('exports/index', [
            'title'       => 'Exports',
            'dataCenters' => $this->dataCentersAccessibles(),
            'success'     => Security::flash('success'),
            'error'       => Security::flash('error'),
        ]);
    }

    public function excel(string $dataCenterId): void
    {
        Auth::requireLogin();
        $dc = $this->authorizeDataCenter((int) $dataCenterId);
        $entreprise = $this->entrepriseModel->findById((int) $dc['entreprise_id']);
        $equipements = array_map(
            static fn(array $e): array => EnergyCalculator::enrich($e),
            $this->equipementModel->findByDataCenterId((int) $dc['id'])
        );
        $totaux = EnergyCalculator::totaux($equipements);
        $ipv = $this->installationModel->findByDataCenterId((int) $dc['id']);
        $recos = $this->recommandationModel->findByDataCenterId((int) $dc['id']);
        if ($recos === []) {
            $recos = RecommendationEngine::generate($dc, $equipements, $ipv);
        }
        $pue = PueCalculator::calculer($equipements);
        $impact = ImpactEnvironnemental::calculer(
            (float) $totaux['annuelle'],
            $ipv ? (float) $ipv['energie_steg_kwh'] : (float) $totaux['annuelle'],
            $ipv ? (float) $ipv['energie_pv_kwh'] : 0.0
        );

        $avant = SimulationEngine::snapshotAvant($equipements, $ipv, $dc);
        $type = $avant['type_panneau'];
        $scenarios = [
            'A' => DecisionEngine::metriquesScenario($avant, $type),
            'B' => DecisionEngine::metriquesScenario(
                SimulationEngine::appliquerModifications($avant, ['ajouter_panneaux' => 50]),
                $type
            ),
        ];
        $decision = DecisionEngine::diagnostiquer($scenarios);

        $sheets = [
            [
                'title' => 'Synthese',
                'headers' => ['Indicateur', 'Valeur'],
                'rows' => [
                    ['Entreprise', $entreprise['nom'] ?? ''],
                    ['Data Center', $dc['nom']],
                    ['Gouvernorat', $dc['gouvernorat_nom'] ?? ''],
                    ['Conso annuelle kWh', $totaux['annuelle']],
                    ['Production PV kWh', $ipv['production_annuelle_kwh'] ?? 0],
                    ['Couverture %', $ipv['taux_couverture'] ?? 0],
                    ['Energie STEG kWh', $ipv['energie_steg_kwh'] ?? $totaux['annuelle']],
                    ['PUE', $pue['pue'] ?? 'N/A'],
                    ['CO2 evite kg', $impact['co2_evite_total_kg']],
                    ['Reduction CO2 %', $impact['pourcentage_reduction']],
                    ['Diagnostic', $decision['diagnostic']],
                ],
            ],
            [
                'title' => 'Equipements',
                'headers' => ['Nom', 'Categorie', 'Qte', 'Puissance W', 'Taux %', 'kWh/j', 'kWh/an'],
                'rows' => array_map(static fn(array $e): array => [
                    $e['nom'], $e['categorie_label'], (int) $e['quantite'],
                    (float) $e['puissance_watts'], (float) $e['taux_utilisation'],
                    (float) $e['conso_journaliere'], (float) $e['conso_annuelle'],
                ], $equipements),
            ],
            [
                'title' => 'Recommandations',
                'headers' => ['Priorite', 'Type', 'Message'],
                'rows' => array_map(static fn(array $r): array => [
                    $r['priorite'], $r['type'], $r['message'],
                ], $recos),
            ],
            [
                'title' => 'Scenarios',
                'headers' => ['Scenario', 'Conso', 'Prod PV', 'Couverture %', 'STEG', 'Cout', 'ROI %', 'Amort', 'CO2'],
                'rows' => array_values(array_map(static function (string $k, array $s): array {
                    return [
                        $k, $s['consommation_annuelle'], $s['production_pv'], $s['couverture_solaire'],
                        $s['energie_steg'], $s['cout_annuel'], $s['roi'], $s['temps_amortissement'], $s['co2_evite'],
                    ];
                }, array_keys($scenarios), $scenarios)),
            ],
        ];

        $filename = 'GreenDC_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $dc['nom']) . '.xls';
        ExcelExport::download($filename, $sheets);
    }

    /**
     * Redirige vers le rapport imprimable (PDF via navigateur).
     */
    public function pdf(string $dataCenterId): void
    {
        Auth::requireLogin();
        $this->authorizeDataCenter((int) $dataCenterId);
        Security::redirect('rapports/' . $dataCenterId);
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizeDataCenter(int $id): array
    {
        $dc = $this->dataCenterModel->findById($id);
        if ($dc === null) {
            Security::flash('error', 'Data Center introuvable.');
            Security::redirect('exports');
        }
        if (Auth::isClient() && (int) $dc['entreprise_utilisateur_id'] !== (int) Auth::id()) {
            Security::flash('error', 'Accès non autorisé.');
            Security::redirect('exports');
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
        return $entreprise ? $this->dataCenterModel->findByEntrepriseId((int) $entreprise['id']) : [];
    }
}
