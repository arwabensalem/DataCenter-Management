<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;

$fmt = static fn(float $v, int $dec = 2): string => number_format($v, $dec, ',', ' ');
$deltaClass = static function (float $v, bool $inverse = false): string {
    if (abs($v) < 0.0001) {
        return 'text-muted';
    }
    $positif = $v > 0;
    if ($inverse) {
        $positif = !$positif;
    }
    return $positif ? 'text-success' : 'text-danger';
};
?>
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <a href="<?= Security::e(Url::to('simulations')) ?>" class="text-decoration-none text-muted small">
            <i class="fa-solid fa-arrow-left me-1"></i> Simulations
        </a>
        <h2 class="h4 mb-0 mt-1"><?= Security::e($simulation['nom']) ?></h2>
        <p class="text-muted mb-0">
            <?= Security::e($simulation['data_center_nom']) ?>
            · <?= Security::e(date('d/m/Y H:i', strtotime($simulation['date_simulation']))) ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= Security::e(Url::to('ai?dc=' . (int) ($simulation['data_center_id'] ?? 0))) ?>"
           class="btn btn-outline-secondary btn-sm"
           title="Demander une explication IA des résultats calculés">
            <i class="fa-solid fa-robot me-1"></i> Expliquer (IA)
        </a>
        <form method="post" action="<?= Security::e(Url::to('simulations/' . $simulation['id'] . '/delete')) ?>"
              onsubmit="return confirm('Supprimer cette simulation ?');">
            <?= Security::csrfField() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fa-solid fa-trash me-1"></i> Supprimer
            </button>
        </form>
    </div>
</div>

<?php if (!empty($simulation['description'])): ?>
    <div class="alert alert-light border mb-4"><?= Security::e($simulation['description']) ?></div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-xl">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-leaf"></i></div>
            <div>
                <div class="stat-label">Énergie économisée</div>
                <div class="stat-value <?= $deltaClass((float) $simulation['energie_economisee']) ?>">
                    <?= Security::e($fmt((float) $simulation['energie_economisee'], 0)) ?>
                    <small class="fs-6">kWh</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-percent"></i></div>
            <div>
                <div class="stat-label">Réduction conso</div>
                <div class="stat-value"><?= Security::e($fmt((float) $simulation['pourcentage_reduction'], 1)) ?> %</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl">
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-coins"></i></div>
            <div>
                <div class="stat-label">Coût économisé</div>
                <div class="stat-value"><?= Security::e($fmt((float) $simulation['cout_economise'], 0)) ?>
                    <small class="fs-6">TND</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-plug"></i></div>
            <div>
                <div class="stat-label">↓ Dépendance STEG</div>
                <div class="stat-value"><?= Security::e($fmt((float) $simulation['reduction_dependance_steg'], 1)) ?> pts</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-cloud"></i></div>
            <div>
                <div class="stat-label">CO₂ évité</div>
                <div class="stat-value"><?= Security::e($fmt((float) $simulation['co2_evite'], 0)) ?>
                    <small class="fs-6">kg</small></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">AVANT</h3>
            <table class="table table-sm mb-0">
                <tr><td>Conso annuelle</td><td class="text-end fw-semibold"><?= Security::e($fmt((float) $avant['conso_annuelle_kwh'], 0)) ?> kWh</td></tr>
                <tr><td>Production PV</td><td class="text-end"><?= Security::e($fmt((float) $avant['production_pv_kwh'], 0)) ?> kWh</td></tr>
                <tr><td>Énergie solaire</td><td class="text-end"><?= Security::e($fmt((float) $avant['energie_pv_kwh'], 0)) ?> kWh</td></tr>
                <tr><td>Énergie STEG</td><td class="text-end"><?= Security::e($fmt((float) $avant['energie_steg_kwh'], 0)) ?> kWh</td></tr>
                <tr><td>Couverture PV</td><td class="text-end"><?= Security::e($fmt((float) $avant['taux_couverture'], 1)) ?> %</td></tr>
                <tr><td>Dépendance STEG</td><td class="text-end"><?= Security::e($fmt((float) $avant['dependance_steg'], 1)) ?> %</td></tr>
                <tr><td>Panneaux</td><td class="text-end"><?= (int) $avant['nombre_panneaux'] ?></td></tr>
                <tr><td>Ensoleillement</td><td class="text-end"><?= Security::e($fmt((float) $avant['heures_ensoleillement'], 1)) ?> h/j</td></tr>
                <tr><td>Coût STEG / an</td><td class="text-end"><?= Security::e($fmt((float) $avant['cout_annuel_steg'], 0)) ?> TND</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-card h-100 border-success">
            <h3 class="h6 text-uppercase text-success mb-3">APRÈS</h3>
            <table class="table table-sm mb-0">
                <tr><td>Conso annuelle</td><td class="text-end fw-semibold"><?= Security::e($fmt((float) $apres['conso_annuelle_kwh'], 0)) ?> kWh</td></tr>
                <tr><td>Production PV</td><td class="text-end"><?= Security::e($fmt((float) $apres['production_pv_kwh'], 0)) ?> kWh</td></tr>
                <tr><td>Énergie solaire</td><td class="text-end"><?= Security::e($fmt((float) $apres['energie_pv_kwh'], 0)) ?> kWh</td></tr>
                <tr><td>Énergie STEG</td><td class="text-end"><?= Security::e($fmt((float) $apres['energie_steg_kwh'], 0)) ?> kWh</td></tr>
                <tr><td>Couverture PV</td><td class="text-end"><?= Security::e($fmt((float) $apres['taux_couverture'], 1)) ?> %</td></tr>
                <tr><td>Dépendance STEG</td><td class="text-end"><?= Security::e($fmt((float) $apres['dependance_steg'], 1)) ?> %</td></tr>
                <tr><td>Panneaux</td><td class="text-end"><?= (int) $apres['nombre_panneaux'] ?></td></tr>
                <tr><td>Ensoleillement</td><td class="text-end"><?= Security::e($fmt((float) $apres['heures_ensoleillement'], 1)) ?> h/j</td></tr>
                <tr><td>Coût STEG / an</td><td class="text-end"><?= Security::e($fmt((float) $apres['cout_annuel_steg'], 0)) ?> TND</td></tr>
            </table>
        </div>
    </div>
</div>
