<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\SimulationEngine;
use App\Models\DataCenter;
use App\Models\Entreprise;
use App\Models\Equipement;
use App\Models\InstallationPv;
use App\Models\RegionEnsoleillement;
use App\Models\Simulation;
use RuntimeException;

/**
 * Module Simulation — scénarios AVANT / APRÈS.
 */
class SimulationController extends Controller
{
    private Simulation $simulationModel;
    private DataCenter $dataCenterModel;
    private Equipement $equipementModel;
    private InstallationPv $installationModel;
    private Entreprise $entrepriseModel;
    private RegionEnsoleillement $regionModel;

    public function __construct()
    {
        $this->simulationModel = new Simulation();
        $this->dataCenterModel = new DataCenter();
        $this->equipementModel = new Equipement();
        $this->installationModel = new InstallationPv();
        $this->entrepriseModel = new Entreprise();
        $this->regionModel = new RegionEnsoleillement();
    }

    public function index(): void
    {
        Auth::requireLogin();

        $simulations = Auth::isAdmin()
            ? $this->simulationModel->allWithContext()
            : $this->simulationModel->allWithContext((int) Auth::id());

        $this->view('simulations/index', [
            'title'       => 'Simulations',
            'simulations' => $simulations,
            'success'     => Security::flash('success'),
            'error'       => Security::flash('error'),
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();

        $dataCenters = $this->dataCentersDisponibles();
        $preselect = isset($_GET['data_center_id']) && ctype_digit((string) $_GET['data_center_id'])
            ? (int) $_GET['data_center_id']
            : 0;

        $equipements = [];
        $avant = null;
        $dc = null;

        if ($preselect > 0) {
            $dc = $this->authorizeDataCenter($preselect);
            $equipements = $this->equipementModel->findByDataCenterId($preselect);
            $ipv = $this->installationModel->findByDataCenterId($preselect);
            $avant = SimulationEngine::snapshotAvant($equipements, $ipv, $dc);
        }

        $this->view('simulations/form', [
            'title'       => 'Nouvelle simulation',
            'dataCenters' => $dataCenters,
            'regions'     => $this->regionModel->all(),
            'preselect'   => $preselect,
            'dataCenter'  => $dc,
            'equipements' => $equipements,
            'avant'       => $avant,
            'old'         => $_SESSION['_old'] ?? [],
            'errors'      => $_SESSION['_errors'] ?? [],
            'success'     => Security::flash('success'),
            'error'       => Security::flash('error'),
        ]);

        unset($_SESSION['_old'], $_SESSION['_errors']);
    }

    public function store(): void
    {
        Auth::requireLogin();

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('simulations');
        }

        $dataCenterId = Security::clean($_POST['data_center_id'] ?? '');
        $nom = Security::clean($_POST['nom'] ?? '');
        $description = Security::clean($_POST['description'] ?? '');

        $errors = [];
        if ($dataCenterId === '' || !ctype_digit($dataCenterId)) {
            $errors['data_center_id'] = 'Sélectionnez un Data Center.';
        }
        if ($nom === '') {
            $errors['nom'] = 'Le nom du scénario est obligatoire.';
        }

        $mods = $this->extractMods();
        $ville = Security::clean($_POST['ville'] ?? '');
        if (!$this->hasAnyModification($mods) && $ville === '') {
            $errors['mods'] = 'Définissez au moins une modification de scénario.';
        }

        if ($errors !== []) {
            $_SESSION['_old'] = array_merge($_POST, ['nom' => $nom, 'description' => $description]);
            $_SESSION['_errors'] = $errors;
            $redirect = 'simulations/create';
            if (ctype_digit($dataCenterId)) {
                $redirect .= '?data_center_id=' . $dataCenterId;
            }
            Security::redirect($redirect);
        }

        $dc = $this->authorizeDataCenter((int) $dataCenterId);
        $equipements = $this->equipementModel->findByDataCenterId((int) $dataCenterId);
        $ipv = $this->installationModel->findByDataCenterId((int) $dataCenterId);

        // Résoudre ville → heures
        if ($ville !== '') {
            $region = $this->regionModel->findByVille($ville);
            if ($region !== null) {
                $mods['heures_ensoleillement'] = (float) $region['heures_ensoleillement'];
                $mods['ville'] = $region['ville'];
            }
        }

        $avant = SimulationEngine::snapshotAvant($equipements, $ipv, $dc);
        $apres = SimulationEngine::appliquerModifications($avant, $mods);
        $comparaison = SimulationEngine::comparer($avant, $apres);

        try {
            $id = $this->simulationModel->create([
                'data_center_id'            => (int) $dataCenterId,
                'utilisateur_id'            => (int) Auth::id(),
                'nom'                       => $nom,
                'description'               => $description !== '' ? $description : null,
                'parametres_avant'          => $avant,
                'parametres_apres'          => $apres,
                'energie_economisee'        => $comparaison['energie_economisee'],
                'pourcentage_reduction'     => $comparaison['pourcentage_reduction'],
                'cout_economise'            => $comparaison['cout_economise'],
                'reduction_dependance_steg' => $comparaison['reduction_dependance_steg'],
                'co2_evite'                 => $comparaison['co2_evite'],
            ]);

            Security::flash('success', 'Simulation enregistrée.');
            Security::redirect('simulations/' . $id);
        } catch (RuntimeException $e) {
            Security::flash('error', $e->getMessage());
            Security::redirect('simulations/create?data_center_id=' . $dataCenterId);
        }
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $simulation = $this->authorizeAccess((int) $id);

        $this->view('simulations/show', [
            'title'      => $simulation['nom'],
            'simulation' => $simulation,
            'avant'      => $simulation['parametres_avant'],
            'apres'      => $simulation['parametres_apres'],
            'success'    => Security::flash('success'),
            'error'      => Security::flash('error'),
        ]);
    }

    public function delete(string $id): void
    {
        Auth::requireLogin();
        $this->authorizeAccess((int) $id);

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('simulations');
        }

        $this->simulationModel->delete((int) $id);
        Security::flash('success', 'Simulation supprimée.');
        Security::redirect('simulations');
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizeAccess(int $id): array
    {
        $simulation = $this->simulationModel->findById($id);

        if ($simulation === null) {
            Security::flash('error', 'Simulation introuvable.');
            Security::redirect('simulations');
        }

        if (Auth::isClient() && (int) $simulation['entreprise_utilisateur_id'] !== (int) Auth::id()) {
            Security::flash('error', 'Accès non autorisé.');
            Security::redirect('simulations');
        }

        return $simulation;
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizeDataCenter(int $id): array
    {
        $dc = $this->dataCenterModel->findById($id);

        if ($dc === null) {
            Security::flash('error', 'Data Center introuvable.');
            Security::redirect('simulations');
        }

        if (Auth::isClient() && (int) $dc['entreprise_utilisateur_id'] !== (int) Auth::id()) {
            Security::flash('error', 'Accès non autorisé.');
            Security::redirect('simulations');
        }

        return $dc;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function dataCentersDisponibles(): array
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
     * @return array<string, mixed>
     */
    private function extractMods(): array
    {
        $mods = [];

        $ajoutQte = Security::clean($_POST['ajouter_serveurs_qte'] ?? '');
        if ($ajoutQte !== '' && ctype_digit($ajoutQte) && (int) $ajoutQte > 0) {
            $mods['ajouter_serveurs_qte'] = (int) $ajoutQte;
            $mods['ajouter_serveurs_puissance'] = $this->toFloatOr($_POST['ajouter_serveurs_puissance'] ?? '400', 400);
            $mods['ajouter_serveurs_taux'] = $this->toFloatOr($_POST['ajouter_serveurs_taux'] ?? '70', 70);
            $mods['ajouter_serveurs_heures'] = $this->toFloatOr($_POST['ajouter_serveurs_heures'] ?? '24', 24);
        }

        $supprId = Security::clean($_POST['supprimer_equipement_id'] ?? '');
        $supprQty = Security::clean($_POST['supprimer_quantite'] ?? '');
        if ($supprId !== '' && ctype_digit($supprId) && $supprQty !== '' && ctype_digit($supprQty) && (int) $supprQty > 0) {
            $mods['supprimer_equipement_id'] = (int) $supprId;
            $mods['supprimer_quantite'] = (int) $supprQty;
        }

        $remplId = Security::clean($_POST['remplacer_equipement_id'] ?? '');
        if ($remplId !== '' && ctype_digit($remplId)) {
            $mods['remplacer_equipement_id'] = (int) $remplId;
            $nouvP = $this->toFloatNullable($_POST['nouvelle_puissance'] ?? '');
            $nouvT = $this->toFloatNullable($_POST['nouveau_taux'] ?? '');
            if ($nouvP !== null) {
                $mods['nouvelle_puissance'] = $nouvP;
            }
            if ($nouvT !== null) {
                $mods['nouveau_taux'] = $nouvT;
            }
        }

        $ajoutPv = Security::clean($_POST['ajouter_panneaux'] ?? '');
        if ($ajoutPv !== '' && ctype_digit($ajoutPv) && (int) $ajoutPv > 0) {
            $mods['ajouter_panneaux'] = (int) $ajoutPv;
        }

        $nbPv = Security::clean($_POST['nouveau_nombre_panneaux'] ?? '');
        if ($nbPv !== '' && ctype_digit($nbPv)) {
            $mods['nouveau_nombre_panneaux'] = (int) $nbPv;
        }

        $heures = $this->toFloatNullable($_POST['heures_ensoleillement'] ?? '');
        if ($heures !== null && $heures > 0 && $heures <= 24) {
            $mods['heures_ensoleillement'] = $heures;
        }

        return $mods;
    }

    /**
     * @param array<string, mixed> $mods
     */
    private function hasAnyModification(array $mods): bool
    {
        return $mods !== [];
    }

    private function toFloatNullable(string $value): ?float
    {
        $normalized = str_replace(',', '.', trim($value));
        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function toFloatOr(string $value, float $default): float
    {
        return $this->toFloatNullable($value) ?? $default;
    }
}
