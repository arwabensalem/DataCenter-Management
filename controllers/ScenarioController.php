<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\DecisionEngine;
use App\Helpers\Security;
use App\Helpers\SimulationEngine;
use App\Models\DataCenter;
use App\Models\Entreprise;
use App\Models\Equipement;
use App\Models\InstallationPv;
use App\Models\Simulation;
use App\Models\TypePanneau;

/**
 * Comparaison multi-scénarios (Module 12) + mise en évidence du meilleur.
 */
class ScenarioController extends Controller
{
    private DataCenter $dataCenterModel;
    private Entreprise $entrepriseModel;
    private Equipement $equipementModel;
    private InstallationPv $installationModel;
    private Simulation $simulationModel;
    private TypePanneau $typePanneauModel;

    public function __construct()
    {
        $this->dataCenterModel = new DataCenter();
        $this->entrepriseModel = new Entreprise();
        $this->equipementModel = new Equipement();
        $this->installationModel = new InstallationPv();
        $this->simulationModel = new Simulation();
        $this->typePanneauModel = new TypePanneau();
    }

    public function index(): void
    {
        Auth::requireLogin();

        $this->view('scenarios/index', [
            'title'       => 'Comparaison de scénarios',
            'dataCenters' => $this->dataCentersAccessibles(),
            'success'     => Security::flash('success'),
            'error'       => Security::flash('error'),
        ]);
    }

    public function comparer(string $dataCenterId): void
    {
        Auth::requireLogin();
        $dc = $this->authorizeDataCenter((int) $dataCenterId);

        $equipements = $this->equipementModel->findByDataCenterId((int) $dc['id']);
        $ipv = $this->installationModel->findByDataCenterId((int) $dc['id']);
        $avant = SimulationEngine::snapshotAvant($equipements, $ipv, $dc);

        $type = $avant['type_panneau'];
        if ($type === null && $ipv !== null) {
            $type = $this->typePanneauModel->findById((int) $ipv['type_panneau_id']);
        }

        // Scénario A — infrastructure actuelle
        $scenarioA = DecisionEngine::metriquesScenario($avant, $type);
        $scenarioA['libelle'] = 'Infrastructure actuelle';
        $scenarioA['description'] = 'État de référence sans modification.';

        // Scénario B — ajout de panneaux (+30 % si possible, sinon max surface)
        $modsB = ['ajouter_panneaux' => max(50, (int) ceil(($avant['nombre_panneaux'] ?: 100) * 0.3))];
        if (!empty($dc['gouvernorat_heures'])) {
            $modsB['heures_ensoleillement'] = (float) $dc['gouvernorat_heures'];
        }
        $apresB = SimulationEngine::appliquerModifications($avant, $modsB);
        $scenarioB = DecisionEngine::metriquesScenario($apresB, $type);
        $scenarioB['libelle'] = 'Ajout de panneaux photovoltaïques';
        $scenarioB['description'] = 'Extension de la capacité PV (+ panneaux).';

        // Scénario C — serveurs économes + panneaux
        $modsC = $modsB;
        foreach ($avant['equipements'] as $eq) {
            if ($eq['categorie'] === 'serveur') {
                $modsC['remplacer_equipement_id'] = (int) $eq['id'];
                $modsC['nouvelle_puissance'] = max(200, (float) $eq['puissance_watts'] * 0.7);
                $modsC['nouveau_taux'] = min(100, max(40, (float) $eq['taux_utilisation'] - 10));
                break;
            }
        }
        $apresC = SimulationEngine::appliquerModifications($avant, $modsC);
        $scenarioC = DecisionEngine::metriquesScenario($apresC, $type);
        $scenarioC['libelle'] = 'Serveurs économes + panneaux PV';
        $scenarioC['description'] = 'Remplacement serveurs + extension photovoltaïque.';

        $scenarios = [
            'A' => $scenarioA,
            'B' => $scenarioB,
            'C' => $scenarioC,
        ];

        // Intégrer simulations sauvegardées (D, E…)
        $sims = array_filter(
            Auth::isAdmin()
                ? $this->simulationModel->allWithContext()
                : $this->simulationModel->allWithContext((int) Auth::id()),
            static fn(array $s): bool => (int) $s['data_center_id'] === (int) $dc['id']
        );
        $letter = 'D';
        foreach (array_slice($sims, 0, 3) as $sim) {
            $full = $this->simulationModel->findById((int) $sim['id']);
            if ($full === null) {
                continue;
            }
            $m = DecisionEngine::metriquesScenario($full['parametres_apres'], $type);
            $m['libelle'] = $full['nom'];
            $m['description'] = $full['description'] ?? 'Simulation enregistrée';
            $scenarios[$letter] = $m;
            $letter++;
        }

        $decision = DecisionEngine::diagnostiquer($scenarios);

        $this->view('scenarios/comparer', [
            'title'      => 'Scénarios — ' . $dc['nom'],
            'dataCenter' => $dc,
            'scenarios'  => $scenarios,
            'decision'   => $decision,
            'success'    => Security::flash('success'),
            'error'      => Security::flash('error'),
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
            Security::redirect('scenarios');
        }
        if (Auth::isClient() && (int) $dc['entreprise_utilisateur_id'] !== (int) Auth::id()) {
            Security::flash('error', 'Accès non autorisé.');
            Security::redirect('scenarios');
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
