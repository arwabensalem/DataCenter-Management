<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\EnergyCalculator;
use App\Helpers\PvCalculator;
use App\Helpers\Security;
use App\Models\DataCenter;
use App\Models\Entreprise;
use App\Models\Equipement;
use App\Models\InstallationPv;
use App\Models\RegionEnsoleillement;
use App\Models\TypePanneau;
use RuntimeException;

/**
 * Module Photovoltaïque — dimensionnement et indicateurs ROI.
 */
class PhotovoltaiqueController extends Controller
{
    private InstallationPv $installationModel;
    private DataCenter $dataCenterModel;
    private Equipement $equipementModel;
    private TypePanneau $typePanneauModel;
    private RegionEnsoleillement $regionModel;
    private Entreprise $entrepriseModel;

    public function __construct()
    {
        $this->installationModel = new InstallationPv();
        $this->dataCenterModel = new DataCenter();
        $this->equipementModel = new Equipement();
        $this->typePanneauModel = new TypePanneau();
        $this->regionModel = new RegionEnsoleillement();
        $this->entrepriseModel = new Entreprise();
    }

    /**
     * Liste des installations / Data Centers à dimensionner.
     */
    public function index(): void
    {
        Auth::requireLogin();

        $dataCenters = Auth::isAdmin()
            ? $this->dataCenterModel->allWithEntreprise()
            : $this->dataCentersClient();

        $installations = Auth::isAdmin()
            ? $this->installationModel->allWithContext()
            : $this->installationModel->allWithContext((int) Auth::id());

        $byDc = [];
        foreach ($installations as $inst) {
            $byDc[(int) $inst['data_center_id']] = $inst;
        }

        $this->view('photovoltaique/index', [
            'title'         => 'Photovoltaïque',
            'dataCenters'   => $dataCenters,
            'installations' => $byDc,
            'success'       => Security::flash('success'),
            'error'         => Security::flash('error'),
        ]);
    }

    /**
     * Formulaire de dimensionnement pour un Data Center.
     */
    public function dimensionner(string $dataCenterId): void
    {
        Auth::requireLogin();
        $dc = $this->authorizeDataCenter((int) $dataCenterId);

        $consoAnnuelle = $this->consoAnnuelleDc((int) $dc['id']);
        if ($consoAnnuelle <= 0) {
            Security::flash('error', 'Ajoutez des équipements avant de dimensionner le photovoltaïque.');
            Security::redirect('equipements?data_center_id=' . $dc['id']);
        }

        $existing = $this->installationModel->findByDataCenterId((int) $dc['id']);
        $regions = $this->regionModel->all();
        $heuresDefaut = 5.5;

        // Priorité : gouvernorat du DC (carte énergétique) → ville entreprise → installation existante
        if (!empty($dc['gouvernorat_heures'])) {
            $heuresDefaut = (float) $dc['gouvernorat_heures'];
        } else {
            $regionVille = $this->regionModel->findByVille((string) ($dc['entreprise_ville'] ?? ''));
            if ($regionVille !== null) {
                $heuresDefaut = (float) $regionVille['heures_ensoleillement'];
            }
        }
        if ($existing !== null) {
            $heuresDefaut = (float) $existing['heures_ensoleillement'];
        }

        $old = $_SESSION['_old'] ?? [];
        $preview = null;

        $typeId = (int) ($old['type_panneau_id'] ?? ($existing['type_panneau_id'] ?? 0));
        $heures = (float) ($old['heures_ensoleillement'] ?? $heuresDefaut);

        if ($typeId > 0) {
            $type = $this->typePanneauModel->findById($typeId);
            if ($type !== null) {
                $preview = PvCalculator::dimensionner([
                    'conso_annuelle_kwh'    => $consoAnnuelle,
                    'surface_disponible_pv'=> (float) $dc['surface_disponible_pv'],
                    'prix_kwh_steg'         => (float) $dc['prix_kwh_steg'],
                    'puissance_wc'         => (float) $type['puissance_wc'],
                    'rendement'            => (float) $type['rendement'],
                    'prix_unitaire'        => (float) $type['prix_unitaire'],
                    'surface_m2'           => (float) $type['surface_m2'],
                    'heures_ensoleillement'=> $heures,
                ]);
            }
        }

        $this->view('photovoltaique/form', [
            'title'          => 'Dimensionnement PV',
            'dataCenter'     => $dc,
            'typesPanneaux'  => $this->typePanneauModel->allActifs(),
            'regions'        => $regions,
            'existing'       => $existing,
            'consoAnnuelle'  => $consoAnnuelle,
            'heuresDefaut'   => $heuresDefaut,
            'preview'        => $preview,
            'old'            => $old,
            'errors'         => $_SESSION['_errors'] ?? [],
            'success'        => Security::flash('success'),
            'error'          => Security::flash('error'),
        ]);

        unset($_SESSION['_old'], $_SESSION['_errors']);
    }

