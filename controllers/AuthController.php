<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Models\Utilisateur;

/**
 * Authentification : connexion, déconnexion, gestion des rôles (session).
 */
class AuthController extends Controller
{
    private Utilisateur $utilisateurModel;

    public function __construct()
    {
        $this->utilisateurModel = new Utilisateur();
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLogin(): void
    {
        if (Auth::check()) {
            Security::redirect('dashboard');
        }

        $this->view('auth/login', [
            'title' => 'Connexion',
            'error' => Security::flash('error'),
            'success' => Security::flash('success'),
        ], 'layouts/guest');
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function login(): void
    {
        if (!$this->isPost()) {
            Security::redirect('login');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');
            Security::redirect('login');
        }

        $email = Security::clean($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            Security::flash('error', 'Veuillez renseigner l\'e-mail et le mot de passe.');
            Security::redirect('login');
        }

        if (!Security::isValidEmail($email)) {
            Security::flash('error', 'Adresse e-mail invalide.');
            Security::redirect('login');
        }

        $user = $this->utilisateurModel->attemptLogin($email, $password);

        if ($user === null) {
            // Message générique pour éviter l'énumération de comptes
            Security::flash('error', 'Identifiants incorrects ou compte inactif.');
            Security::redirect('login');
        }

        Auth::login($user);
        Security::flash('success', 'Bienvenue, ' . $user['prenom'] . ' !');
        Security::redirect('dashboard');
    }

    /**
     * Déconnexion.
     */
    public function logout(): void
    {
        Auth::logout();
        Security::flash('success', 'Vous êtes déconnecté.');
        Security::redirect('login');
    }
}
