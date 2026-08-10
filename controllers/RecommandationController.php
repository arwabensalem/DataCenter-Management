<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\RecommendationEngine;
use App\Helpers\Security;
use App\Models\DataCenter;
use App\Models\Entreprise;
use App\Models\Equipement;
use App\Models\InstallationPv;
use App\Models\Recommandation;
use RuntimeException;

/**
 * Moteur de recommandations — génération et consultation.
 */
class RecommandationController extends Controller
{
    private Recommandation $recommandationModel;
    private DataCenter $dataCenterModel;
    private Equipement $equipementModel;
    private InstallationPv $installationModel;
    private Entreprise $entrepriseModel;

    public function __construct()
    {
        $this->recommandationModel = new Recommandation();
        $this->dataCenterModel = new DataCenter();
        $this->equipementModel = new Equipement();
        $this->installationModel = new InstallationPv();
        $this->entrepriseModel = new Entreprise();
    }

    /**
     * Vue d'ensemble : liste des DC + dernières recommandations.
     */
    public function index(): void
    {
        Auth::requireLogin();

        $dataCenters = $this->dataCentersDisponibles();
        $recommandations = Auth::isAdmin()
            ? $this->recommandationModel->latestByAccessibleDcs()
            : $this->recommandationModel->latestByAccessibleDcs((int) Auth::id());

        $this->view('recommandations/index', [
            'title'            => 'Recommandations',
            'dataCenters'      => $dataCenters,
            'recommandations'  => $recommandations,
            'success'          => Security::flash('success'),
            'error'            => Security::flash('error'),
        ]);
    }

    /**
     * Détail des recommandations d'un Data Center.
     */
    public function show(string $dataCenterId): void
    {
        Auth::requireLogin();
        $dc = $this->authorizeDataCenter((int) $dataCenterId);
        $recommandations = $this->recommandationModel->findByDataCenterId((int) $dc['id']);
        $counts = $this->recommandationModel->countByPriorite((int) $dc['id']);

        $this->view('recommandations/show', [
            'title'           => 'Recommandations — ' . $dc['nom'],
            'dataCenter'      => $dc,
            'recommandations' => $recommandations,
            'counts'          => $counts,
            'success'         => Security::flash('success'),
            'error'           => Security::flash('error'),
        ]);
    }

    /**
     * Génère (ou régénère) les recommandations pour un DC.
     */
    public function generer(string $dataCenterId): void
    {
        Auth::requireLogin();
        $dc = $this->authorizeDataCenter((int) $dataCenterId);

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('recommandations');
        }

        $equipements = $this->equipementModel->findByDataCenterId((int) $dc['id']);
        $installation = $this->installationModel->findByDataCenterId((int) $dc['id']);
        $items = RecommendationEngine::generate($dc, $equipements, $installation);

        try {
            $nb = $this->recommandationModel->replaceForDataCenter((int) $dc['id'], $items);
            Security::flash(
                'success',
                $nb > 0
                    ? sprintf('%d recommandation(s) générée(s) à partir des règles métiers.', $nb)
                    : 'Aucune recommandation : la configuration actuelle est satisfaisante selon les règles.'
            );
            Security::redirect('recommandations/' . $dc['id']);
        } catch (RuntimeException $e) {
            Security::flash('error', $e->getMessage());
            Security::redirect('recommandations');
        }
    }

    /**
     * Génère pour tous les DC accessibles.
     */
    public function genererTout(): void
    {
        Auth::requireLogin();

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('recommandations');
        }

        $total = 0;
        foreach ($this->dataCentersDisponibles() as $dc) {
            $equipements = $this->equipementModel->findByDataCenterId((int) $dc['id']);
            $installation = $this->installationModel->findByDataCenterId((int) $dc['id']);
            $items = RecommendationEngine::generate($dc, $equipements, $installation);
            $total += $this->recommandationModel->replaceForDataCenter((int) $dc['id'], $items);
        }

        Security::flash('success', sprintf('Analyse terminée : %d recommandation(s) au total.', $total));
        Security::redirect('recommandations');
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizeDataCenter(int $id): array
    {
        $dc = $this->dataCenterModel->findById($id);

        if ($dc === null) {
            Security::flash('error', 'Data Center introuvable.');
            Security::redirect('recommandations');
        }

        if (Auth::isClient() && (int) $dc['entreprise_utilisateur_id'] !== (int) Auth::id()) {
            Security::flash('error', 'Accès non autorisé.');
            Security::redirect('recommandations');
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
}
