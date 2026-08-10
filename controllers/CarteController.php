<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Models\Gouvernorat;

/**
 * Carte énergétique interactive de la Tunisie.
 */
class CarteController extends Controller
{
    private Gouvernorat $gouvernoratModel;

    public function __construct()
    {
        $this->gouvernoratModel = new Gouvernorat();
    }

    public function index(): void
    {
        Auth::requireLogin();

        $gouvernorats = $this->gouvernoratModel->all();
        $mapData = $this->gouvernoratModel->forMap();

        $this->view('carte/index', [
            'title'        => 'Carte énergétique',
            'gouvernorats' => $gouvernorats,
            'mapData'      => $mapData,
            'success'      => Security::flash('success'),
            'error'        => Security::flash('error'),
        ]);
    }

    /**
     * API JSON d'un gouvernorat (préremplissage PV / DC).
     */
    public function api(string $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $g = $this->gouvernoratModel->findById((int) $id);
        if ($g === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Gouvernorat introuvable']);
            exit;
        }

        echo json_encode([
            'id' => (int) $g['id'],
            'nom' => $g['nom'],
            'irradiation_kwh_m2_an' => (float) $g['irradiation_kwh_m2_an'],
            'heures_ensoleillement' => (float) $g['heures_ensoleillement'],
            'potentiel_pv' => $g['potentiel_pv'],
            'temperature_moyenne' => $g['temperature_moyenne'] !== null
                ? (float) $g['temperature_moyenne']
                : null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