    /**
     * Calcule et enregistre le dimensionnement.
     */
    public function calculer(string $dataCenterId): void
    {
        Auth::requireLogin();
        $dc = $this->authorizeDataCenter((int) $dataCenterId);

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('photovoltaique');
        }

        $typeId = Security::clean($_POST['type_panneau_id'] ?? '');
        $heuresRaw = Security::clean($_POST['heures_ensoleillement'] ?? '');
        $errors = [];

        if ($typeId === '' || !ctype_digit($typeId)) {
            $errors['type_panneau_id'] = 'Sélectionnez un type de panneau.';
        }

        $heures = $this->toFloat($heuresRaw);
        if ($heures === null || $heures <= 0 || $heures > 24) {
            $errors['heures_ensoleillement'] = 'Heures d\'ensoleillement entre 0 (exclu) et 24.';
        }

        $type = null;
        if ($errors === []) {
            $type = $this->typePanneauModel->findById((int) $typeId);
            if ($type === null || $type['statut'] !== 'actif') {
                $errors['type_panneau_id'] = 'Type de panneau invalide.';
            }
        }

        $consoAnnuelle = $this->consoAnnuelleDc((int) $dc['id']);
        if ($consoAnnuelle <= 0) {
            $errors['conso'] = 'Aucune consommation à couvrir.';
        }

        if ($errors !== []) {
            $_SESSION['_old'] = [
                'type_panneau_id'       => $typeId,
                'heures_ensoleillement' => $heuresRaw,
            ];
            $_SESSION['_errors'] = $errors;
            Security::redirect('photovoltaique/dimensionner/' . $dataCenterId);
        }

        $result = PvCalculator::dimensionner([
            'conso_annuelle_kwh'     => $consoAnnuelle,
            'surface_disponible_pv' => (float) $dc['surface_disponible_pv'],
            'prix_kwh_steg'          => (float) $dc['prix_kwh_steg'],
            'puissance_wc'          => (float) $type['puissance_wc'],
            'rendement'             => (float) $type['rendement'],
            'prix_unitaire'         => (float) $type['prix_unitaire'],
            'surface_m2'            => (float) $type['surface_m2'],
            'heures_ensoleillement' => (float) $heures,
        ]);

        if ((int) $result['nombre_panneaux'] === 0) {
            Security::flash('error', 'Surface disponible insuffisante pour installer des panneaux, ou données invalides.');
            $_SESSION['_old'] = [
                'type_panneau_id'       => $typeId,
                'heures_ensoleillement' => $heuresRaw,
            ];
            Security::redirect('photovoltaique/dimensionner/' . $dataCenterId);
        }

