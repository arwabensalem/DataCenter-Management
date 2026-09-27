<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;

/**
 * Bloc AI Insights dashboard.
 *
 * @var array<string, mixed>|null $aiInsights
 */
if (empty($aiInsights)) {
    return;
}

$fmt = static fn(float $v, int $d = 0): string => number_format($v, $d, ',', ' ');
$dcId = (int) ($aiInsights['data_center_id'] ?? 0);
?>
<div class="ai-insights-card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
        <h3 class="mb-0"><i class="fa-solid fa-robot me-1"></i> AI Insights</h3>
        <?php if (!empty($aiInsights['fallback'])): ?>
            <span class="badge bg-soft-blue text-dark">Analyse déterministe</span>
        <?php else: ?>
            <span class="badge bg-success">Enrichi RAG</span>
        <?php endif; ?>
    </div>
    <?php if (!empty($aiInsights['data_center_name'])): ?>
        <p class="small text-muted mb-2">
            Data Center : <strong><?= Security::e((string) $aiInsights['data_center_name']) ?></strong>
        </p>
    <?php endif; ?>

    <div class="ai-metric-row">
        <div class="ai-metric">
            <div class="label">⚡ Consommation</div>
            <div class="value"><?= Security::e($fmt((float) ($aiInsights['total_energy_kwh'] ?? 0))) ?>
                <small class="fw-normal">kWh</small></div>
        </div>
        <div class="ai-metric">
            <div class="label">📊 PUE</div>
            <div class="value">
                <?= isset($aiInsights['pue']) && $aiInsights['pue'] !== null
                    ? Security::e(number_format((float) $aiInsights['pue'], 2, ',', ''))
                    : '—' ?>
            </div>
        </div>
        <div class="ai-metric">
            <div class="label">❄️ Cooling</div>
            <div class="value"><?= Security::e(number_format((float) ($aiInsights['cooling_share_pct'] ?? 0), 1, ',', '')) ?> %</div>
        </div>
        <div class="ai-metric">
            <div class="label">☀️ PV Coverage</div>
            <div class="value"><?= Security::e(number_format((float) ($aiInsights['pv_coverage_pct'] ?? 0), 1, ',', '')) ?> %</div>
        </div>
        <div class="ai-metric">
            <div class="label">🌱 CO₂ évité</div>
            <div class="value"><?= Security::e($fmt((float) ($aiInsights['co2_avoided_kg'] ?? 0), 0)) ?>
                <small class="fw-normal">kg</small></div>
        </div>
    </div>

    <hr class="my-3">
    <p class="mb-2"><strong>🔎 Analyse</strong></p>
    <p class="mb-3"><?= Security::e((string) ($aiInsights['analysis'] ?? '')) ?></p>

    <?php if (!empty($aiInsights['recommendations'])): ?>
        <p class="mb-2"><strong>💡 Recommandations</strong></p>
        <ol class="mb-3">
            <?php foreach ($aiInsights['recommendations'] as $tip): ?>
                <li><?= Security::e((string) $tip) ?></li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <div class="d-flex flex-wrap gap-2">
        <?php if ($dcId > 0): ?>
            <a href="<?= Security::e(Url::to('ai/analyze/' . $dcId)) ?>" class="btn btn-brand btn-sm">
                Voir l'analyse complète
            </a>
            <a href="<?= Security::e(Url::to('ai?dc=' . $dcId)) ?>" class="btn btn-outline-secondary btn-sm">
                Ouvrir le chat AI
            </a>
        <?php else: ?>
            <a href="<?= Security::e(Url::to('ai')) ?>" class="btn btn-brand btn-sm">Ouvrir AI Advisor</a>
        <?php endif; ?>
    </div>
</div>
