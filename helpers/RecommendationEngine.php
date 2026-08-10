<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Moteur de recommandations par règles métiers (sans IA externe).
 */
final class RecommendationEngine
{
    /** Seuil : serveur sous-utilisé (%) */
    private const TAUX_SOUS_UTILISATION = 40.0;

    /** Seuil : équipement énergivore (part de la conso annuelle %) */
    private const PART_ENERGIVORE = 25.0;

    /** Seuil : part climatisation jugée élevée (%) */
    private const PART_CLIMATISATION = 30.0;

    /** Seuil : couverture PV insuffisante (%) */
    private const COUVERTURE_INSUFFISANTE = 50.0;

    /** Seuil : couverture PV très faible (%) */
    private const COUVERTURE_FAIBLE = 25.0;

    /**
     * Génère la liste des recommandations pour un Data Center.
     *
     * @param array<string, mixed>       $dataCenter
     * @param list<array<string, mixed>> $equipements
     * @param array<string, mixed>|null  $installationPv
     * @return list<array{type: string, priorite: string, message: string}>
     */
    public static function generate(array $dataCenter, array $equipements, ?array $installationPv): array
    {
        $recommendations = [];
        $enriched = array_map(
            static fn(array $eq): array => EnergyCalculator::enrich($eq),
            $equipements
        );
        $totaux = EnergyCalculator::totaux($enriched);
        $consoAn = (float) $totaux['annuelle'];

        if ($enriched === []) {
            $recommendations[] = [
                'type'     => 'aucun_equipement',
                'priorite' => 'haute',
                'message'  => 'Aucun équipement n\'est enregistré. Ajoutez l\'inventaire énergétique pour permettre l\'analyse et le dimensionnement photovoltaïque.',
            ];

            return $recommendations;
        }

        self::ruleServeursSousUtilises($enriched, $recommendations);
        self::ruleEquipementsEnergivores($enriched, $consoAn, $recommendations);
        self::ruleClimatisation($totaux['par_categorie'], $consoAn, $recommendations);
        self::ruleSurfacePvInsuffisante($dataCenter, $installationPv, $consoAn, $recommendations);
        self::ruleCouverturePv($installationPv, $consoAn, $recommendations);
        self::ruleRemplacementServeurs($enriched, $recommendations);
        self::rulePuissancePv($installationPv, $recommendations);
        self::ruleDependanceSteg($installationPv, $consoAn, $recommendations);

        // Tri : haute → moyenne → basse
        $ordre = ['haute' => 0, 'moyenne' => 1, 'basse' => 2];
        usort(
            $recommendations,
            static fn(array $a, array $b): int => ($ordre[$a['priorite']] ?? 9) <=> ($ordre[$b['priorite']] ?? 9)
        );

        return $recommendations;
    }

    /**
     * @param list<array<string, mixed>> $equipements
     * @param list<array{type: string, priorite: string, message: string}> $out
     */
    private static function ruleServeursSousUtilises(array $equipements, array &$out): void
    {
        foreach ($equipements as $eq) {
            if ($eq['categorie'] !== 'serveur') {
                continue;
            }
            $taux = (float) $eq['taux_utilisation'];
            if ($taux < self::TAUX_SOUS_UTILISATION) {
                $out[] = [
                    'type'     => 'serveur_sous_utilise',
                    'priorite' => 'moyenne',
                    'message'  => sprintf(
                        'Le serveur « %s » est sous-utilisé (%.0f %% ). Envisagez la consolidation ou la virtualisation pour réduire la consommation.',
                        $eq['nom'],
                        $taux
                    ),
                ];
            }
        }
    }

    /**
     * @param list<array<string, mixed>> $equipements
     * @param list<array{type: string, priorite: string, message: string}> $out
     */
    private static function ruleEquipementsEnergivores(array $equipements, float $consoAn, array &$out): void
    {
        if ($consoAn <= 0) {
            return;
        }

        foreach ($equipements as $eq) {
            $part = ((float) $eq['conso_annuelle'] / $consoAn) * 100;
            if ($part >= self::PART_ENERGIVORE) {
                $out[] = [
                    'type'     => 'equipement_energivore',
                    'priorite' => 'haute',
                    'message'  => sprintf(
                        'L\'équipement « %s » (%s) représente %.1f %% de la consommation annuelle (%.0f kWh). Priorisez son optimisation ou son remplacement.',
                        $eq['nom'],
                        EnergyCalculator::categoryLabel((string) $eq['categorie']),
                        $part,
                        (float) $eq['conso_annuelle']
                    ),
                ];
            }
        }
    }

    /**
     * @param array<string, float> $parCategorie
     * @param list<array{type: string, priorite: string, message: string}> $out
     */
    private static function ruleClimatisation(array $parCategorie, float $consoAn, array &$out): void
    {
        if ($consoAn <= 0 || !isset($parCategorie['climatisation'])) {
            return;
        }

        $part = ($parCategorie['climatisation'] / $consoAn) * 100;
        if ($part >= self::PART_CLIMATISATION) {
            $out[] = [
                'type'     => 'climatisation_elevee',
                'priorite' => 'haute',
                'message'  => sprintf(
                    'La climatisation représente %.1f %% de la consommation annuelle. Vérifiez le PUE, la température de consigne et l\'étanchéité des allées froides/chaudes.',
                    $part
                ),
            ];
        }
    }

