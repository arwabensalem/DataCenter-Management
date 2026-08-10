<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Impact environnemental (émissions CO₂).
 */
final class ImpactEnvironnemental
{
    /**
     * @param float $consoAnnuelleAvant kWh (réseau, avant optimisation)
     * @param float $energieStegApres   kWh STEG après optimisation / PV
     * @param float $energiePv          kWh solaire utilisé
     * @return array<string, float|string>
     */
    public static function calculer(
        float $consoAnnuelleAvant,
        float $energieStegApres,
        float $energiePv
    ): array {
        $app = require dirname(__DIR__) . '/config/app.php';
        $facteur = (float) $app['co2_kg_per_kwh'];

        $co2Avant = round($consoAnnuelleAvant * $facteur, 2);
        $co2Apres = round($energieStegApres * $facteur, 2);
        $co2EvitePv = round($energiePv * $facteur, 2);
        $co2EviteTotal = round(max(0, $co2Avant - $co2Apres), 2);
        $reductionPct = $co2Avant > 0
            ? round(($co2EviteTotal / $co2Avant) * 100, 2)
            : 0.0;

        return [
            'facteur_kg_kwh'        => $facteur,
            'co2_avant_kg'          => $co2Avant,
            'co2_apres_kg'          => $co2Apres,
            'co2_evite_pv_kg'       => $co2EvitePv,
            'co2_evite_total_kg'    => $co2EviteTotal,
            'co2_avant_tonnes'      => round($co2Avant / 1000, 3),
            'co2_apres_tonnes'      => round($co2Apres / 1000, 3),
            'co2_evite_tonnes'      => round($co2EviteTotal / 1000, 3),
            'pourcentage_reduction' => $reductionPct,
        ];
    }
}
