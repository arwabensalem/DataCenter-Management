<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Calcul du PUE (Power Usage Effectiveness).
 *
 * PUE = Énergie totale installation / Énergie IT
 * IT ≈ serveurs + stockage + réseau (switch, routeur, firewall)
 * Total ≈ IT + climatisation + UPS (et autres)
 */
final class PueCalculator
{
    private const IT_CATEGORIES = ['serveur', 'stockage', 'switch', 'routeur', 'firewall'];

    /**
     * @param list<array<string, mixed>> $equipements
     * @return array{
     *   pue: float|null,
     *   energie_it_kwh: float,
     *   energie_totale_kwh: float,
     *   energie_infra_kwh: float,
     *   niveau: string,
     *   label: string,
     *   couleur: string,
     *   explication: string
     * }
     */
    public static function calculer(array $equipements): array
    {
        $totaux = EnergyCalculator::totaux($equipements);
        $energieTotale = (float) $totaux['annuelle'];
        $energieIt = 0.0;

        foreach ($equipements as $eq) {
            $enriched = isset($eq['conso_annuelle']) ? $eq : EnergyCalculator::enrich($eq);
            if (in_array($eq['categorie'], self::IT_CATEGORIES, true)) {
                $energieIt += (float) $enriched['conso_annuelle'];
            }
        }

        $energieInfra = max(0, $energieTotale - $energieIt);

        if ($energieIt <= 0 || $energieTotale <= 0) {
            return [
                'pue'               => null,
                'energie_it_kwh'    => round($energieIt, 2),
                'energie_totale_kwh'=> round($energieTotale, 2),
                'energie_infra_kwh' => round($energieInfra, 2),
                'niveau'            => 'indisponible',
                'label'             => 'Données insuffisantes',
                'couleur'           => 'secondary',
                'explication'       => 'Le PUE nécessite une consommation IT (serveurs, stockage, réseau) pour être calculé.',
            ];
        }

        $pue = round($energieTotale / $energieIt, 2);
        [$niveau, $label, $couleur] = self::classifier($pue);

        return [
            'pue'                => $pue,
            'energie_it_kwh'     => round($energieIt, 2),
            'energie_totale_kwh' => round($energieTotale, 2),
            'energie_infra_kwh'  => round($energieInfra, 2),
            'niveau'             => $niveau,
            'label'              => $label,
            'couleur'            => $couleur,
            'explication'        => 'Le PUE (Power Usage Effectiveness) mesure l\'efficacité énergétique d\'un Data Center. '
                . 'Il est égal à l\'énergie totale consommée divisée par l\'énergie dédiée à l\'IT. '
                . 'Un PUE proche de 1,0 est idéal ; au-delà de 2,0 l\'infrastructure (climatisation, UPS…) consomme autant ou plus que l\'IT.',
        ];
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private static function classifier(float $pue): array
    {
        if ($pue <= 1.2) {
            return ['excellent', 'Excellent', 'success'];
        }
        if ($pue <= 1.5) {
            return ['bon', 'Bon', 'primary'];
        }
        if ($pue <= 2.0) {
            return ['moyen', 'Moyen', 'warning'];
        }

        return ['faible', 'Faible', 'danger'];
    }
}
