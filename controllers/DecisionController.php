<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AlertEngine;
use App\Helpers\Auth;
use App\Helpers\DecisionEngine;
use App\Helpers\EnergyCalculator;
use App\Helpers\ImpactEnvironnemental;
use App\Helpers\PueCalculator;
use App\Helpers\RecommendationEngine;
use App\Helpers\Security;
use App\Helpers\SimulationEngine;
use App\Models\DataCenter;
use App\Models\Entreprise;
use App\Models\Equipement;
use App\Models\InstallationPv;
use App\Models\TypePanneau;

/**
 * Aide à la décision globale + PUE + impact environnemental (16–18).
 */
class DecisionController extends Controller
{
    private DataCenter $dataCenterModel;
    private Entreprise $entrepriseModel;
    private Equipement $equipementModel;
    private InstallationPv $installationModel;
    private TypePanneau $typePanneauModel;

    public function __construct()
    {
        $this->dataCenterModel = new DataCenter();
        $this->entrepriseModel = new Entreprise();
        $this->equipementModel = new Equipement();
        $this->installationModel = new InstallationPv();
        $this->typePanneauModel = new TypePanneau();
    }

    public function index(): void
    {
        Auth::requireLogin();
        $this->view('decision/index', [
            'title'       => 'Aide à la décision',
            'dataCenters' => $this->dataCentersAccessibles(),
            'success'     => Security::flash('success'),
            'error'       => Security::flash('error'),
        ]);
    }

    public function analyser(string $dataCenterId): void
    {
        Auth::requireLogin();
        $dc = $this->authorizeDataCenter((int) $dataCenterId);

        $equipements = $this->equipementModel->findByDataCenterId((int) $dc['id']);
        $ipv = $this->installationModel->findByDataCenterId((int) $dc['id']);
        $totaux = EnergyCalculator::totaux($equipements);
        $pue = PueCalculator::calculer($equipements);

        $energiePv = $ipv !== null ? (float) $ipv['energie_pv_kwh'] : 0.0;
        $energieSteg = $ipv !== null
            ? (float) $ipv['energie_steg_kwh']
            : (float) $totaux['annuelle'];

        $impact = ImpactEnvironnemental::calculer(
            (float) $totaux['annuelle'],
            $energieSteg,
            $energiePv
        );

        $alerts = AlertEngine::generate($dc, $equipements, $ipv, $pue);
        $recommandations = RecommendationEngine::generate($dc, $equipements, $ipv);

        // Scénarios A/B/C pour diagnostic
        $avant = SimulationEngine::snapshotAvant($equipements, $ipv, $dc);
        $type = $avant['type_panneau'];
        $scenarios = [
            'A' => DecisionEngine::metriquesScenario($avant, $type),
        ];
        $modsB = ['ajouter_panneaux' => 50];
        if (!empty($dc['gouvernorat_heures'])) {
            $modsB['heures_ensoleillement'] = (float) $dc['gouvernorat_heures'];
        }
        $scenarios['B'] = DecisionEngine::metriquesScenario(
            SimulationEngine::appliquerModifications($avant, $modsB),
            $type
        );
        $modsC = $modsB;
        foreach ($avant['equipements'] as $eq) {
            if ($eq['categorie'] === 'serveur') {
                $modsC['remplacer_equipement_id'] = (int) $eq['id'];
                $modsC['nouvelle_puissance'] = max(200, (float) $eq['puissance_watts'] * 0.7);
                $modsC['nouveau_taux'] = max(40, (float) $eq['taux_utilisation'] - 10);
                break;
            }
        }
        $scenarios['C'] = DecisionEngine::metriquesScenario(
            SimulationEngine::appliquerModifications($avant, $modsC),
            $type
        );
        foreach ($scenarios as $k => $s) {
            $scenarios[$k]['libelle'] = match ($k) {
                'A' => 'Infrastructure actuelle',
                'B' => 'Ajout PV',
                'C' => 'Serveurs économes + PV',
                default => $k,
            };
        }
        $decision = DecisionEngine::diagnostiquer($scenarios);

        $this->view('decision/analyser', [
            'title'           => 'Diagnostic — ' . $dc['nom'],
            'dataCenter'      => $dc,
            'pue'             => $pue,
            'impact'          => $impact,
            'alerts'          => $alerts,
            'recommandations' => $recommandations,
            'scenarios'       => $scenarios,
            'decision'        => $decision,
            'totaux'          => $totaux,
            'installation'    => $ipv,
            'success'         => Security::flash('success'),
            'error'           => Security::flash('error'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizeDataCenter(int $id): array
    {
        $dc = $this->dataCenterModel->findById($id);
        if ($dc === null) {
            Security::flash('error', 'Data Center introuvable.');
            Security::redirect('decision');
        }
        if (Auth::isClient() && (int) $dc['entreprise_utilisateur_id'] !== (int) Auth::id()) {
            Security::flash('error', 'Accès non autorisé.');
            Security::redirect('decision');
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
