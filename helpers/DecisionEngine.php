<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Comparaison multi-scénarios + moteur d'aide à la décision.
 */
final class DecisionEngine
{
    /**
     * Construit les métriques d'un scénario à partir d'un état (snapshot).
     *
     * @param array<string, mixed> $etat
     * @param array<string, mixed>|null $typePanneau
     * @return array<string, mixed>
     */
    public static function metriquesScenario(array $etat, ?array $typePanneau = null): array
    {
        $app = require dirname(__DIR__) . '/config/app.php';
        $facteur = (float) $app['co2_kg_per_kwh'];

        $conso = (float) ($etat['conso_annuelle_kwh'] ?? 0);
        $energiePv = (float) ($etat['energie_pv_kwh'] ?? 0);
        $energieSteg = (float) ($etat['energie_steg_kwh'] ?? $conso);
        $production = (float) ($etat['production_pv_kwh'] ?? 0);
        $couverture = (float) ($etat['taux_couverture'] ?? 0);
        $prixKwh = (float) ($etat['prix_kwh_steg'] ?? 0.25);
        $nbPanneaux = (int) ($etat['nombre_panneaux'] ?? 0);

        $coutAnnuel = EnergyCalculator::coutAnnuel($energieSteg, $prixKwh);
        $economie = EnergyCalculator::coutAnnuel($energiePv, $prixKwh);

        $coutInstall = 0.0;
        if ($typePanneau !== null && $nbPanneaux > 0) {
            $coutInstall = $nbPanneaux * (float) $typePanneau['prix_unitaire'];
        } elseif (isset($etat['cout_installation'])) {
            $coutInstall = (float) $etat['cout_installation'];
        }

        $roi = $coutInstall > 0 ? round(($economie / $coutInstall) * 100, 2) : 0.0;
        $amort = ($economie > 0 && $coutInstall > 0) ? round($coutInstall / $economie, 2) : 0.0;
        $co2Evite = round($energiePv * $facteur, 2);

        return [
            'consommation_annuelle' => round($conso, 2),
            'production_pv'         => round($production, 2),
            'couverture_solaire'    => $couverture,
            'energie_steg'          => round($energieSteg, 2),
            'cout_annuel'           => $coutAnnuel,
            'roi'                   => $roi,
            'temps_amortissement'   => $amort,
            'co2_evite'             => $co2Evite,
            'nombre_panneaux'       => $nbPanneaux,
            'score'                 => self::score([
                'dependance_steg' => $conso > 0 ? ($energieSteg / $conso) * 100 : 100,
                'couverture'      => $couverture,
                'co2_evite'       => $co2Evite,
                'amort'           => $amort,
                'cout_annuel'     => $coutAnnuel,
            ]),
        ];
    }

    /**
     * Score composite (plus élevé = meilleur).
     *
     * @param array<string, float> $m
     */
    public static function score(array $m): float
    {
        $score = 0.0;
        $score += min(100, (float) $m['couverture']) * 0.35;
        $score += max(0, 100 - (float) $m['dependance_steg']) * 0.25;
        $score += min(100, ((float) $m['co2_evite'] / 1000)) * 0.20; // ~tonnes
        // Amortissement : meilleur si entre 4 et 10 ans
        $amort = (float) $m['amort'];
        if ($amort > 0 && $amort <= 10) {
            $score += (11 - min(10, $amort)) * 2;
        }
        $score += max(0, 50 - ((float) $m['cout_annuel'] / 5000)) * 0.1;

        return round($score, 2);
    }

    /**
     * Identifie le meilleur scénario et produit un diagnostic textuel.
     *
     * @param array<string, array<string, mixed>> $scenarios  clé = nom (A, B, C…)
     * @return array{meilleur: string|null, diagnostic: string, classement: list<string>}
     */
    public static function diagnostiquer(array $scenarios): array
    {
        if ($scenarios === []) {
            return [
                'meilleur'    => null,
                'diagnostic'  => 'Aucun scénario disponible pour le diagnostic.',
                'classement'  => [],
            ];
        }

        uasort(
            $scenarios,
            static fn(array $a, array $b): int => ((float) $b['score'] <=> (float) $a['score'])
        );

        $classement = array_keys($scenarios);
        $meilleurCle = $classement[0];
        $best = $scenarios[$meilleurCle];
        $base = $scenarios[$classement[count($classement) - 1]] ?? $best;

        $reductionSteg = 0.0;
        if ((float) $base['energie_steg'] > 0) {
            $reductionSteg = round(
                (1 - ((float) $best['energie_steg'] / (float) $base['energie_steg'])) * 100,
                1
            );
        }

        $co2Tonnes = round((float) $best['co2_evite'] / 1000, 2);
        $amortTxt = (float) $best['temps_amortissement'] > 0
            ? sprintf('%.1f ans', $best['temps_amortissement'])
            : 'non applicable';

        $diagnostic = sprintf(
            'Le scénario %s est recommandé car il permet de réduire la consommation provenant du réseau STEG de %.1f %%, '
            . 'de diminuer les émissions de CO₂ de %.2f tonnes par an et d\'obtenir un retour sur investissement en %s '
            . '(couverture solaire %.1f %%, score décisionnel %.1f).',
            $meilleurCle,
            max(0, $reductionSteg),
            $co2Tonnes,
            $amortTxt,
            (float) $best['couverture_solaire'],
            (float) $best['score']
        );

        return [
            'meilleur'   => $meilleurCle,
            'diagnostic' => $diagnostic,
            'classement' => $classement,
        ];
    }
}