    /**
     * @param array<string, mixed>      $dataCenter
     * @param array<string, mixed>|null $installationPv
     * @param list<array{type: string, priorite: string, message: string}> $out
     */
    private static function ruleSurfacePvInsuffisante(
        array $dataCenter,
        ?array $installationPv,
        float $consoAn,
        array &$out
    ): void {
        if ($installationPv === null || $consoAn <= 0) {
            return;
        }

        $surfaceDispo = (float) $dataCenter['surface_disponible_pv'];
        $surfaceNec = (float) $installationPv['surface_necessaire'];
        $couverture = (float) $installationPv['taux_couverture'];

        if ($couverture < 100 && $surfaceNec >= $surfaceDispo * 0.95) {
            $out[] = [
                'type'     => 'surface_pv_insuffisante',
                'priorite' => 'haute',
                'message'  => sprintf(
                    'La surface disponible (%.0f m²) est insuffisante pour couvrir toute la consommation (couverture actuelle %.1f %% ). Envisagez d\'étendre la surface PV ou de réduire la charge électrique.',
                    $surfaceDispo,
                    $couverture
                ),
            ];
        }
    }

    /**
     * @param array<string, mixed>|null $installationPv
     * @param list<array{type: string, priorite: string, message: string}> $out
     */
    private static function ruleCouverturePv(?array $installationPv, float $consoAn, array &$out): void
    {
        if ($consoAn <= 0) {
            return;
        }

        if ($installationPv === null) {
            $out[] = [
                'type'     => 'pas_de_pv',
                'priorite' => 'haute',
                'message'  => 'Aucune installation photovoltaïque n\'est dimensionnée. Lancez le module Photovoltaïque pour estimer la couverture solaire et le ROI.',
            ];
            return;
        }

        $couverture = (float) $installationPv['taux_couverture'];

        if ($couverture < self::COUVERTURE_FAIBLE) {
            $out[] = [
                'type'     => 'couverture_pv_faible',
                'priorite' => 'haute',
                'message'  => sprintf(
                    'Le taux de couverture photovoltaïque est très insuffisant (%.1f %% ). Une installation plus puissante ou une réduction de consommation est recommandée.',
                    $couverture
                ),
            ];
        } elseif ($couverture < self::COUVERTURE_INSUFFISANTE) {
            $out[] = [
                'type'     => 'couverture_pv_insuffisante',
                'priorite' => 'moyenne',
                'message'  => sprintf(
                    'Le taux de couverture photovoltaïque (%.1f %% ) reste insuffisant. Augmentez la puissance installée ou optimisez les équipements énergivores.',
                    $couverture
                ),
            ];
        }
    }

    /**
     * @param list<array<string, mixed>> $equipements
     * @param list<array{type: string, priorite: string, message: string}> $out
     */
    private static function ruleRemplacementServeurs(array $equipements, array &$out): void
    {
        foreach ($equipements as $eq) {
            if ($eq['categorie'] !== 'serveur') {
                continue;
            }
            $puissance = (float) $eq['puissance_watts'];
            $taux = (float) $eq['taux_utilisation'];

            // Serveurs anciens / gourmands : > 450 W avec taux élevé
            if ($puissance >= 450 && $taux >= 60) {
                $out[] = [
                    'type'     => 'remplacement_serveur',
                    'priorite' => 'moyenne',
                    'message'  => sprintf(
                        'Le remplacement de « %s » (%.0f W, taux %.0f %% ) par un modèle plus économe permettrait de réduire significativement la consommation. Simulez ce scénario dans le module Simulations.',
                        $eq['nom'],
                        $puissance,
                        $taux
                    ),
                ];
            }
        }
    }

    /**
     * @param array<string, mixed>|null $installationPv
     * @param list<array{type: string, priorite: string, message: string}> $out
     */
    private static function rulePuissancePv(?array $installationPv, array &$out): void
    {
        if ($installationPv === null) {
            return;
        }

        $couverture = (float) $installationPv['taux_couverture'];
        $nb = (int) $installationPv['nombre_panneaux'];

        if ($couverture < 80 && $nb > 0) {
            $out[] = [
                'type'     => 'pv_plus_puissant',
                'priorite' => 'moyenne',
                'message'  => sprintf(
                    'Une installation photovoltaïque plus puissante est recommandée. Actuellement %d panneaux couvrent %.1f %% des besoins. Explorez des panneaux à plus fort rendement ou une extension de surface.',
                    $nb,
                    $couverture
                ),
            ];
        }
    }

    /**
     * @param array<string, mixed>|null $installationPv
     * @param list<array{type: string, priorite: string, message: string}> $out
     */
    private static function ruleDependanceSteg(?array $installationPv, float $consoAn, array &$out): void
    {
        if ($installationPv === null || $consoAn <= 0) {
            return;
        }

        $energieSteg = (float) $installationPv['energie_steg_kwh'];
        $dependance = ($energieSteg / $consoAn) * 100;

        if ($dependance >= 70) {
            $out[] = [
                'type'     => 'dependance_steg',
                'priorite' => 'moyenne',
                'message'  => sprintf(
                    'La dépendance au réseau STEG reste élevée (%.1f %% de l\'énergie annuelle). Combinez efficacité énergétique et extension photovoltaïque pour la réduire.',
                    $dependance
                ),
            ];
        }
    }
}