        try {
            $this->installationModel->upsert((int) $dc['id'], [
                'type_panneau_id'          => (int) $typeId,
                'heures_ensoleillement'    => (float) $heures,
                'puissance_necessaire_kwc' => $result['puissance_necessaire_kwc'],
                'nombre_panneaux'          => $result['nombre_panneaux'],
                'surface_necessaire'       => $result['surface_necessaire'],
                'production_annuelle_kwh'  => $result['production_annuelle_kwh'],
                'taux_couverture'          => $result['taux_couverture'],
                'energie_pv_kwh'           => $result['energie_pv_kwh'],
                'energie_steg_kwh'         => $result['energie_steg_kwh'],
                'cout_installation'        => $result['cout_installation'],
                'roi_pourcentage'          => $result['roi_pourcentage'],
                'temps_amortissement'      => $result['temps_amortissement'],
            ]);

            $msg = 'Dimensionnement photovoltaïque calculé et enregistré.';
            if ($result['surface_insuffisante']) {
                $msg .= ' Attention : la surface disponible ne permet pas une couverture à 100 %.';
            }
            Security::flash('success', $msg);
            Security::redirect('photovoltaique/resultats/' . $dataCenterId);
        } catch (RuntimeException $e) {
            Security::flash('error', $e->getMessage());
            Security::redirect('photovoltaique/dimensionner/' . $dataCenterId);
        }
    }

    /**
     * Affiche les résultats du dimensionnement.
     */
    public function resultats(string $dataCenterId): void
    {
        Auth::requireLogin();
        $dc = $this->authorizeDataCenter((int) $dataCenterId);
        $installation = $this->installationModel->findByDataCenterId((int) $dc['id']);

        if ($installation === null) {
            Security::flash('error', 'Aucun dimensionnement pour ce Data Center.');
            Security::redirect('photovoltaique/dimensionner/' . $dataCenterId);
        }

        $consoAnnuelle = $this->consoAnnuelleDc((int) $dc['id']);
        $app = require dirname(__DIR__) . '/config/app.php';
        $co2 = round((float) $installation['energie_pv_kwh'] * (float) $app['co2_kg_per_kwh'], 2);
        $economie = round(
            (float) $installation['energie_pv_kwh'] * (float) $dc['prix_kwh_steg'],
            2
        );

        $this->view('photovoltaique/resultats', [
            'title'         => 'Résultats PV — ' . $dc['nom'],
            'dataCenter'    => $dc,
            'installation'  => $installation,
            'consoAnnuelle' => $consoAnnuelle,
            'co2Evite'      => $co2,
            'economie'      => $economie,
            'success'       => Security::flash('success'),
            'error'         => Security::flash('error'),
        ]);
    }

    /**
     * Suppression d'un dimensionnement.
     */
    public function delete(string $dataCenterId): void
    {
        Auth::requireLogin();
        $this->authorizeDataCenter((int) $dataCenterId);

        if (!$this->isPost() || !Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Security::flash('error', 'Requête invalide.');
            Security::redirect('photovoltaique');
        }

        $this->installationModel->deleteByDataCenterId((int) $dataCenterId);
        Security::flash('success', 'Dimensionnement photovoltaïque supprimé.');
        Security::redirect('photovoltaique');
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizeDataCenter(int $id): array
    {
        $dc = $this->dataCenterModel->findById($id);

        if ($dc === null) {
            Security::flash('error', 'Data Center introuvable.');
            Security::redirect('photovoltaique');
        }

        if (Auth::isClient() && (int) $dc['entreprise_utilisateur_id'] !== (int) Auth::id()) {
            Security::flash('error', 'Accès non autorisé.');
            Security::redirect('photovoltaique');
        }

        return $dc;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function dataCentersClient(): array
    {
        $entreprise = $this->entrepriseModel->findByUtilisateurId((int) Auth::id());
        if ($entreprise === null) {
            return [];
        }

        return $this->dataCenterModel->findByEntrepriseId((int) $entreprise['id']);
    }

    private function consoAnnuelleDc(int $dataCenterId): float
    {
        $equipements = $this->equipementModel->findByDataCenterId($dataCenterId);
        $totaux = EnergyCalculator::totaux($equipements);

        return (float) $totaux['annuelle'];
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
