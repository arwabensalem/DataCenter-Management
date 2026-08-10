<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\EnergyCalculator;
use App\Helpers\Security;
use App\Models\DataCenter;
use App\Models\Entreprise;
use App\Models\Equipement;
use RuntimeException;

/**
 * CRUD Équipements + affichage des consommations calculées.
 */
class EquipementController extends Controller
{
    private Equipement $equipementModel;
    private DataCenter $dataCenterModel;
    private Entreprise $entrepriseModel;

    public function __construct()
    {
        $this->equipementModel = new Equipement();
        $this->dataCenterModel = new DataCenter();
        $this->entrepriseModel = new Entreprise();
    }

    /**
     * Liste des équipements (filtrable par data_center_id).
     */
    public function index(): void
    {
        Auth::requireLogin();

        $filterDcId = isset($_GET['data_center_id']) && ctype_digit((string) $_GET['data_center_id'])
            ? (int) $_GET['data_center_id']
            : null;

        if ($filterDcId !== null) {
            $this->authorizeDataCenter($filterDcId);
            $raw = $this->equipementModel->findByDataCenterId($filterDcId);
            $filterDc = $this->dataCenterModel->findById($filterDcId);
        } elseif (Auth::isAdmin()) {
            $raw = $this->equipementModel->allWithContext();
            $filterDc = null;
        } else {
            $raw = $this->equipementModel->findByUtilisateurId((int) Auth::id());
            $filterDc = null;
        }

        $equipements = array_map(
            static fn(array $eq): array => EnergyCalculator::enrich($eq),
            $raw
        );
        $totaux = EnergyCalculator::totaux($equipements);

        $this->view('equipements/index', [
            'title'        => 'Équipements',
            'equipements'  => $equipements,
            'totaux'       => $totaux,
            'filterDc'     => $filterDc,
            'dataCenters'  => $this->dataCentersDisponibles(),
            'success'      => Security::flash('success'),
            'error'        => Security::flash('error'),
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();

        $preselectDc = isset($_GET['data_center_id']) && ctype_digit((string) $_GET['data_center_id'])
            ? (string) $_GET['data_center_id']
            : '';

        if ($preselectDc !== '') {
            $this->authorizeDataCenter((int) $preselectDc);
        }

        $old = $_SESSION['_old'] ?? [];
        if ($preselectDc !== '' && empty($old['data_center_id'])) {
            $old['data_center_id'] = $preselectDc;
        }

        $this->view('equipements/form', [
            'title'       => 'Nouvel équipement',
            'equipement'  => null,
            'dataCenters' => $this->dataCentersDisponibles(),
            'categories'  => EnergyCalculator::categories(),
            'mode'        => 'create',
            'old'         => $old,
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
            Security::redirect('equipements');
        }

        $data = $this->extractInput();
        $errors = $this->validate($data);

        if ($errors === [] && !$this->canUseDataCenter((int) $data['data_center_id'])) {
            $errors['data_center_id'] = 'Data Center non autorisé.';
        }

        if ($errors !== []) {
            $_SESSION['_old'] = $data;
            $_SESSION['_errors'] = $errors;
            Security::redirect('equipements/create');
        }

        try {
            $id = $this->equipementModel->create($this->castData($data));
            Security::flash('success', 'Équipement créé. Les consommations ont été calculées automatiquement.');
            Security::redirect('equipements/' . $id);
        } catch (RuntimeException $e) {
            Security::flash('error', $e->getMessage());
            $_SESSION['_old'] = $data;
            Security::redirect('equipements/create');
        }
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $equipement = EnergyCalculator::enrich($this->authorizeAccess((int) $id));
        $coutAnnuel = EnergyCalculator::coutAnnuel(
            (float) $equipement['conso_annuelle'],
            (float) $equipement['prix_kwh_steg']
        );

        $this->view('equipements/show', [
            'title'      => $equipement['nom'],
            'equipement' => $equipement,
            'coutAnnuel' => $coutAnnuel,
            'success'    => Security::flash('success'),
            'error'      => Security::flash('error'),
        ]);
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();
        $equipement = $this->authorizeAccess((int) $id);

        $this->view('equipements/form', [
            'title'       => 'Modifier l\'équipement',
            'equipement'  => $equipement,
            'dataCenters' => $this->dataCentersDisponibles(),
            'categories'  => EnergyCalculator::categories(),
            'mode'        => 'edit',
            'old'         => $_SESSION['_old'] ?? [],
            'errors'      => $_SESSION['_errors'] ?? [],
            'success'     => Security::flash('success'),
            'error'       => Security::flash('error'),
        ]);

        unset($_SESSION['_old'], $_SESSION['_errors']);
    }

    public function update(string $id): void
    {
        Auth::requireLogin();
        $equipement = $this->authorizeAccess((int) $id);

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('equipements/' . $id);
        }

        $data = $this->extractInput();

        if (Auth::isClient()) {
            // Le client ne peut déplacer l'équipement que vers ses propres DC
            // (déjà contrôlé par canUseDataCenter)
        }

        $errors = $this->validate($data);

        if ($errors === [] && !$this->canUseDataCenter((int) $data['data_center_id'])) {
            $errors['data_center_id'] = 'Data Center non autorisé.';
        }

        if ($errors !== []) {
            $_SESSION['_old'] = $data;
            $_SESSION['_errors'] = $errors;
            Security::redirect('equipements/' . $id . '/edit');
        }

        try {
            $this->equipementModel->update((int) $id, $this->castData($data));
            Security::flash('success', 'Équipement mis à jour. Consommations recalculées.');
            Security::redirect('equipements/' . $id);
        } catch (RuntimeException $e) {
            Security::flash('error', $e->getMessage());
            $_SESSION['_old'] = $data;
            Security::redirect('equipements/' . $id . '/edit');
        }
    }

    public function delete(string $id): void
    {
        Auth::requireLogin();
        $this->authorizeAccess((int) $id);

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('equipements');
        }

        $this->equipementModel->delete((int) $id);
        Security::flash('success', 'Équipement supprimé.');
        Security::redirect('equipements');
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizeAccess(int $id): array
    {
        $equipement = $this->equipementModel->findById($id);

        if ($equipement === null) {
            Security::flash('error', 'Équipement introuvable.');
            Security::redirect('equipements');
        }

        if (Auth::isClient() && (int) $equipement['entreprise_utilisateur_id'] !== (int) Auth::id()) {
            Security::flash('error', 'Accès non autorisé à cet équipement.');
            Security::redirect('equipements');
        }

        return $equipement;
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizeDataCenter(int $id): array
    {
        $dc = $this->dataCenterModel->findById($id);

        if ($dc === null) {
            Security::flash('error', 'Data Center introuvable.');
            Security::redirect('equipements');
        }

        if (Auth::isClient() && (int) $dc['entreprise_utilisateur_id'] !== (int) Auth::id()) {
            Security::flash('error', 'Accès non autorisé.');
            Security::redirect('equipements');
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

    private function canUseDataCenter(int $dataCenterId): bool
    {
        if ($dataCenterId <= 0) {
            return false;
        }

        $dc = $this->dataCenterModel->findById($dataCenterId);
        if ($dc === null) {
            return false;
        }

        if (Auth::isAdmin()) {
            return true;
        }

        return (int) $dc['entreprise_utilisateur_id'] === (int) Auth::id();
    }

    /**
     * @return array<string, string>
     */
    private function extractInput(): array
    {
        return [
            'data_center_id'        => Security::clean($_POST['data_center_id'] ?? ''),
            'nom'                   => Security::clean($_POST['nom'] ?? ''),
            'categorie'             => Security::clean($_POST['categorie'] ?? ''),
            'fabricant'             => Security::clean($_POST['fabricant'] ?? ''),
            'modele'                => Security::clean($_POST['modele'] ?? ''),
            'quantite'              => Security::clean($_POST['quantite'] ?? ''),
            'puissance_watts'       => Security::clean($_POST['puissance_watts'] ?? ''),
            'taux_utilisation'      => Security::clean($_POST['taux_utilisation'] ?? ''),
            'heures_fonctionnement' => Security::clean($_POST['heures_fonctionnement'] ?? ''),
        ];
    }

    /**
     * @param array<string, string> $data
     * @return array<string, string>
     */
    private function validate(array $data): array
    {
        $errors = [];
        $categories = array_keys(EnergyCalculator::categories());

        if ($data['data_center_id'] === '' || !ctype_digit($data['data_center_id'])) {
            $errors['data_center_id'] = 'Veuillez sélectionner un Data Center.';
        }
        if ($data['nom'] === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if (!in_array($data['categorie'], $categories, true)) {
            $errors['categorie'] = 'Catégorie invalide.';
        }
        if ($data['fabricant'] === '') {
            $errors['fabricant'] = 'Le fabricant est obligatoire.';
        }
        if ($data['modele'] === '') {
            $errors['modele'] = 'Le modèle est obligatoire.';
        }

        $quantite = $this->toInt($data['quantite']);
        $puissance = $this->toFloat($data['puissance_watts']);
        $taux = $this->toFloat($data['taux_utilisation']);
        $heures = $this->toFloat($data['heures_fonctionnement']);

        if ($quantite === null || $quantite < 1) {
            $errors['quantite'] = 'Quantité minimale : 1.';
        }
        if ($puissance === null || $puissance <= 0) {
            $errors['puissance_watts'] = 'Puissance (W) invalide.';
        }
        if ($taux === null || $taux < 0 || $taux > 100) {
            $errors['taux_utilisation'] = 'Taux d\'utilisation entre 0 et 100 %.';
        }
        if ($heures === null || $heures <= 0 || $heures > 24) {
            $errors['heures_fonctionnement'] = 'Heures / jour entre 0 (exclu) et 24.';
        }

        return $errors;
    }

    /**
     * @param array<string, string> $data
     * @return array<string, mixed>
     */
    private function castData(array $data): array
    {
        return [
            'data_center_id'        => (int) $data['data_center_id'],
            'nom'                   => $data['nom'],
            'categorie'             => $data['categorie'],
            'fabricant'             => $data['fabricant'],
            'modele'                => $data['modele'],
            'quantite'              => (int) $data['quantite'],
            'puissance_watts'       => (float) str_replace(',', '.', $data['puissance_watts']),
            'taux_utilisation'      => (float) str_replace(',', '.', $data['taux_utilisation']),
            'heures_fonctionnement' => (float) str_replace(',', '.', $data['heures_fonctionnement']),
        ];
    }

    private function toFloat(string $value): ?float
    {
        $normalized = str_replace(',', '.', trim($value));
        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function toInt(string $value): ?int
    {
        if ($value === '' || !ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }
}
