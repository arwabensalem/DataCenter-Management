<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Models\DataCenter;
use App\Models\Entreprise;
use App\Models\Gouvernorat;
use RuntimeException;

/**
 * CRUD Data Centers — lié à une entreprise.
 */
class DataCenterController extends Controller
{
    private DataCenter $dataCenterModel;
    private Entreprise $entrepriseModel;
    private Gouvernorat $gouvernoratModel;

    public function __construct()
    {
        $this->dataCenterModel = new DataCenter();
        $this->entrepriseModel = new Entreprise();
        $this->gouvernoratModel = new Gouvernorat();
    }

    /**
     * Liste des Data Centers (filtrée pour le client).
     */
    public function index(): void
    {
        Auth::requireLogin();

        if (Auth::isAdmin()) {
            $dataCenters = $this->dataCenterModel->allWithEntreprise();
        } else {
            $entreprise = $this->entrepriseModel->findByUtilisateurId((int) Auth::id());
            if ($entreprise === null) {
                Security::flash('error', 'Aucune entreprise associée à votre compte.');
                Security::redirect('dashboard');
            }
            $dataCenters = $this->dataCenterModel->findByEntrepriseId((int) $entreprise['id']);
        }

        $this->view('data_centers/index', [
            'title'        => 'Data Centers',
            'dataCenters'  => $dataCenters,
            'success'      => Security::flash('success'),
            'error'        => Security::flash('error'),
        ]);
    }

    /**
     * Formulaire de création.
     */
    public function create(): void
    {
        Auth::requireLogin();

        $this->view('data_centers/form', [
            'title'        => 'Nouveau Data Center',
            'dataCenter'   => null,
            'entreprises'  => $this->entreprisesDisponibles(),
            'gouvernorats' => $this->gouvernoratModel->all(),
            'mode'         => 'create',
            'old'          => $_SESSION['_old'] ?? [],
            'errors'       => $_SESSION['_errors'] ?? [],
            'success'      => Security::flash('success'),
            'error'        => Security::flash('error'),
        ]);

        unset($_SESSION['_old'], $_SESSION['_errors']);
    }

    /**
     * Enregistrement.
     */
    public function store(): void
    {
        Auth::requireLogin();

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('data-centers');
        }

        $data = $this->extractInput();
        $errors = $this->validate($data);

        if (!$this->canUseEntreprise((int) $data['entreprise_id'])) {
            $errors['entreprise_id'] = 'Entreprise non autorisée.';
        }

        if ($errors !== []) {
            $_SESSION['_old'] = $data;
            $_SESSION['_errors'] = $errors;
            Security::redirect('data-centers/create');
        }

