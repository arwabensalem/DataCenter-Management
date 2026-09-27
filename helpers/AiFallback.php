<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Fallback déterministe si le service RAG / LLM est indisponible.
 */
final class AiFallback
{
    /**
     * @param array<string, mixed> $businessContext  Sortie de AiContextBuilder::build()
     * @return array{
     *   answer: string,
     *   sources: list<array<string, mixed>>,
     *   metrics: array<string, mixed>,
     *   recommendations: list<array<string, mixed>>,
     *   fallback: bool
     * }
     */
    public static function analyze(array $businessContext, string $question = ''): array
    {
        $dc = $businessContext['data_center'] ?? [];
        $name = (string) ($dc['name'] ?? 'Data Center');
        $lines = [];

        $lines[] = "### AI Insights (mode déterministe)";
        $lines[] = "";
        $lines[] = "Analyse basée sur les **calculateurs métier** GreenDC (service IA indisponible ou non configuré).";
        $lines[] = "";
        $lines[] = "#### Résumé — {$name}";
        $lines[] = sprintf("- Consommation totale : **%s kWh/an**", self::fmt((float) ($dc['total_energy_kwh'] ?? 0)));
        $lines[] = sprintf("- Énergie IT : **%s kWh/an**", self::fmt((float) ($dc['it_energy_kwh'] ?? 0)));
        $lines[] = sprintf(
            "- Cooling : **%s kWh/an** (%.1f %%)",
            self::fmt((float) ($dc['cooling_energy_kwh'] ?? 0)),
            (float) ($dc['cooling_share_pct'] ?? 0)
        );
        $pue = $dc['pue'] ?? null;
        $lines[] = sprintf(
            "- PUE : **%s** (%s)",
            $pue !== null ? number_format((float) $pue, 2, ',', '') : 'n/d',
            (string) ($dc['pue_label'] ?? '')
        );
        $lines[] = sprintf("- Couverture PV : **%.1f %%**", (float) ($dc['pv_coverage_pct'] ?? 0));

        $co2 = $businessContext['co2'] ?? [];
        if ($co2 !== []) {
            $lines[] = sprintf(
                "- CO₂ évité (PV) : **%s kg** (facteur %s kg/kWh)",
                self::fmt((float) ($co2['avoided_kg'] ?? 0), 1),
                (string) ($co2['factor_kg_per_kwh'] ?? '')
            );
        }

        $lines[] = "";
        $lines[] = "#### Problèmes détectés";
        $problems = $businessContext['detected_problems'] ?? [];
        if ($problems === []) {
            $lines[] = "_Aucun seuil d'alerte franchi selon les règles actuelles._";
        } else {
            foreach ($problems as $p) {
                $lines[] = sprintf(
                    "- **%s** — %s",
                    (string) ($p['title'] ?? ''),
                    (string) ($p['message'] ?? '')
                );
            }
        }

        $lines[] = "";
        $lines[] = "#### Actions recommandées (règles métiers)";
        $recos = $businessContext['rule_recommendations'] ?? [];
        if ($recos === []) {
            $lines[] = "_Aucune recommandation prioritaire générée._";
        } else {
            foreach (array_slice($recos, 0, 5) as $i => $r) {
                $lines[] = sprintf(
                    "%d. **[%s]** %s — %s",
                    $i + 1,
                    strtoupper((string) ($r['priority'] ?? '')),
                    (string) ($r['title'] ?? ''),
                    (string) ($r['reason'] ?? '')
                );
            }
        }

        $lines[] = "";
        $lines[] = "#### Impact attendu";
        $lines[] = "Les économies chiffrées nécessitent une **simulation** via SimulationEngine "
            . "ou un dimensionnement PV. L'analyse ci-dessus est qualitative / basée sur des seuils.";

        if ($question !== '') {
            $lines[] = "";
            $lines[] = "#### Votre question";
            $lines[] = "> " . $question;
            $lines[] = "";
            $lines[] = "Relancez la question lorsque le service RAG est disponible pour une réponse documentée.";
        }

        return [
            'answer'          => implode("\n", $lines),
            'sources'         => [],
            'metrics'         => [
                'elapsed_ms'         => 0,
                'passages_retrieved' => 0,
                'llm_used'           => false,
                'fallback'           => true,
            ],
            'recommendations' => $recos,
            'fallback'        => true,
        ];
    }

    /**
     * Insights courts pour le dashboard.
     *
     * @param array<string, mixed> $businessContext
     * @return array<string, mixed>
     */
    public static function insightsCard(array $businessContext): array
    {
        $dc = $businessContext['data_center'] ?? [];
        $recos = $businessContext['rule_recommendations'] ?? [];
        $problems = $businessContext['detected_problems'] ?? [];

        $analysis = 'Analyse déterministe basée sur les calculateurs GreenDC.';
        if ($problems !== []) {
            $first = $problems[0];
            $analysis = 'Levier principal identifié : ' . (string) ($first['title'] ?? $first['message'] ?? 'optimisation énergétique') . '.';
        } elseif (($dc['pue'] ?? null) !== null && (float) $dc['pue'] > 1.8) {
            $analysis = 'Le PUE est élevé : priorisez le refroidissement et la charge IT utile.';
        } elseif ((float) ($dc['pv_coverage_pct'] ?? 0) < 50) {
            $analysis = 'La couverture photovoltaïque reste inférieure à l\'objectif indicatif de 50 %.';
        }

        $tips = [];
        foreach (array_slice($recos, 0, 3) as $r) {
            $tips[] = (string) ($r['title'] ?? $r['reason'] ?? '');
        }

        return [
            'data_center_name' => (string) ($dc['name'] ?? ''),
            'total_energy_kwh' => (float) ($dc['total_energy_kwh'] ?? 0),
            'pue'              => $dc['pue'] ?? null,
            'pue_label'        => (string) ($dc['pue_label'] ?? ''),
            'cooling_share_pct'=> (float) ($dc['cooling_share_pct'] ?? 0),
            'pv_coverage_pct'  => (float) ($dc['pv_coverage_pct'] ?? 0),
            'co2_avoided_kg'   => (float) (($businessContext['co2']['avoided_kg'] ?? 0)),
            'analysis'         => $analysis,
            'recommendations'  => $tips,
            'fallback'         => true,
            'llm_used'         => false,
        ];
    }

    private static function fmt(float $v, int $dec = 0): string
    {
        return number_format($v, $dec, ',', ' ');
    }
}
