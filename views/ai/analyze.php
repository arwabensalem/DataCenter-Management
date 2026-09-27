<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;

/** @var array<string, mixed> $dataCenter */
/** @var array<string, mixed> $context */
/** @var array<string, mixed> $result */
/** @var array<string, mixed> $insights */

$dcMetrics = $context['data_center'] ?? [];
$fmt = static fn(float $v, int $d = 0): string => number_format($v, $d, ',', ' ');
$fallback = !empty($result['fallback']);
$answer = (string) ($result['answer'] ?? '');
$sources = $result['sources'] ?? [];
?>
<div class="mb-3">
    <a href="<?= Security::e(Url::to('ai?dc=' . (int) $dataCenter['id'])) ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Retour au chat
    </a>
</div>

<div class="welcome-banner p-4 mb-4">
    <h2 class="h4 mb-1"><i class="fa-solid fa-magnifying-glass-chart me-2"></i>AI Energy Analysis</h2>
    <p class="mb-0 opacity-90"><?= Security::e((string) $dataCenter['nom']) ?>
        <?php if ($fallback): ?>
            <span class="badge bg-warning text-dark ms-2">Fallback déterministe</span>
        <?php else: ?>
            <span class="badge bg-success ms-2">RAG</span>
        <?php endif; ?>
    </p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-bolt"></i></div>
            <div>
                <div class="stat-label">Consommation</div>
                <div class="stat-value"><?= Security::e($fmt((float) ($dcMetrics['total_energy_kwh'] ?? 0))) ?>
                    <small class="fs-6">kWh</small></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-gauge"></i></div>
            <div>
                <div class="stat-label">PUE</div>
                <div class="stat-value">
                    <?= isset($dcMetrics['pue']) && $dcMetrics['pue'] !== null
                        ? Security::e(number_format((float) $dcMetrics['pue'], 2, ',', ''))
                        : '—' ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-snowflake"></i></div>
            <div>
                <div class="stat-label">Cooling</div>
                <div class="stat-value"><?= Security::e(number_format((float) ($dcMetrics['cooling_share_pct'] ?? 0), 1, ',', '')) ?> %</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-sun"></i></div>
            <div>
                <div class="stat-label">PV Coverage</div>
                <div class="stat-value"><?= Security::e(number_format((float) ($dcMetrics['pv_coverage_pct'] ?? 0), 1, ',', '')) ?> %</div>
            </div>
        </div>
    </div>
</div>

<div class="form-card mb-4">
    <h3 class="h6 text-uppercase text-muted mb-3">Analyse</h3>
    <div class="ai-answer-body">
        <?= nl2br(Security::e($answer)) ?>
    </div>
</div>

<?php if (!empty($sources)): ?>
<div class="form-card mb-4">
    <h3 class="h6 text-uppercase text-muted mb-3">Sources utilisées</h3>
    <ul class="list-unstyled mb-0 ai-sources">
        <?php foreach ($sources as $s): ?>
            <li class="mb-1">
                📄 <?= Security::e((string) ($s['title'] ?? $s['file'] ?? 'Document')) ?>
                <?php if (!empty($s['page'])): ?>
                    — p.<?= (int) $s['page'] ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="d-flex gap-2">
    <a href="<?= Security::e(Url::to('recommandations/' . (int) $dataCenter['id'])) ?>" class="btn btn-outline-secondary btn-sm">
        Recommandations
    </a>
    <a href="<?= Security::e(Url::to('simulations')) ?>" class="btn btn-outline-secondary btn-sm">
        Simulations
    </a>
    <a href="<?= Security::e(Url::to('dashboard')) ?>" class="btn btn-brand btn-sm">
        Dashboard
    </a>
</div>
