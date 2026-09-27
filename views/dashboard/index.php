<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Url;

$fmt = static fn(float $v, int $dec = 0): string => number_format($v, $dec, ',', ' ');
$kpis = $kpis ?? [];
$charts = $charts ?? [];
?>
<div class="welcome-banner p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="h4 mb-1">Tableau de bord décisionnel</h2>
            <p class="mb-0 opacity-90">
                Bonjour <?= Security::e(Auth::fullName()) ?> —
                vue d'ensemble énergétique <?= Auth::isAdmin() ? 'de la plateforme' : 'de votre entreprise' ?>.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= Security::e(Url::to('decision')) ?>" class="btn btn-light btn-sm">
                <i class="fa-solid fa-brain me-1"></i> Diagnostic
            </a>
            <a href="<?= Security::e(Url::to('recommandations')) ?>" class="btn btn-light btn-sm">
                <i class="fa-solid fa-lightbulb me-1"></i> Recommandations
            </a>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/partials/ai_insights.php'; ?>

<?php if (!empty($alerts)): ?>
<div class="form-card mb-4">
    <h3 class="h6 text-uppercase text-muted mb-3">
        <i class="fa-solid fa-bell me-1"></i> Alertes intelligentes
    </h3>
    <div class="row g-2">
        <?php foreach ($alerts as $a): ?>
            <div class="col-md-6">
                <div class="alert alert-<?= $a['niveau'] === 'danger' ? 'danger' : 'warning' ?> py-2 mb-0 d-flex gap-2">
                    <i class="fa-solid <?= Security::e($a['icone']) ?> mt-1"></i>
                    <div>
                        <strong class="small"><?= Security::e($a['titre']) ?></strong>
                        <?php if (!empty($a['data_center'])): ?>
                            <span class="badge bg-soft-blue ms-1"><?= Security::e($a['data_center']) ?></span>
                        <?php endif; ?>
                        <div class="small"><?= Security::e($a['message']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($pueList)): ?>
<div class="row g-3 mb-4">
    <?php foreach ($pueList as $item): ?>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-<?= Security::e($item['pue']['couleur'] === 'primary' ? 'blue' : ($item['pue']['couleur'] === 'success' ? 'green' : ($item['pue']['couleur'] === 'warning' ? 'teal' : 'blue'))) ?>">
                    <i class="fa-solid fa-gauge"></i>
                </div>
                <div>
                    <div class="stat-label">PUE — <?= Security::e($item['dc']) ?></div>
                    <div class="stat-value">
                        <?= $item['pue']['pue'] !== null ? Security::e(number_format((float) $item['pue']['pue'], 2, ',', '')) : '—' ?>
                        <span class="badge bg-<?= Security::e($item['pue']['couleur']) ?> ms-1"><?= Security::e($item['pue']['label']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-server"></i></div>
            <div>
                <div class="stat-label">Data Centers</div>
                <div class="stat-value"><?= (int) ($kpis['nb_data_centers'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-bolt"></i></div>
            <div>
                <div class="stat-label">Consommation / an</div>
                <div class="stat-value"><?= Security::e($fmt((float) ($kpis['consommation_totale'] ?? 0))) ?>
                    <small class="fs-6">kWh</small></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-solar-panel"></i></div>
            <div>
                <div class="stat-label">Production PV / an</div>
                <div class="stat-value"><?= Security::e($fmt((float) ($kpis['production_pv'] ?? 0))) ?>
                    <small class="fs-6">kWh</small></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-chart-pie"></i></div>
            <div>
                <div class="stat-label">Couverture PV</div>
                <div class="stat-value"><?= Security::e($fmt((float) ($kpis['couverture_pv'] ?? 0), 1)) ?> %</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-plug"></i></div>
            <div>
                <div class="stat-label">Énergie STEG / an</div>
                <div class="stat-value"><?= Security::e($fmt((float) ($kpis['energie_steg'] ?? 0))) ?>
                    <small class="fs-6">kWh</small></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-coins"></i></div>
            <div>
                <div class="stat-label">Coût annuel STEG</div>
                <div class="stat-value"><?= Security::e($fmt((float) ($kpis['cout_annuel'] ?? 0))) ?>
                    <small class="fs-6">TND</small></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-percent"></i></div>
            <div>
                <div class="stat-label">ROI moyen</div>
                <div class="stat-value"><?= Security::e($fmt((float) ($kpis['roi'] ?? 0), 1)) ?> %</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-hourglass-half"></i></div>
            <div>
                <div class="stat-label">Amortissement</div>
                <div class="stat-value">
                    <?php if ((float) ($kpis['temps_amortissement'] ?? 0) > 0): ?>
                        <?= Security::e($fmt((float) $kpis['temps_amortissement'], 1)) ?>
                        <small class="fs-6">ans</small>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-leaf"></i></div>
            <div>
                <div class="stat-label">CO₂ évité / an</div>
                <div class="stat-value"><?= Security::e($fmt((float) ($kpis['co2_evite'] ?? 0))) ?>
                    <small class="fs-6">kg</small></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-building"></i></div>
            <div>
                <div class="stat-label"><?= Auth::isAdmin() ? 'Entreprises' : 'Équipements' ?></div>
                <div class="stat-value">
                    <?= Auth::isAdmin() ? (int) $nbEntreprises : (int) ($kpis['nb_equipements'] ?? 0) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-flask"></i></div>
            <div>
                <div class="stat-label">Simulations</div>
                <div class="stat-value"><?= (int) $nbSimulations ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-lightbulb"></i></div>
            <div>
                <div class="stat-label">Recommandations</div>
                <div class="stat-value"><?= (int) $nbRecos ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Consommation &amp; production mensuelles</h3>
            <canvas id="chartMensuel" height="120"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Mix énergétique</h3>
            <canvas id="chartMix" height="200"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Production vs Consommation (par DC)</h3>
            <canvas id="chartComparaison" height="160"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Répartition par équipement</h3>
            <canvas id="chartRepartition" height="160"></canvas>
        </div>
    </div>
</div>

<?php if (!empty($topRecos)): ?>
<div class="form-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h6 text-uppercase text-muted mb-0">Alertes &amp; recommandations récentes</h3>
        <a href="<?= Security::e(Url::to('recommandations')) ?>" class="small">Tout voir</a>
    </div>
    <ul class="list-group list-group-flush">
        <?php foreach ($topRecos as $r): ?>
            <li class="list-group-item px-0 d-flex gap-2 align-items-start">
                <span class="badge <?= $r['priorite'] === 'haute' ? 'bg-danger' : ($r['priorite'] === 'moyenne' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                    <?= Security::e(ucfirst($r['priorite'])) ?>
                </span>
                <span class="small"><?= Security::e($r['message']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<script>
window.GDC_CHARTS = <?= json_encode($charts, JSON_UNESCAPED_UNICODE) ?>;
</script>
