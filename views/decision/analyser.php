<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;

$fmt = static fn(float $v, int $d = 0): string => number_format($v, $d, ',', ' ');
?>
<div class="mb-3">
    <a href="<?= Security::e(Url::to('decision')) ?>" class="text-muted text-decoration-none small">
        <i class="fa-solid fa-arrow-left me-1"></i> Aide à la décision
    </a>
    <h2 class="h4 mt-1">Diagnostic — <?= Security::e($dataCenter['nom']) ?></h2>
</div>

<div class="welcome-banner p-4 mb-4">
    <h3 class="h5 mb-2"><i class="fa-solid fa-brain me-2"></i>Conclusion du moteur d'aide à la décision</h3>
    <p class="mb-0 opacity-90"><?= Security::e($decision['diagnostic']) ?></p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="form-card h-100 text-center">
            <div class="stat-label">PUE actuel</div>
            <div class="display-6 fw-bold text-brand">
                <?= $pue['pue'] !== null ? Security::e($fmt((float) $pue['pue'], 2)) : '—' ?>
            </div>
            <span class="badge bg-<?= Security::e($pue['couleur']) ?> mt-2"><?= Security::e($pue['label']) ?></span>
            <p class="small text-muted mt-3 mb-0"><?= Security::e($pue['explication']) ?></p>
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Impact environnemental</h3>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="kpi-box">
                        <div class="label">CO₂ avant</div>
                        <div class="value"><?= Security::e($fmt((float) $impact['co2_avant_tonnes'], 2)) ?> t</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="kpi-box">
                        <div class="label">CO₂ après</div>
                        <div class="value"><?= Security::e($fmt((float) $impact['co2_apres_tonnes'], 2)) ?> t</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="kpi-box">
                        <div class="label">Évité (PV)</div>
                        <div class="value text-success"><?= Security::e($fmt((float) $impact['co2_evite_tonnes'], 2)) ?> t</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="kpi-box">
                        <div class="label">Réduction</div>
                        <div class="value"><?= Security::e($fmt((float) $impact['pourcentage_reduction'], 1)) ?> %</div>
                    </div>
                </div>
            </div>
            <canvas id="chartImpactCo2" height="100" class="mt-3"></canvas>
        </div>
    </div>
</div>

<?php if (!empty($alerts)): ?>
<div class="form-card mb-4">
    <h3 class="h6 text-uppercase text-muted mb-3">Alertes intelligentes</h3>
    <?php foreach ($alerts as $a): ?>
        <div class="alert alert-<?= $a['niveau'] === 'danger' ? 'danger' : 'warning' ?> d-flex gap-2">
            <i class="fa-solid <?= Security::e($a['icone']) ?> mt-1"></i>
            <div>
                <strong><?= Security::e($a['titre']) ?></strong>
                <div class="small"><?= Security::e($a['message']) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Scénarios évalués</h3>
            <table class="table table-sm">
                <thead><tr><th>Scénario</th><th>Couverture</th><th>CO₂ évité</th><th>Score</th></tr></thead>
                <tbody>
                <?php foreach ($scenarios as $k => $s): ?>
                    <tr class="<?= ($decision['meilleur'] ?? '') === $k ? 'table-success' : '' ?>">
                        <td><strong><?= Security::e($k) ?></strong> <?= Security::e($s['libelle'] ?? '') ?></td>
                        <td><?= Security::e($fmt((float) $s['couverture_solaire'], 1)) ?> %</td>
                        <td><?= Security::e($fmt((float) $s['co2_evite'], 0)) ?> kg</td>
                        <td><?= Security::e($fmt((float) $s['score'], 1)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Recommandations (priorisées)</h3>
            <?php if (empty($recommandations)): ?>
                <p class="text-muted mb-0">Aucune recommandation.</p>
            <?php else: ?>
                <ol class="mb-0 ps-3">
                    <?php foreach (array_slice($recommandations, 0, 8) as $r): ?>
                        <li class="mb-2 small">
                            <span class="badge bg-<?= $r['priorite'] === 'haute' ? 'danger' : ($r['priorite'] === 'moyenne' ? 'warning text-dark' : 'secondary') ?>">
                                <?= Security::e($r['priorite']) ?>
                            </span>
                            <?= Security::e($r['message']) ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
window.GDC_IMPACT_CHART = {
    labels: ['CO₂ avant', 'CO₂ après', 'CO₂ évité'],
    values: [
        <?= (float) $impact['co2_avant_tonnes'] ?>,
        <?= (float) $impact['co2_apres_tonnes'] ?>,
        <?= (float) $impact['co2_evite_tonnes'] ?>
    ]
};
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('chartImpactCo2');
    if (!el || typeof Chart === 'undefined' || !window.GDC_IMPACT_CHART) return;
    new Chart(el, {
        type: 'bar',
        data: {
            labels: window.GDC_IMPACT_CHART.labels,
            datasets: [{
                label: 'tonnes CO₂ / an',
                data: window.GDC_IMPACT_CHART.values,
                backgroundColor: ['#94a3b8', '#1a5f8a', '#1b7a4e'],
            }],
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
    });
});
</script>
