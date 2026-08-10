<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Moteur de simulation AVANT / APRÈS pour un Data Center.
 */
final class SimulationEngine
{
    /**
     * Construit l'état de référence (AVANT) à partir des données actuelles.
     *
     * @param list<array<string, mixed>> $equipements
     * @param array<string, mixed>|null  $installationPv
     * @param array<string, mixed>       $dataCenter
     * @return array<string, mixed>
     */
    public static function snapshotAvant(array $equipements, ?array $installationPv, array $dataCenter): array
    {
        $totaux = EnergyCalculator::totaux($equipements);
        $prixKwh = (float) $dataCenter['prix_kwh_steg'];
        $consoAn = (float) $totaux['annuelle'];

        $energiePv = $installationPv !== null ? (float) $installationPv['energie_pv_kwh'] : 0.0;
        $energieSteg = $installationPv !== null
            ? (float) $installationPv['energie_steg_kwh']
            : $consoAn;
        $production = $installationPv !== null ? (float) $installationPv['production_annuelle_kwh'] : 0.0;
        $couverture = $installationPv !== null ? (float) $installationPv['taux_couverture'] : 0.0;
        $nbPanneaux = $installationPv !== null ? (int) $installationPv['nombre_panneaux'] : 0;
        $heures = $installationPv !== null ? (float) $installationPv['heures_ensoleillement'] : 5.5;

        $dependanceSteg = $consoAn > 0 ? round(($energieSteg / $consoAn) * 100, 2) : 0.0;

        return [
            'conso_annuelle_kwh'      => round($consoAn, 2),
            'conso_journaliere_kwh'   => round((float) $totaux['journaliere'], 4),
            'cout_annuel_steg'        => EnergyCalculator::coutAnnuel($energieSteg, $prixKwh),
            'energie_pv_kwh'          => round($energiePv, 2),
            'energie_steg_kwh'        => round($energieSteg, 2),
            'production_pv_kwh'       => round($production, 2),
            'taux_couverture'         => $couverture,
            'dependance_steg'         => $dependanceSteg,
            'nombre_panneaux'         => $nbPanneaux,
            'heures_ensoleillement'   => $heures,
            'prix_kwh_steg'           => $prixKwh,
            'nb_equipements'          => count($equipements),
            'equipements'             => array_map(
                static fn(array $eq): array => [
                    'id' => (int) $eq['id'],
                    'nom' => $eq['nom'],
                    'categorie' => $eq['categorie'],
                    'quantite' => (int) $eq['quantite'],
                    'puissance_watts' => (float) $eq['puissance_watts'],
                    'taux_utilisation' => (float) $eq['taux_utilisation'],
                    'heures_fonctionnement' => (float) $eq['heures_fonctionnement'],
                    'conso_annuelle' => EnergyCalculator::enrich($eq)['conso_annuelle'],
                ],
                $equipements
            ),
            'type_panneau' => $installationPv !== null ? [
                'id' => (int) $installationPv['type_panneau_id'],
                'puissance_wc' => (float) ($installationPv['puissance_wc'] ?? 0),
                'rendement' => (float) ($installationPv['rendement'] ?? 0),
                'surface_m2' => (float) ($installationPv['surface_m2'] ?? 0),
                'prix_unitaire' => (float) ($installationPv['prix_unitaire'] ?? 0),
            ] : null,
            'surface_disponible_pv' => (float) $dataCenter['surface_disponible_pv'],
        ];
    }

