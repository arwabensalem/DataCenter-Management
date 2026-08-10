<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Url;

$pctPv = (float) $dataCenter['surface_totale'] > 0
    ? ((float) $dataCenter['surface_disponible_pv'] / (float) $dataCenter['surface_totale']) * 100
    : 0;
?>
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <a href="<?= Security::e(Url::to('data-centers')) ?>" class="text-decoration-none text-muted small">
            <i class="fa-solid fa-arrow-left me-1"></i> Liste des Data Centers
        </a>
        <h2 class="h4 mb-0 mt-1"><?= Security::e($dataCenter['nom']) ?></h2>
        <p class="text-muted mb-0">
            <?= Security::e($dataCenter['entreprise_nom']) ?>
            · <?= Security::e($dataCenter['localisation']) ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= Security::e(Url::to('data-centers/' . $dataCenter['id'] . '/edit')) ?>" class="btn btn-brand">
            <i class="fa-solid fa-pen me-1"></i> Modifier
        </a>
        <form method="post"
              action="<?= Security::e(Url::to('data-centers/' . $dataCenter['id'] . '/delete')) ?>"
              onsubmit="return confirm('Supprimer définitivement ce Data Center ?');">
            <?= Security::csrfField() ?>
            <button type="submit" class="btn btn-outline-danger">
                <i class="fa-solid fa-trash me-1"></i> Supprimer
            </button>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-ruler-combined"></i></div>
            <div>
                <div class="stat-label">Surface totale</div>
                <div class="stat-value"><?= Security::e(number_format((float) $dataCenter['surface_totale'], 0, ',', ' ')) ?> m²</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-solar-panel"></i></div>
            <div>
                <div class="stat-label">Surface PV</div>
                <div class="stat-value"><?= Security::e(number_format((float) $dataCenter['surface_disponible_pv'], 0, ',', ' ')) ?> m²</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-bolt"></i></div>
            <div>
                <div class="stat-label">Prix kWh STEG</div>
                <div class="stat-value"><?= Security::e(number_format((float) $dataCenter['prix_kwh_steg'], 3, ',', ' ')) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-clock"></i></div>
            <div>
                <div class="stat-label">Fonctionnement</div>
                <div class="stat-value"><?= Security::e(number_format((float) $dataCenter['heures_fonctionnement'], 1, ',', ' ')) ?> h</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Détails</h3>
            <dl class="row mb-0 detail-list">
                <dt class="col-sm-5">Entreprise</dt>
                <dd class="col-sm-7">
                    <?php if (Auth::isAdmin()): ?>
                        <a href="<?= Security::e(Url::to('entreprises/' . $dataCenter['entreprise_id'])) ?>">
                            <?= Security::e($dataCenter['entreprise_nom']) ?>
                        </a>
                    <?php else: ?>
                        <?= Security::e($dataCenter['entreprise_nom']) ?>
                    <?php endif; ?>
                </dd>
                <dt class="col-sm-5">Localisation</dt>
                <dd class="col-sm-7"><?= Security::e($dataCenter['localisation']) ?></dd>
                <dt class="col-sm-5">Part surface PV</dt>
                <dd class="col-sm-7"><?= Security::e(number_format($pctPv, 1, ',', ' ')) ?> %</dd>
                <dt class="col-sm-5">Équipements</dt>
                <dd class="col-sm-7">
                    <span class="badge bg-soft-blue"><?= (int) $dataCenter['nb_equipements'] ?></span>
                    <a href="<?= Security::e(Url::to('equipements?data_center_id=' . $dataCenter['id'])) ?>"
                       class="small ms-2">Gérer les équipements</a>
                </dd>
                <dt class="col-sm-5">Créé le</dt>
                <dd class="col-sm-7"><?= Security::e(date('d/m/Y H:i', strtotime($dataCenter['date_creation']))) ?></dd>
            </dl>
        </div>
    </div>
    <div class="col-md-5">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Potentiel photovoltaïque</h3>
            <div class="progress mb-2" style="height: 12px;">
                <div class="progress-bar bg-success" role="progressbar"
                     style="width: <?= min(100, max(0, $pctPv)) ?>%"
                     aria-valuenow="<?= (int) $pctPv ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <p class="small text-muted mb-0">
                <?= Security::e(number_format((float) $dataCenter['surface_disponible_pv'], 2, ',', ' ')) ?> m²
                disponibles sur
                <?= Security::e(number_format((float) $dataCenter['surface_totale'], 2, ',', ' ')) ?> m²
                pour l'installation de panneaux.
            </p>
        </div>
    </div>
</div>
