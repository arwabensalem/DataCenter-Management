<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Agrégation des indicateurs du tableau de bord.
 */
final class DashboardAggregator
{
    /**
     * Calcule les KPI et séries graphiques pour une liste de Data Centers.
     *
     * @param list<array<string, mixed>> $dataCenters
     * @param callable(int): list<array<string, mixed>> $equipementsFn
     * @param callable(int): (array<string, mixed>|null) $installationFn
     * @return array{
     *   kpis: array<string, float|int>,
     *   charts: array<string, mixed>
     * }
     */
    public static function build(array $dataCenters, callable $equipementsFn, callable $installationFn): array
    {
        $app = require dirname(__DIR__) . '/config/app.php';
        $co2Factor = (float) $app['co2_kg_per_kwh'];

        $nbDc = count($dataCenters);
        $consoAn = 0.0;
        $productionAn = 0.0;
        $energiePv = 0.0;
        $energieSteg = 0.0;
        $coutInstallation = 0.0;
        $roiSum = 0.0;
        $roiCount = 0;
        $amortSum = 0.0;
        $amortCount = 0;
        $parCategorie = [];
        $labelsDc = [];
        $seriesConso = [];
        $seriesProd = [];
        $nbEquipements = 0;

        foreach ($dataCenters as $dc) {
            $dcId = (int) $dc['id'];
            $equipements = $equipementsFn($dcId);
            $totaux = EnergyCalculator::totaux($equipements);
            $dcConso = (float) $totaux['annuelle'];
            $consoAn += $dcConso;
            $nbEquipements += count($equipements);

            foreach ($totaux['par_categorie'] as $cat => $val) {
                $parCategorie[$cat] = ($parCategorie[$cat] ?? 0.0) + (float) $val;
            }

            $ipv = $installationFn($dcId);
            $dcProd = 0.0;
            $dcPv = 0.0;
            $dcSteg = $dcConso;

            if ($ipv !== null) {
                $dcProd = (float) $ipv['production_annuelle_kwh'];
                $dcPv = (float) $ipv['energie_pv_kwh'];
                $dcSteg = (float) $ipv['energie_steg_kwh'];
                $productionAn += $dcProd;
                $energiePv += $dcPv;
                $energieSteg += $dcSteg;
                $coutInstallation += (float) $ipv['cout_installation'];
                $roiSum += (float) $ipv['roi_pourcentage'];
                $roiCount++;
                if ((float) $ipv['temps_amortissement'] > 0) {
                    $amortSum += (float) $ipv['temps_amortissement'];
                    $amortCount++;
                }
            } else {
                $energieSteg += $dcConso;
            }

            $labelsDc[] = (string) $dc['nom'];
            $seriesConso[] = round($dcConso, 2);
            $seriesProd[] = round($dcProd, 2);
        }

        $couverture = $consoAn > 0 ? round(min(100, ($energiePv / $consoAn) * 100), 2) : 0.0;
        $prixMoyen = 0.0;
        if ($nbDc > 0) {
            $prixSum = 0.0;
            foreach ($dataCenters as $dc) {
                $prixSum += (float) $dc['prix_kwh_steg'];
            }
            $prixMoyen = $prixSum / $nbDc;
        }

        $coutAnnuelSteg = EnergyCalculator::coutAnnuel($energieSteg, $prixMoyen > 0 ? $prixMoyen : 0.25);
        $economiePv = EnergyCalculator::coutAnnuel($energiePv, $prixMoyen > 0 ? $prixMoyen : 0.25);
        $co2Evite = round($energiePv * $co2Factor, 2);
        $roiMoyen = $roiCount > 0 ? round($roiSum / $roiCount, 2) : 0.0;
        $amortMoyen = $amortCount > 0 ? round($amortSum / $amortCount, 2) : 0.0;

        // Consommation mensuelle estimée (répartition égale + légère variation saisonnière)
        $consoMensuelle = [];
        $prodMensuelle = [];
        $mois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        // Facteurs saisonniers indicatifs (ensoleillement Tunisie)
        $facteursSoleil = [0.70, 0.75, 0.90, 1.05, 1.15, 1.20, 1.25, 1.20, 1.05, 0.90, 0.75, 0.70];
        $baseConsoMois = $consoAn / 12;
        $sumFacteurs = array_sum($facteursSoleil);

        for ($i = 0; $i < 12; $i++) {
            $consoMensuelle[] = round($baseConsoMois, 2);
            $prodMensuelle[] = $productionAn > 0
                ? round(($productionAn * $facteursSoleil[$i]) / $sumFacteurs, 2)
                : 0.0;
        }

        // Répartition par catégorie (labels FR)
        $catLabels = [];
        $catValues = [];
        foreach ($parCategorie as $code => $val) {
            if ($val <= 0) {
                continue;
            }
            $catLabels[] = EnergyCalculator::categoryLabel((string) $code);
            $catValues[] = round((float) $val, 2);
        }

        $partSolaire = $consoAn > 0 ? round(($energiePv / $consoAn) * 100, 2) : 0.0;
        $partSteg = round(max(0, 100 - $partSolaire), 2);

        return [
            'kpis' => [
                'nb_data_centers'       => $nbDc,
                'nb_equipements'        => $nbEquipements,
                'consommation_totale'   => round($consoAn, 2),
                'production_pv'         => round($productionAn, 2),
                'couverture_pv'         => $couverture,
                'energie_steg'          => round($energieSteg, 2),
                'energie_pv'            => round($energiePv, 2),
                'cout_annuel'           => $coutAnnuelSteg,
                'economie_pv'           => $economiePv,
                'cout_installation'     => round($coutInstallation, 2),
                'roi'                   => $roiMoyen,
                'temps_amortissement'   => $amortMoyen,
                'co2_evite'             => $co2Evite,
            ],
            'charts' => [
                'mois' => $mois,
                'conso_mensuelle' => $consoMensuelle,
                'prod_mensuelle' => $prodMensuelle,
                'comparaison' => [
                    'labels' => $labelsDc,
                    'consommation' => $seriesConso,
                    'production' => $seriesProd,
                ],
                'repartition' => [
                    'labels' => $catLabels,
                    'values' => $catValues,
                ],
                'mix_energie' => [
                    'labels' => ['Solaire', 'STEG'],
                    'values' => [$partSolaire, $partSteg],
                ],
            ],
        ];
    }
}
