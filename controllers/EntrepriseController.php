<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Models\Entreprise;
use RuntimeException;

/**
 * CRUD Entreprises + création du compte client associé.
 */
class EntrepriseController extends Controller
{
    private Entreprise $entrepriseModel;

    public function __construct()
    {
        $this->entrepriseModel = new Entreprise();
    }

    /**
     * Liste (admin) ou fiche de mon entreprise (client).
     */
    public function index(): void
    {
        Auth::requireLogin();

        if (Auth::isClient()) {
            $entreprise = $this->entrepriseModel->findByUtilisateurId((int) Auth::id());
            if ($entreprise === null) {
                Security::flash('error', 'Aucune entreprise n\'est associée à votre compte.');
                Security::redirect('dashboard');
            }
            Security::redirect('entreprises/' . $entreprise['id']);
        }

        Auth::requireRole('admin');

        $this->view('entreprises/index', [
            'title'       => 'Entreprises',
            'entreprises' => $this->entrepriseModel->allWithDetails(),
            'success'     => Security::flash('success'),
            'error'       => Security::flash('error'),
        ]);
    }

    /**
     * Formulaire de création (admin uniquement).
     */
    public function create(): void
    {
        Auth::requireRole('admin');

        $this->view('entreprises/form', [
            'title'      => 'Nouvelle entreprise',
            'entreprise' => null,
            'mode'       => 'create',
            'old'        => $_SESSION['_old'] ?? [],
            'errors'     => $_SESSION['_errors'] ?? [],
            'success'    => Security::flash('success'),
            'error'      => Security::flash('error'),
        ]);

        unset($_SESSION['_old'], $_SESSION['_errors']);
    }

    /**
     * Enregistrement (admin) : client + entreprise.
     */
    public function store(): void
    {
        Auth::requireRole('admin');

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('entreprises');
        }

        $data = $this->extractInput();
        $errors = $this->validate($data, true);

        if ($this->entrepriseModel->emailUtilisateurExists($data['client_email'])) {
            $errors['client_email'] = 'Cet e-mail est déjà utilisé par un compte.';
        }

        if ($errors !== []) {
            $_SESSION['_old'] = $data;
            $_SESSION['_errors'] = $errors;
            Security::redirect('entreprises/create');
        }

