<?php

/**
 * Smoke tests PHP — calculateurs + fallback AI (CLI).
 * Usage: php rag/tests/php_smoke.php
 */

declare(strict_types=1);

require dirname(__DIR__, 2) . '/config/bootstrap.php';

use App\Helpers\AiContextBuilder;
use App\Helpers\AiFallback;
use App\Helpers\EnergyCalculator;
use App\Helpers\ImpactEnvironnemental;
use App\Helpers\PueCalculator;
use App\Helpers\PvCalculator;
use App\Helpers\RecommendationEngine;

$failed = 0;

function assert_true(bool $cond, string $msg): void
{
    global $failed;
    if ($cond) {
        echo "[OK] $msg\n";
    } else {
        echo "[FAIL] $msg\n";
        $failed++;
    }
}

// Energy
$eq = [
    'nom' => 'Serveur A',
    'categorie' => 'serveur',
    'puissance_watts' => 400,
    'taux_utilisation' => 70,
    'quantite' => 2,
    'heures_fonctionnement' => 24,
];
$enriched = EnergyCalculator::enrich($eq);
assert_true($enriched['conso_annuelle'] > 0, 'EnergyCalculator conso annuelle > 0');

// PUE
$equipements = [
    EnergyCalculator::enrich([
        'nom' => 'IT', 'categorie' => 'serveur', 'puissance_watts' => 500,
        'taux_utilisation' => 80, 'quantite' => 10, 'heures_fonctionnement' => 24,
    ]),
    EnergyCalculator::enrich([
        'nom' => 'Clim', 'categorie' => 'climatisation', 'puissance_watts' => 2000,
        'taux_utilisation' => 60, 'quantite' => 2, 'heures_fonctionnement' => 24,
    ]),
];
$pue = PueCalculator::calculer($equipements);
assert_true($pue['pue'] !== null && $pue['pue'] > 1, 'PueCalculator PUE > 1');

// CO2
$co2 = ImpactEnvironnemental::calculer(10000, 7000, 3000);
assert_true((float) $co2['facteur_kg_kwh'] === 0.55, 'CO2 facteur config 0.55');
assert_true((float) $co2['co2_evite_total_kg'] > 0, 'CO2 évité > 0');

// PV (signature check via reflection if needed — call safely)
assert_true(class_exists(PvCalculator::class), 'PvCalculator chargé');

// Recos
$dc = [
    'id' => 1,
    'nom' => 'DC Test',
    'localisation' => 'Tunis',
    'surface_disponible_pv' => 100,
    'prix_kwh_steg' => 0.25,
];
$recos = RecommendationEngine::generate($dc, $equipements, null);
assert_true(is_array($recos), 'RecommendationEngine retourne un tableau');

// AI context + fallback
$ctx = AiContextBuilder::build($dc, $equipements, null, [], null);
assert_true(isset($ctx['data_center']['pue']), 'AiContextBuilder contient PUE');
$fb = AiFallback::analyze($ctx, 'Pourquoi mon PUE est élevé ?');
assert_true(str_contains($fb['answer'], 'PUE') || str_contains($fb['answer'], 'déterministe'), 'AiFallback produit une analyse');
$card = AiFallback::insightsCard($ctx);
assert_true(isset($card['total_energy_kwh']), 'insightsCard métriques présentes');

echo $failed === 0 ? "\nTous les smoke tests PHP OK.\n" : "\n$failed test(s) en échec.\n";
exit($failed === 0 ? 0 : 1);
