<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Construit un contexte métier compact pour le service RAG / LLM.
 * N'inclut jamais de mots de passe ni de données personnelles inutiles.
 */
final class AiContextBuilder
{
    /**
     * @param array<string, mixed>       $dataCenter
     * @param list<array<string, mixed>> $equipements
     * @param array<string, mixed>|null  $installationPv
     * @param list<array<string, mixed>> $recommandations
     * @param array<string, mixed>|null  $latestSimulation
     * @return array<string, mixed>
     */
    public static function build(
        array $dataCenter,
        array $equipements,
        ?array $installationPv,
        array $recommandations = [],
        ?array $latestSimulation = null
    ): array {
        $enriched = array_map(
            static fn(array $eq): array => EnergyCalculator::enrich($eq),
            $equipements
        );
        $totaux = EnergyCalculator::totaux($enriched);
        $pue = PueCalculator::calculer($enriched);
        $consoAn = (float) $totaux['annuelle'];
        $cooling = (float) ($totaux['par_categorie']['climatisation'] ?? 0);
        $prixKwh = (float) ($dataCenter['prix_kwh_steg'] ?? 0.25);

        $energiePv = $installationPv !== null
            ? (float) ($installationPv['energie_pv_kwh'] ?? $installationPv['production_annuelle_kwh'] ?? 0)
            : 0.0;
        $energieSteg = $installationPv !== null
            ? (float) ($installationPv['energie_steg_kwh'] ?? max(0, $consoAn - $energiePv))
            : $consoAn;

        $co2 = ImpactEnvironnemental::calculer($consoAn, $energieSteg, $energiePv);

        // Top équipements (max 5)
        usort(
            $enriched,
            static fn(array $a, array $b): int =>
                ((float) $b['conso_annuelle'] <=> (float) $a['conso_annuelle'])
        );
        $top = [];
        foreach (array_slice($enriched, 0, 5) as $eq) {
            $top[] = [
                'name'            => (string) $eq['nom'],
                'category'        => (string) $eq['categorie'],
                'annual_kwh'      => round((float) $eq['conso_annuelle'], 2),
                'power_w'         => (float) $eq['puissance_watts'],
                'utilization_pct' => (float) $eq['taux_utilisation'],
            ];
        }

        $alerts = AlertEngine::generate($dataCenter, $equipements, $installationPv, $pue);
        $ruleRecos = RecommendationEngine::generate($dataCenter, $equipements, $installationPv);

        $problems = [];
        foreach ($alerts as $a) {
            $problems[] = [
                'code'    => $a['code'],
                'level'   => $a['niveau'],
                'title'   => $a['titre'],
                'message' => $a['message'],
            ];
        }

        $ruleRecommendations = [];
        foreach ($ruleRecos as $r) {
            $ruleRecommendations[] = [
                'title'    => self::titleFromType((string) $r['type']),
                'category' => self::categoryFromType((string) $r['type']),
                'priority' => (string) $r['priorite'],
                'reason'   => (string) $r['message'],
                'action'   => (string) $r['message'],
                'type'     => (string) $r['type'],
            ];
        }

        $pvBlock = null;
        if ($installationPv !== null) {
            $pvBlock = [
                'production_kwh'   => round((float) ($installationPv['production_annuelle_kwh'] ?? 0), 2),
                'coverage_pct'     => round((float) ($installationPv['taux_couverture'] ?? 0), 2),
                'panels'           => (int) ($installationPv['nombre_panneaux'] ?? 0),
                'power_kwc'        => round((float) ($installationPv['puissance_necessaire_kwc'] ?? 0), 2),
                'roi_pct'          => isset($installationPv['roi_pourcentage'])
                    ? round((float) $installationPv['roi_pourcentage'], 2) : null,
                'payback_years'    => isset($installationPv['temps_amortissement'])
                    ? round((float) $installationPv['temps_amortissement'], 2) : null,
            ];
        }

        $simBlock = null;
        if ($latestSimulation !== null) {
            $avant = $latestSimulation['parametres_avant'] ?? [];
            $apres = $latestSimulation['parametres_apres'] ?? [];
            $simBlock = [
                'id'              => (int) ($latestSimulation['id'] ?? 0),
                'nom'             => (string) ($latestSimulation['nom'] ?? ''),
                'energie_economisee_kwh' => isset($latestSimulation['energie_economisee'])
                    ? round((float) $latestSimulation['energie_economisee'], 2) : null,
                'pourcentage_reduction'  => isset($latestSimulation['pourcentage_reduction'])
                    ? round((float) $latestSimulation['pourcentage_reduction'], 2) : null,
                'co2_evite_kg'    => isset($latestSimulation['co2_evite'])
                    ? round((float) $latestSimulation['co2_evite'], 2) : null,
                'conso_avant_kwh' => isset($avant['conso_annuelle_kwh'])
                    ? round((float) $avant['conso_annuelle_kwh'], 2) : null,
                'conso_apres_kwh' => isset($apres['conso_annuelle_kwh'])
                    ? round((float) $apres['conso_annuelle_kwh'], 2) : null,
            ];
        }

        $energyByCat = [];
        foreach ($totaux['par_categorie'] as $cat => $kwh) {
            $energyByCat[$cat] = round((float) $kwh, 2);
        }

        return [
            'data_center' => [
                'id'                  => (int) $dataCenter['id'],
                'name'                => (string) $dataCenter['nom'],
                'location'            => (string) ($dataCenter['localisation'] ?? ''),
                'gouvernorat'         => (string) ($dataCenter['gouvernorat_nom'] ?? ''),
                'total_energy_kwh'    => round($consoAn, 2),
                'it_energy_kwh'       => round((float) $pue['energie_it_kwh'], 2),
                'cooling_energy_kwh'  => round($cooling, 2),
                'infra_energy_kwh'    => round((float) $pue['energie_infra_kwh'], 2),
                'cooling_share_pct'   => $consoAn > 0 ? round(($cooling / $consoAn) * 100, 1) : 0.0,
                'pue'                 => $pue['pue'],
                'pue_label'           => $pue['label'],
                'annual_cost_tnd'     => EnergyCalculator::coutAnnuel($energieSteg, $prixKwh),
                'prix_kwh_steg'       => $prixKwh,
                'surface_pv_m2'       => isset($dataCenter['surface_disponible_pv'])
                    ? (float) $dataCenter['surface_disponible_pv'] : null,
                'pv_coverage_pct'     => $pvBlock['coverage_pct'] ?? 0.0,
                'pv_production_kwh'   => $pvBlock['production_kwh'] ?? 0.0,
            ],
            'pv'                    => $pvBlock,
            'co2'                   => [
                'factor_kg_per_kwh' => $co2['facteur_kg_kwh'],
                'before_kg'         => $co2['co2_avant_kg'],
                'after_kg'          => $co2['co2_apres_kg'],
                'avoided_kg'        => $co2['co2_evite_total_kg'],
                'reduction_pct'     => $co2['pourcentage_reduction'],
            ],
            'equipments_top'        => $top,
            'energy_by_category'    => $energyByCat,
            'detected_problems'     => $problems,
            'alerts'                => $problems,
            'rule_recommendations'  => $ruleRecommendations,
            'latest_simulation'     => $simBlock,
            // Recos persistées (messages déjà générés) — max 8
            'stored_recommendations'=> array_slice(array_map(
                static fn(array $r): array => [
                    'type'     => (string) ($r['type'] ?? ''),
                    'priority' => (string) ($r['priorite'] ?? ''),
                    'message'  => (string) ($r['message'] ?? ''),
                ],
                $recommandations
            ), 0, 8),
        ];
    }

    private static function titleFromType(string $type): string
    {
        return match ($type) {
            'serveur_sous_utilise'   => 'Serveurs sous-utilisés',
            'equipement_energivore'  => 'Équipement énergivore',
            'climatisation_elevee'   => 'Refroidissement élevé',
            'couverture_pv_faible',
            'couverture_pv_insuffisante' => 'Couverture photovoltaïque',
            'surface_pv_insuffisante'=> 'Surface PV insuffisante',
            'remplacement_serveurs'  => 'Remplacement de serveurs',
            'puissance_pv'           => 'Puissance PV',
            'dependance_steg'        => 'Dépendance au réseau STEG',
            'aucun_equipement'       => 'Inventaire incomplet',
            default                  => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    private static function categoryFromType(string $type): string
    {
        return match (true) {
            str_contains($type, 'clim') || str_contains($type, 'cool') => 'cooling',
            str_contains($type, 'pv') || str_contains($type, 'pv') || str_contains($type, 'steg')
                || str_contains($type, 'surface') || str_contains($type, 'puissance') => 'photovoltaic',
            str_contains($type, 'serveur') || str_contains($type, 'energivore') => 'it_equipment',
            default => 'energy',
        };
    }
}