        try {
            $id = $this->entrepriseModel->createWithClient(
                [
                    'nom'       => $data['nom'],
                    'adresse'   => $data['adresse'],
                    'ville'     => $data['ville'],
                    'telephone' => $data['telephone'],
                    'email'     => $data['email'],
                ],
                [
                    'nom'       => $data['client_nom'],
                    'prenom'    => $data['client_prenom'],
                    'email'     => $data['client_email'],
                    'password'  => $data['client_password'],
                    'telephone' => $data['client_telephone'],
                ]
            );

            Security::flash('success', 'Entreprise et compte client créés avec succès.');
            Security::redirect('entreprises/' . $id);
        } catch (RuntimeException $e) {
            Security::flash('error', $e->getMessage());
            $_SESSION['_old'] = $data;
            Security::redirect('entreprises/create');
        }
    }

    /**
     * Fiche détail.
     */
    public function show(string $id): void
    {
        Auth::requireLogin();
        $entreprise = $this->authorizeAccess((int) $id);

        $this->view('entreprises/show', [
            'title'      => $entreprise['nom'],
            'entreprise' => $entreprise,
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
        $entreprise = $this->authorizeAccess((int) $id);

        $this->view('entreprises/form', [
            'title'      => 'Modifier l\'entreprise',
            'entreprise' => $entreprise,
            'mode'       => 'edit',
            'old'        => $_SESSION['_old'] ?? [],
            'errors'     => $_SESSION['_errors'] ?? [],
            'success'    => Security::flash('success'),
            'error'      => Security::flash('error'),
        ]);

        unset($_SESSION['_old'], $_SESSION['_errors']);
    }

    /**
     * Mise à jour des infos entreprise.
     */
    public function update(string $id): void
    {
        Auth::requireLogin();
        $entreprise = $this->authorizeAccess((int) $id);

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('entreprises/' . $id);
        }

        $data = $this->extractInput(false);
        $errors = $this->validate($data, false);

        if ($errors !== []) {
            $_SESSION['_old'] = $data;
            $_SESSION['_errors'] = $errors;
            Security::redirect('entreprises/' . $id . '/edit');
        }

        $this->entrepriseModel->update((int) $entreprise['id'], [
            'nom'       => $data['nom'],
            'adresse'   => $data['adresse'],
            'ville'     => $data['ville'],
            'telephone' => $data['telephone'],
            'email'     => $data['email'],
        ]);

        Security::flash('success', 'Entreprise mise à jour.');
        Security::redirect('entreprises/' . $id);
    }

    /**
     * Suppression (admin uniquement).
     */
    public function delete(string $id): void
    {
        Auth::requireRole('admin');

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('entreprises');
        }

        $entreprise = $this->entrepriseModel->findById((int) $id);
        if ($entreprise === null) {
            Security::flash('error', 'Entreprise introuvable.');
            Security::redirect('entreprises');
        }

        try {
            $this->entrepriseModel->delete((int) $id);
            Security::flash('success', 'Entreprise et compte client supprimés.');
        } catch (RuntimeException $e) {
            Security::flash('error', $e->getMessage());
        }

        Security::redirect('entreprises');
    }

    /**
     * Contrôle d'accès : admin = tout ; client = sa propre entreprise.
     *
     * @return array<string, mixed>
     */
    private function authorizeAccess(int $id): array
    {
        $entreprise = $this->entrepriseModel->findById($id);

        if ($entreprise === null) {
            Security::flash('error', 'Entreprise introuvable.');
            Security::redirect(Auth::isAdmin() ? 'entreprises' : 'dashboard');
        }

        if (Auth::isClient() && (int) $entreprise['utilisateur_id'] !== (int) Auth::id()) {
            Security::flash('error', 'Accès non autorisé à cette entreprise.');
            Security::redirect('dashboard');
        }

        return $entreprise;
    }

    /**
     * @return array<string, string>
     */
    private function extractInput(bool $withClient = true): array
    {
        $data = [
            'nom'       => Security::clean($_POST['nom'] ?? ''),
            'adresse'   => Security::clean($_POST['adresse'] ?? ''),
            'ville'     => Security::clean($_POST['ville'] ?? ''),
            'telephone' => Security::clean($_POST['telephone'] ?? ''),
            'email'     => Security::clean($_POST['email'] ?? ''),
        ];

        if ($withClient) {
            $data['client_nom'] = Security::clean($_POST['client_nom'] ?? '');
            $data['client_prenom'] = Security::clean($_POST['client_prenom'] ?? '');
            $data['client_email'] = Security::clean($_POST['client_email'] ?? '');
            $data['client_telephone'] = Security::clean($_POST['client_telephone'] ?? '');
            $data['client_password'] = (string) ($_POST['client_password'] ?? '');
            $data['client_password_confirm'] = (string) ($_POST['client_password_confirm'] ?? '');
        }

        return $data;
    }

    /**
     * @param array<string, string> $data
     * @return array<string, string>
     */
    private function validate(array $data, bool $withClient): array
    {
        $errors = [];

        if ($data['nom'] === '') {
            $errors['nom'] = 'Le nom de l\'entreprise est obligatoire.';
        }
        if ($data['adresse'] === '') {
            $errors['adresse'] = 'L\'adresse est obligatoire.';
        }
        if ($data['ville'] === '') {
            $errors['ville'] = 'La ville est obligatoire.';
        }
        if ($data['telephone'] === '') {
            $errors['telephone'] = 'Le téléphone est obligatoire.';
        }
        if ($data['email'] === '' || !Security::isValidEmail($data['email'])) {
            $errors['email'] = 'E-mail entreprise invalide.';
        }

        if ($withClient) {
            if ($data['client_nom'] === '') {
                $errors['client_nom'] = 'Le nom du contact est obligatoire.';
            }
            if ($data['client_prenom'] === '') {
                $errors['client_prenom'] = 'Le prénom du contact est obligatoire.';
            }
            if ($data['client_email'] === '' || !Security::isValidEmail($data['client_email'])) {
                $errors['client_email'] = 'E-mail du compte client invalide.';
            }
            if (strlen($data['client_password']) < 8) {
                $errors['client_password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
            }
            if ($data['client_password'] !== $data['client_password_confirm']) {
                $errors['client_password_confirm'] = 'La confirmation du mot de passe ne correspond pas.';
            }
        }

        return $errors;
    }
}
