<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Dimensionnement photovoltaïque et indicateurs financiers.
 * Tous les résultats sont calculés — jamais saisis manuellement.
 */
final class PvCalculator
{
    /**
     * Production annuelle d'un panneau (kWh).
     */
    public static function productionPanneauAnnuelle(
        float $puissanceWc,
        float $heuresEnsoleillement,
        float $rendement
    ): float {
        // (Wc × h/j × 365 × rendement%) / (1000 × 100)
        return ($puissanceWc * $heuresEnsoleillement * 365 * $rendement) / 100000;
    }

    /**
     * Dimensionne une installation PV pour un Data Center.
     *
     * @param array{
     *   conso_annuelle_kwh: float,
     *   surface_disponible_pv: float,
     *   prix_kwh_steg: float,
     *   puissance_wc: float,
     *   rendement: float,
     *   prix_unitaire: float,
     *   surface_m2: float,
     *   heures_ensoleillement: float
     * } $input
     * @return array<string, float|int>
     */
    public static function dimensionner(array $input): array
    {
        $consoAn = max(0.0, (float) $input['conso_annuelle_kwh']);
        $surfaceDispo = max(0.0, (float) $input['surface_disponible_pv']);
        $prixKwh = max(0.0, (float) $input['prix_kwh_steg']);
        $puissanceWc = (float) $input['puissance_wc'];
        $rendement = (float) $input['rendement'];
        $prixUnitaire = (float) $input['prix_unitaire'];
        $surfacePanneau = (float) $input['surface_m2'];
        $heures = (float) $input['heures_ensoleillement'];

        $prodParPanneau = self::productionPanneauAnnuelle($puissanceWc, $heures, $rendement);

        // Panneaux théoriques pour 100 % de couverture
        $panneauxNecessaires = $prodParPanneau > 0
            ? (int) ceil($consoAn / $prodParPanneau)
            : 0;

        // Puissance crête nécessaire (kWc) pour couvrir 100 %
        $puissanceNecessaireKwc = round(($panneauxNecessaires * $puissanceWc) / 1000, 4);

        // Contrainte surface disponible
        $maxPanneauxSurface = $surfacePanneau > 0
            ? (int) floor($surfaceDispo / $surfacePanneau)
            : 0;

        $nombrePanneaux = min($panneauxNecessaires, $maxPanneauxSurface);
        // Au moins 0 ; si conso = 0, 0 panneaux
        $nombrePanneaux = max(0, $nombrePanneaux);

        $surfaceNecessaire = round($nombrePanneaux * $surfacePanneau, 2);
        $productionAnnuelle = round($nombrePanneaux * $prodParPanneau, 2);

        $tauxCouverture = $consoAn > 0
            ? round(min(100, ($productionAnnuelle / $consoAn) * 100), 2)
            : 0.0;

        $energiePv = round(min($productionAnnuelle, $consoAn), 2);
        $energieSteg = round(max(0, $consoAn - $energiePv), 2);

        $coutInstallation = round($nombrePanneaux * $prixUnitaire, 2);
        $economieAnnuelle = round($energiePv * $prixKwh, 2);

        $roi = $coutInstallation > 0
            ? round(($economieAnnuelle / $coutInstallation) * 100, 2)
            : 0.0;

        $amortissement = ($economieAnnuelle > 0 && $coutInstallation > 0)
            ? round($coutInstallation / $economieAnnuelle, 2)
            : 0.0;

        $app = require dirname(__DIR__) . '/config/app.php';
        $co2Evite = round($energiePv * (float) $app['co2_kg_per_kwh'], 2);

        return [
            'puissance_necessaire_kwc' => $puissanceNecessaireKwc,
            'panneaux_theoriques'      => $panneauxNecessaires,
            'max_panneaux_surface'     => $maxPanneauxSurface,
            'nombre_panneaux'          => $nombrePanneaux,
            'surface_necessaire'       => $surfaceNecessaire,
            'production_annuelle_kwh'  => $productionAnnuelle,
            'taux_couverture'          => $tauxCouverture,
            'energie_pv_kwh'           => $energiePv,
            'energie_steg_kwh'         => $energieSteg,
            'cout_installation'        => $coutInstallation,
            'economie_annuelle'        => $economieAnnuelle,
            'roi_pourcentage'          => $roi,
            'temps_amortissement'      => $amortissement,
            'co2_evite_kg'             => $co2Evite,
            'conso_annuelle_kwh'       => round($consoAn, 2),
            'surface_insuffisante'     => $panneauxNecessaires > $maxPanneauxSurface,
        ];
    }
}