    /**
     * Applique les modifications de scénario pour obtenir l'état APRÈS.
     *
     * @param array<string, mixed> $avant
     * @param array<string, mixed> $mods
     * @return array<string, mixed>
     */
    public static function appliquerModifications(array $avant, array $mods): array
    {
        $equipements = $avant['equipements'];

        // 1) Supprimer / réduire quantité
        if (!empty($mods['supprimer_equipement_id']) && (int) $mods['supprimer_quantite'] > 0) {
            $targetId = (int) $mods['supprimer_equipement_id'];
            $qtyRemove = (int) $mods['supprimer_quantite'];
            foreach ($equipements as $i => $eq) {
                if ((int) $eq['id'] === $targetId) {
                    $newQty = max(0, (int) $eq['quantite'] - $qtyRemove);
                    if ($newQty === 0) {
                        unset($equipements[$i]);
                    } else {
                        $equipements[$i]['quantite'] = $newQty;
                    }
                    break;
                }
            }
            $equipements = array_values($equipements);
        }

        // 2) Remplacer un équipement (puissance / taux plus économes)
        if (!empty($mods['remplacer_equipement_id'])) {
            $targetId = (int) $mods['remplacer_equipement_id'];
            foreach ($equipements as $i => $eq) {
                if ((int) $eq['id'] === $targetId) {
                    if (isset($mods['nouvelle_puissance']) && $mods['nouvelle_puissance'] !== null) {
                        $equipements[$i]['puissance_watts'] = (float) $mods['nouvelle_puissance'];
                    }
                    if (isset($mods['nouveau_taux']) && $mods['nouveau_taux'] !== null) {
                        $equipements[$i]['taux_utilisation'] = (float) $mods['nouveau_taux'];
                    }
                    break;
                }
            }
        }

        // 3) Ajouter des serveurs
        $ajoutQte = (int) ($mods['ajouter_serveurs_qte'] ?? 0);
        if ($ajoutQte > 0) {
            $equipements[] = [
                'id' => 0,
                'nom' => 'Serveurs simulés',
                'categorie' => 'serveur',
                'quantite' => $ajoutQte,
                'puissance_watts' => (float) ($mods['ajouter_serveurs_puissance'] ?? 400),
                'taux_utilisation' => (float) ($mods['ajouter_serveurs_taux'] ?? 70),
                'heures_fonctionnement' => (float) ($mods['ajouter_serveurs_heures'] ?? 24),
            ];
        }

        // Recalcul conso
        $consoAn = 0.0;
        $consoJour = 0.0;
        foreach ($equipements as $i => $eq) {
            $enriched = EnergyCalculator::enrich($eq);
            $equipements[$i]['conso_annuelle'] = $enriched['conso_annuelle'];
            $consoAn += (float) $enriched['conso_annuelle'];
            $consoJour += (float) $enriched['conso_journaliere'];
        }
        $consoAn = round($consoAn, 2);

        // 4) PV : panneaux et ensoleillement
        $nbPanneaux = (int) $avant['nombre_panneaux'];
        $heures = (float) $avant['heures_ensoleillement'];
        $type = $avant['type_panneau'];

        if (isset($mods['heures_ensoleillement']) && $mods['heures_ensoleillement'] !== null) {
            $heures = (float) $mods['heures_ensoleillement'];
        }

        if (isset($mods['ajouter_panneaux']) && (int) $mods['ajouter_panneaux'] > 0) {
            $nbPanneaux += (int) $mods['ajouter_panneaux'];
        }

        if (isset($mods['nouveau_nombre_panneaux']) && $mods['nouveau_nombre_panneaux'] !== null) {
            $nbPanneaux = max(0, (int) $mods['nouveau_nombre_panneaux']);
        }

        // Contrainte surface
        if ($type !== null && (float) $type['surface_m2'] > 0) {
            $max = (int) floor((float) $avant['surface_disponible_pv'] / (float) $type['surface_m2']);
            $nbPanneaux = min($nbPanneaux, $max);
        }

        $production = 0.0;
        $energiePv = 0.0;
        $energieSteg = $consoAn;
        $couverture = 0.0;

        if ($type !== null && $nbPanneaux > 0 && (float) $type['puissance_wc'] > 0) {
            $prodPanneau = PvCalculator::productionPanneauAnnuelle(
                (float) $type['puissance_wc'],
                $heures,
                (float) $type['rendement']
            );
            $production = round($nbPanneaux * $prodPanneau, 2);
            $energiePv = round(min($production, $consoAn), 2);
            $energieSteg = round(max(0, $consoAn - $energiePv), 2);
            $couverture = $consoAn > 0 ? round(min(100, ($production / $consoAn) * 100), 2) : 0.0;
        }

        $prixKwh = (float) $avant['prix_kwh_steg'];
        $dependanceSteg = $consoAn > 0 ? round(($energieSteg / $consoAn) * 100, 2) : 0.0;

        return [
            'conso_annuelle_kwh'    => $consoAn,
            'conso_journaliere_kwh' => round($consoJour, 4),
            'cout_annuel_steg'      => EnergyCalculator::coutAnnuel($energieSteg, $prixKwh),
            'energie_pv_kwh'        => $energiePv,
            'energie_steg_kwh'      => $energieSteg,
            'production_pv_kwh'     => $production,
            'taux_couverture'       => $couverture,
            'dependance_steg'       => $dependanceSteg,
            'nombre_panneaux'       => $nbPanneaux,
            'heures_ensoleillement' => $heures,
            'prix_kwh_steg'         => $prixKwh,
            'nb_equipements'        => count($equipements),
            'equipements'           => $equipements,
            'modifications'         => $mods,
            'ville'                 => $mods['ville'] ?? null,
        ];
    }

    /**
     * Compare AVANT et APRÈS.
     *
     * @param array<string, mixed> $avant
     * @param array<string, mixed> $apres
     * @return array<string, float>
     */
    public static function comparer(array $avant, array $apres): array
    {
        $app = require dirname(__DIR__) . '/config/app.php';
        $facteurCo2 = (float) $app['co2_kg_per_kwh'];

        $energieEconomisee = round(
            (float) $avant['conso_annuelle_kwh'] - (float) $apres['conso_annuelle_kwh'],
            2
        );

        // Aussi compter la réduction d'énergie STEG (effet PV)
        $stegAvant = (float) $avant['energie_steg_kwh'];
        $stegApres = (float) $apres['energie_steg_kwh'];
        $reductionStegKwh = round($stegAvant - $stegApres, 2);

        $pourcentageReduction = (float) $avant['conso_annuelle_kwh'] > 0
            ? round(($energieEconomisee / (float) $avant['conso_annuelle_kwh']) * 100, 2)
            : 0.0;

        $coutEconomise = round(
            (float) $avant['cout_annuel_steg'] - (float) $apres['cout_annuel_steg'],
            2
        );

        $reductionDependance = round(
            (float) $avant['dependance_steg'] - (float) $apres['dependance_steg'],
            2
        );

        // CO₂ évité = réduction conso réseau STEG × facteur
        $co2Evite = round(max(0, $reductionStegKwh) * $facteurCo2, 2);

        return [
            'energie_economisee'        => $energieEconomisee,
            'pourcentage_reduction'     => $pourcentageReduction,
            'cout_economise'            => $coutEconomise,
            'reduction_dependance_steg' => $reductionDependance,
            'reduction_steg_kwh'        => $reductionStegKwh,
            'co2_evite'                 => $co2Evite,
        ];
    }
}
