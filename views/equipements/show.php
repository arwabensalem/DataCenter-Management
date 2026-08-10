<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;

$fmt = static fn(float $v, int $dec = 2): string => number_format($v, $dec, ',', ' ');
?>
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <a href="<?= Security::e(Url::to('equipements?data_center_id=' . $equipement['data_center_id'])) ?>"
           class="text-decoration-none text-muted small">
            <i class="fa-solid fa-arrow-left me-1"></i> Équipements
        </a>
        <h2 class="h4 mb-0 mt-1"><?= Security::e($equipement['nom']) ?></h2>
        <p class="text-muted mb-0">
            <span class="badge bg-soft-green"><?= Security::e($equipement['categorie_label']) ?></span>
            · <?= Security::e($equipement['data_center_nom']) ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= Security::e(Url::to('equipements/' . $equipement['id'] . '/edit')) ?>" class="btn btn-brand">
            <i class="fa-solid fa-pen me-1"></i> Modifier
        </a>
        <form method="post"
              action="<?= Security::e(Url::to('equipements/' . $equipement['id'] . '/delete')) ?>"
              onsubmit="return confirm('Supprimer cet équipement ?');">
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
            <div class="stat-icon bg-green"><i class="fa-solid fa-calendar-day"></i></div>
            <div>
                <div class="stat-label">Journalière</div>
                <div class="stat-value"><?= Security::e($fmt((float) $equipement['conso_journaliere'])) ?> <small class="fs-6">kWh</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-calendar"></i></div>
            <div>
                <div class="stat-label">Mensuelle</div>
                <div class="stat-value"><?= Security::e($fmt((float) $equipement['conso_mensuelle'])) ?> <small class="fs-6">kWh</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-calendar-check"></i></div>
            <div>
                <div class="stat-label">Annuelle</div>
                <div class="stat-value"><?= Security::e($fmt((float) $equipement['conso_annuelle'], 0)) ?> <small class="fs-6">kWh</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-coins"></i></div>
            <div>
                <div class="stat-label">Coût annuel STEG</div>
                <div class="stat-value"><?= Security::e($fmt((float) $coutAnnuel)) ?> <small class="fs-6">TND</small></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Fiche technique</h3>
            <dl class="row mb-0 detail-list">
                <dt class="col-sm-5">Fabricant</dt>
                <dd class="col-sm-7"><?= Security::e($equipement['fabricant']) ?></dd>
                <dt class="col-sm-5">Modèle</dt>
                <dd class="col-sm-7"><?= Security::e($equipement['modele']) ?></dd>
                <dt class="col-sm-5">Quantité</dt>
                <dd class="col-sm-7"><?= (int) $equipement['quantite'] ?></dd>
                <dt class="col-sm-5">Puissance unitaire</dt>
                <dd class="col-sm-7"><?= Security::e($fmt((float) $equipement['puissance_watts'], 0)) ?> W</dd>
                <dt class="col-sm-5">Taux d'utilisation</dt>
                <dd class="col-sm-7"><?= Security::e($fmt((float) $equipement['taux_utilisation'], 1)) ?> %</dd>
                <dt class="col-sm-5">Heures / jour</dt>
                <dd class="col-sm-7"><?= Security::e($fmt((float) $equipement['heures_fonctionnement'], 1)) ?> h</dd>
            </dl>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Formule appliquée</h3>
            <p class="small font-monospace bg-light p-3 rounded mb-3">
                kWh/j = (P<sub>W</sub> × taux/100 × qté × heures) / 1000
            </p>
            <p class="small text-muted mb-0">
                Data Center :
                <a href="<?= Security::e(Url::to('data-centers/' . $equipement['data_center_id'])) ?>">
                    <?= Security::e($equipement['data_center_nom']) ?>
                </a><br>
                Prix kWh STEG :
                <?= Security::e($fmt((float) $equipement['prix_kwh_steg'], 4)) ?> TND
            </p>
        </div>
    </div>
</div>