        try {
            $id = $this->dataCenterModel->create([
                'entreprise_id'         => (int) $data['entreprise_id'],
                'nom'                   => $data['nom'],
                'localisation'          => $data['localisation'],
                'gouvernorat_id'        => $data['gouvernorat_id'] !== '' ? (int) $data['gouvernorat_id'] : null,
                'surface_totale'        => (float) $data['surface_totale'],
                'surface_disponible_pv' => (float) $data['surface_disponible_pv'],
                'prix_kwh_steg'         => (float) $data['prix_kwh_steg'],
                'heures_fonctionnement' => (float) $data['heures_fonctionnement'],
            ]);

            Security::flash('success', 'Data Center créé avec succès.');
            Security::redirect('data-centers/' . $id);
        } catch (RuntimeException $e) {
            Security::flash('error', $e->getMessage());
            $_SESSION['_old'] = $data;
            Security::redirect('data-centers/create');
        }
    }

    /**
     * Fiche détail.
     */
    public function show(string $id): void
    {
        Auth::requireLogin();
        $dataCenter = $this->authorizeAccess((int) $id);

        $this->view('data_centers/show', [
            'title'      => $dataCenter['nom'],
            'dataCenter' => $dataCenter,
            'success'    => Security::flash('success'),
            'error'      => Security::flash('error'),
        ]);
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(string $id): void
    {
        Auth::requireLogin();
        $dataCenter = $this->authorizeAccess((int) $id);

        $this->view('data_centers/form', [
            'title'       => 'Modifier le Data Center',
            'dataCenter'  => $dataCenter,
            'entreprises' => $this->entreprisesDisponibles(),
            'gouvernorats' => $this->gouvernoratModel->all(),
            'mode'        => 'edit',
            'old'         => $_SESSION['_old'] ?? [],
            'errors'      => $_SESSION['_errors'] ?? [],
            'success'     => Security::flash('success'),
            'error'       => Security::flash('error'),
        ]);

        unset($_SESSION['_old'], $_SESSION['_errors']);
    }

    /**
     * Mise à jour.
     */
    public function update(string $id): void
    {
        Auth::requireLogin();
        $dataCenter = $this->authorizeAccess((int) $id);

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('data-centers/' . $id);
        }

        $data = $this->extractInput();

        // Le client ne peut pas changer d'entreprise
        if (Auth::isClient()) {
            $data['entreprise_id'] = (string) $dataCenter['entreprise_id'];
        }

        $errors = $this->validate($data);

        if (!$this->canUseEntreprise((int) $data['entreprise_id'])) {
            $errors['entreprise_id'] = 'Entreprise non autorisée.';
        }

        if ($errors !== []) {
            $_SESSION['_old'] = $data;
            $_SESSION['_errors'] = $errors;
            Security::redirect('data-centers/' . $id . '/edit');
        }

        try {
            $this->dataCenterModel->update((int) $id, [
                'entreprise_id'         => (int) $data['entreprise_id'],
                'nom'                   => $data['nom'],
                'localisation'          => $data['localisation'],
                'gouvernorat_id'        => $data['gouvernorat_id'] !== '' ? (int) $data['gouvernorat_id'] : null,
                'surface_totale'        => (float) $data['surface_totale'],
                'surface_disponible_pv' => (float) $data['surface_disponible_pv'],
                'prix_kwh_steg'         => (float) $data['prix_kwh_steg'],
                'heures_fonctionnement' => (float) $data['heures_fonctionnement'],
            ]);

            Security::flash('success', 'Data Center mis à jour.');
            Security::redirect('data-centers/' . $id);
        } catch (RuntimeException $e) {
            Security::flash('error', $e->getMessage());
            $_SESSION['_old'] = $data;
            Security::redirect('data-centers/' . $id . '/edit');
        }
    }

    /**
     * Suppression.
     */
    public function delete(string $id): void
    {
        Auth::requireLogin();
        $this->authorizeAccess((int) $id);

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('data-centers');
        }

        $this->dataCenterModel->delete((int) $id);
        Security::flash('success', 'Data Center supprimé.');
        Security::redirect('data-centers');
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizeAccess(int $id): array
    {
        $dataCenter = $this->dataCenterModel->findById($id);

        if ($dataCenter === null) {
            Security::flash('error', 'Data Center introuvable.');
            Security::redirect('data-centers');
        }

        if (Auth::isClient() && (int) $dataCenter['entreprise_utilisateur_id'] !== (int) Auth::id()) {
            Security::flash('error', 'Accès non autorisé à ce Data Center.');
            Security::redirect('data-centers');
        }

        return $dataCenter;
    }

    /**
     * Entreprises sélectionnables selon le rôle.
     *
     * @return list<array<string, mixed>>
     */
    private function entreprisesDisponibles(): array
    {
        if (Auth::isAdmin()) {
            return $this->entrepriseModel->allWithDetails();
        }

        $entreprise = $this->entrepriseModel->findByUtilisateurId((int) Auth::id());
        return $entreprise !== null ? [$entreprise] : [];
    }

    private function canUseEntreprise(int $entrepriseId): bool
    {
        if ($entrepriseId <= 0) {
            return false;
        }

        if (Auth::isAdmin()) {
            return $this->entrepriseModel->findById($entrepriseId) !== null;
        }

        $entreprise = $this->entrepriseModel->findByUtilisateurId((int) Auth::id());
        return $entreprise !== null && (int) $entreprise['id'] === $entrepriseId;
    }

    /**
     * @return array<string, string>
     */
    private function extractInput(): array
    {
        return [
            'entreprise_id'         => Security::clean($_POST['entreprise_id'] ?? ''),
            'nom'                   => Security::clean($_POST['nom'] ?? ''),
            'localisation'          => Security::clean($_POST['localisation'] ?? ''),
            'gouvernorat_id'        => Security::clean($_POST['gouvernorat_id'] ?? ''),
            'surface_totale'        => Security::clean($_POST['surface_totale'] ?? ''),
            'surface_disponible_pv' => Security::clean($_POST['surface_disponible_pv'] ?? ''),
            'prix_kwh_steg'         => Security::clean($_POST['prix_kwh_steg'] ?? ''),
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

        if ($data['entreprise_id'] === '' || !ctype_digit($data['entreprise_id'])) {
            $errors['entreprise_id'] = 'Veuillez sélectionner une entreprise.';
        }
        if ($data['nom'] === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if ($data['localisation'] === '') {
            $errors['localisation'] = 'La localisation est obligatoire.';
        }
        if ($data['gouvernorat_id'] === '' || !ctype_digit($data['gouvernorat_id'])) {
            $errors['gouvernorat_id'] = 'Sélectionnez un gouvernorat (carte énergétique).';
        }

        $surfaceTotale = $this->toFloat($data['surface_totale']);
        $surfacePv = $this->toFloat($data['surface_disponible_pv']);
        $prix = $this->toFloat($data['prix_kwh_steg']);
        $heures = $this->toFloat($data['heures_fonctionnement']);

        if ($surfaceTotale === null || $surfaceTotale <= 0) {
            $errors['surface_totale'] = 'Surface totale invalide (m² > 0).';
        }
        if ($surfacePv === null || $surfacePv < 0) {
            $errors['surface_disponible_pv'] = 'Surface PV invalide.';
        }
        if ($surfaceTotale !== null && $surfacePv !== null && $surfacePv > $surfaceTotale) {
            $errors['surface_disponible_pv'] = 'La surface PV ne peut pas dépasser la surface totale.';
        }
        if ($prix === null || $prix <= 0) {
            $errors['prix_kwh_steg'] = 'Prix du kWh STEG invalide.';
        }
        if ($heures === null || $heures <= 0 || $heures > 24) {
            $errors['heures_fonctionnement'] = 'Heures / jour entre 0 (exclu) et 24.';
        }

        return $errors;
    }

    private function toFloat(string $value): ?float
    {
        $normalized = str_replace(',', '.', trim($value));
        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }
}
