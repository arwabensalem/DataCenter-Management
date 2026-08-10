<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;

$fmt = static fn(float $v, int $dec = 2): string => number_format($v, $dec, ',', ' ');
$pctPv = (float) $installation['taux_couverture'];
$pctSteg = max(0, 100 - $pctPv);
?>
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <a href="<?= Security::e(Url::to('photovoltaique')) ?>" class="text-decoration-none text-muted small">
            <i class="fa-solid fa-arrow-left me-1"></i> Photovoltaïque
        </a>
        <h2 class="h4 mb-0 mt-1">Résultats — <?= Security::e($dataCenter['nom']) ?></h2>
        <p class="text-muted mb-0">
            <?= Security::e($installation['type_panneau_nom']) ?>
            · <?= Security::e($fmt((float) $installation['heures_ensoleillement'], 1)) ?> h/j d'ensoleillement
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= Security::e(Url::to('photovoltaique/dimensionner/' . $dataCenter['id'])) ?>" class="btn btn-brand">
            <i class="fa-solid fa-rotate me-1"></i> Recalculer
        </a>
        <form method="post"
              action="<?= Security::e(Url::to('photovoltaique/delete/' . $dataCenter['id'])) ?>"
              onsubmit="return confirm('Supprimer ce dimensionnement ?');">
            <?= Security::csrfField() ?>
            <button type="submit" class="btn btn-outline-danger">
                <i class="fa-solid fa-trash me-1"></i>
            </button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-solar-panel"></i></div>
            <div>
                <div class="stat-label">Panneaux</div>
                <div class="stat-value"><?= (int) $installation['nombre_panneaux'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-gauge-high"></i></div>
            <div>
                <div class="stat-label">Puissance néces.</div>
                <div class="stat-value"><?= Security::e($fmt((float) $installation['puissance_necessaire_kwc'], 1)) ?> <small class="fs-6">kWc</small></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-chart-pie"></i></div>
            <div>
                <div class="stat-label">Couverture</div>
                <div class="stat-value"><?= Security::e($fmt($pctPv, 1)) ?> %</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-leaf"></i></div>
            <div>
                <div class="stat-label">CO₂ évité / an</div>
                <div class="stat-value"><?= Security::e($fmt($co2Evite, 0)) ?> <small class="fs-6">kg</small></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Bilan énergétique annuel</h3>
            <dl class="row detail-list mb-3">
                <dt class="col-6">Consommation DC</dt>
                <dd class="col-6"><?= Security::e($fmt($consoAnnuelle, 0)) ?> kWh</dd>
                <dt class="col-6">Production PV</dt>
                <dd class="col-6"><?= Security::e($fmt((float) $installation['production_annuelle_kwh'], 0)) ?> kWh</dd>
                <dt class="col-6">Énergie solaire utilisée</dt>
                <dd class="col-6 text-success fw-semibold"><?= Security::e($fmt((float) $installation['energie_pv_kwh'], 0)) ?> kWh</dd>
                <dt class="col-6">Énergie STEG</dt>
                <dd class="col-6 text-danger fw-semibold"><?= Security::e($fmt((float) $installation['energie_steg_kwh'], 0)) ?> kWh</dd>
                <dt class="col-6">Surface nécessaire</dt>
                <dd class="col-6"><?= Security::e($fmt((float) $installation['surface_necessaire'], 1)) ?> m²
                    / <?= Security::e($fmt((float) $dataCenter['surface_disponible_pv'], 1)) ?> m² dispo.</dd>
            </dl>
            <div class="mb-1 small d-flex justify-content-between">
                <span>Solaire <?= Security::e($fmt($pctPv, 1)) ?> %</span>
                <span>STEG <?= Security::e($fmt($pctSteg, 1)) ?> %</span>
            </div>
            <div class="progress" style="height: 14px;">
                <div class="progress-bar bg-success" style="width: <?= min(100, $pctPv) ?>%"></div>
                <div class="progress-bar bg-secondary" style="width: <?= min(100, $pctSteg) ?>%"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Indicateurs financiers</h3>
            <dl class="row detail-list mb-0">
                <dt class="col-6">Coût d'installation</dt>
                <dd class="col-6 fw-bold"><?= Security::e($fmt((float) $installation['cout_installation'], 0)) ?> TND</dd>
                <dt class="col-6">Économie annuelle</dt>
                <dd class="col-6 text-success"><?= Security::e($fmt($economie, 0)) ?> TND</dd>
                <dt class="col-6">ROI annuel</dt>
                <dd class="col-6"><?= Security::e($fmt((float) $installation['roi_pourcentage'], 1)) ?> %</dd>
                <dt class="col-6">Temps d'amortissement</dt>
                <dd class="col-6">
                    <?php if ((float) $installation['temps_amortissement'] > 0): ?>
                        <?= Security::e($fmt((float) $installation['temps_amortissement'], 1)) ?> ans
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </dd>
                <dt class="col-6">Prix panneau</dt>
                <dd class="col-6"><?= Security::e($fmt((float) $installation['prix_unitaire'], 0)) ?> TND</dd>
                <dt class="col-6">Rendement</dt>
                <dd class="col-6"><?= Security::e($fmt((float) $installation['rendement'], 1)) ?> %</dd>
                <dt class="col-6">Calculé le</dt>
                <dd class="col-6"><?= Security::e(date('d/m/Y H:i', strtotime($installation['date_calcul']))) ?></dd>
            </dl>
        </div>
    </div>
</div>
