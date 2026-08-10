<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Alertes intelligentes pour le tableau de bord.
 */
final class AlertEngine
{
    /**
     * @param array<string, mixed>       $dataCenter
     * @param list<array<string, mixed>> $equipements
     * @param array<string, mixed>|null  $installationPv
     * @param array<string, mixed>|null  $pue
     * @return list<array{code: string, niveau: string, titre: string, message: string, icone: string}>
     */
    public static function generate(
        array $dataCenter,
        array $equipements,
        ?array $installationPv,
        ?array $pue = null
    ): array {
        $alerts = [];
        $enriched = array_map(
            static fn(array $e): array => EnergyCalculator::enrich($e),
            $equipements
        );
        $totaux = EnergyCalculator::totaux($enriched);
        $consoAn = (float) $totaux['annuelle'];

        // Consommation anormalement élevée (> 500 MWh/an pour un seul DC — seuil DSS)
        if ($consoAn > 500000) {
            $alerts[] = self::alert(
                'conso_elevee',
                'warning',
                'Consommation anormalement élevée',
                sprintf('La consommation annuelle atteint %.0f kWh. Une analyse d\'efficacité est recommandée.', $consoAn),
                'fa-bolt'
            );
        }

        // Serveurs très consommateurs
        foreach ($enriched as $eq) {
            if ($eq['categorie'] !== 'serveur' || $consoAn <= 0) {
                continue;
            }
            $part = ((float) $eq['conso_annuelle'] / $consoAn) * 100;
            if ($part >= 20) {
                $alerts[] = self::alert(
                    'serveur_gourmand',
                    'warning',
                    'Serveur très énergivore',
                    sprintf('« %s » représente %.1f %% de la consommation annuelle.', $eq['nom'], $part),
                    'fa-server'
                );
            }
        }

        // Couverture PV
        if ($installationPv === null) {
            $alerts[] = self::alert(
                'pas_pv',
                'danger',
                'Pas de dimensionnement PV',
                'Aucune installation photovoltaïque n\'est définie pour ce Data Center.',
                'fa-solar-panel'
            );
        } else {
            $couverture = (float) $installationPv['taux_couverture'];
            if ($couverture < 30) {
                $alerts[] = self::alert(
                    'couverture_faible',
                    'danger',
                    'Couverture photovoltaïque insuffisante',
                    sprintf('Couverture actuelle : %.1f %%. Objectif recommandé : ≥ 50 %%.', $couverture),
                    'fa-sun'
                );
            }

            $dependance = $consoAn > 0
                ? ((float) $installationPv['energie_steg_kwh'] / $consoAn) * 100
                : 0;
            if ($dependance >= 70) {
                $alerts[] = self::alert(
                    'dependance_steg',
                    'warning',
                    'Dépendance importante au réseau STEG',
                    sprintf('%.1f %% de l\'énergie provient encore du réseau.', $dependance),
                    'fa-plug'
                );
            }

            $amort = (float) $installationPv['temps_amortissement'];
            if ($amort > 12) {
                $alerts[] = self::alert(
                    'roi_long',
                    'warning',
                    'ROI / amortissement long',
                    sprintf('Temps d\'amortissement estimé : %.1f ans (seuil d\'alerte : 12 ans).', $amort),
                    'fa-hourglass-half'
                );
            }

            $surfaceDispo = (float) $dataCenter['surface_disponible_pv'];
            $surfaceNec = (float) $installationPv['surface_necessaire'];
            if ($couverture < 100 && $surfaceNec >= $surfaceDispo * 0.95) {
                $alerts[] = self::alert(
                    'surface_insuffisante',
                    'danger',
                    'Surface insuffisante pour les panneaux',
                    sprintf('Surface disponible %.0f m² saturée — couverture limitée à %.1f %%.', $surfaceDispo, $couverture),
                    'fa-ruler-combined'
                );
            }
        }

        // PUE faible
        if ($pue !== null && ($pue['pue'] ?? null) !== null && (float) $pue['pue'] > 2.0) {
            $alerts[] = self::alert(
                'pue_faible',
                'danger',
                'PUE faible (efficacité réduite)',
                sprintf('PUE = %.2f — l\'infrastructure consomme trop par rapport à l\'IT.', $pue['pue']),
                'fa-gauge'
            );
        }

        return $alerts;
    }

    /**
     * @return array{code: string, niveau: string, titre: string, message: string, icone: string}
     */
    private static function alert(
        string $code,
        string $niveau,
        string $titre,
        string $message,
        string $icone
    ): array {
        return compact('code', 'niveau', 'titre', 'message', 'icone');
    }
}
