<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\EnergyCalculator;
use App\Helpers\Security;
use App\Helpers\Url;

$old = $old ?? [];
$errors = $errors ?? [];
$fmt = static fn(float $v, int $dec = 2): string => number_format($v, $dec, ',', ' ');
$dcId = (string) ($old['data_center_id'] ?? ($preselect ?: ''));
?>
<div class="mb-3">
    <a href="<?= Security::e(Url::to('simulations')) ?>" class="text-decoration-none text-muted">
        <i class="fa-solid fa-arrow-left me-1"></i> Simulations
    </a>
</div>

<?php if (isset($errors['mods'])): ?>
    <div class="alert alert-warning"><?= Security::e($errors['mods']) ?></div>
<?php endif; ?>

<form method="get" action="<?= Security::e(Url::to('simulations/create')) ?>" class="mb-4">
    <div class="form-card">
        <label class="form-label" for="data_center_id_sel">1. Choisir le Data Center</label>
        <div class="row g-2">
            <div class="col-md-8">
                <select name="data_center_id" id="data_center_id_sel" class="form-select" onchange="this.form.submit()">
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($dataCenters as $dcOpt): ?>
                        <option value="<?= (int) $dcOpt['id'] ?>" <?= (string) $dcOpt['id'] === $dcId ? 'selected' : '' ?>>
                            <?= Security::e($dcOpt['nom']) ?>
                            <?php if (Auth::isAdmin()): ?> — <?= Security::e($dcOpt['entreprise_nom']) ?><?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</form>

<?php if ($avant === null): ?>
    <div class="alert alert-light border">Sélectionnez un Data Center pour configurer le scénario.</div>
<?php else: ?>
<form method="post" action="<?= Security::e(Url::to('simulations/store')) ?>" class="row g-4" novalidate>
    <?= Security::csrfField() ?>
    <input type="hidden" name="data_center_id" value="<?= Security::e($dcId) ?>">

    <div class="col-12">
        <div class="form-card">
            <h2 class="h6 text-uppercase text-muted mb-3">2. Identité du scénario</h2>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="nom">Nom *</label>
                    <input type="text" name="nom" id="nom" class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($old['nom'] ?? '') ?>" required
                           placeholder="Ex. Remplacement serveurs + panneaux">
                    <?php if (isset($errors['nom'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['nom']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="description">Description</label>
                    <input type="text" name="description" id="description" class="form-control"
                           value="<?= Security::e($old['description'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-card h-100 border-success border-opacity-25">
            <h2 class="h6 text-success text-uppercase mb-3"><i class="fa-solid fa-clock-rotate-left me-1"></i> État AVANT</h2>
            <dl class="detail-list mb-0 small">
                <dt>Conso annuelle</dt>
                <dd class="fw-bold"><?= Security::e($fmt((float) $avant['conso_annuelle_kwh'], 0)) ?> kWh</dd>
                <dt>Énergie STEG</dt>
                <dd><?= Security::e($fmt((float) $avant['energie_steg_kwh'], 0)) ?> kWh</dd>
                <dt>Couverture PV</dt>
                <dd><?= Security::e($fmt((float) $avant['taux_couverture'], 1)) ?> %</dd>
                <dt>Panneaux</dt>
                <dd><?= (int) $avant['nombre_panneaux'] ?></dd>
                <dt>Dépendance STEG</dt>
                <dd><?= Security::e($fmt((float) $avant['dependance_steg'], 1)) ?> %</dd>
                <dt>Coût STEG / an</dt>
                <dd><?= Security::e($fmt((float) $avant['cout_annuel_steg'], 0)) ?> TND</dd>
            </dl>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="form-card">
            <h2 class="h6 text-uppercase text-muted mb-3">3. Modifications du scénario (APRÈS)</h2>

            <h3 class="h6 text-brand mt-2">Équipements</h3>
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <label class="form-label small">Ajouter serveurs (qté)</label>
                    <input type="number" name="ajouter_serveurs_qte" class="form-control form-control-sm" min="0"
                           value="<?= Security::e($old['ajouter_serveurs_qte'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Puissance (W)</label>
                    <input type="text" name="ajouter_serveurs_puissance" class="form-control form-control-sm"
                           value="<?= Security::e($old['ajouter_serveurs_puissance'] ?? '350') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Taux (%)</label>
                    <input type="text" name="ajouter_serveurs_taux" class="form-control form-control-sm"
                           value="<?= Security::e($old['ajouter_serveurs_taux'] ?? '65') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Heures / j</label>
                    <input type="text" name="ajouter_serveurs_heures" class="form-control form-control-sm"
                           value="<?= Security::e($old['ajouter_serveurs_heures'] ?? '24') ?>">
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label small">Supprimer / réduire équipement</label>
                    <select name="supprimer_equipement_id" class="form-select form-select-sm">
                        <option value="">— Aucun —</option>
                        <?php foreach ($equipements as $eq): ?>
                            <option value="<?= (int) $eq['id'] ?>">
                                <?= Security::e($eq['nom']) ?> (×<?= (int) $eq['quantite'] ?>)
                                — <?= Security::e(EnergyCalculator::categoryLabel($eq['categorie'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Quantité à retirer</label>
                    <input type="number" name="supprimer_quantite" class="form-control form-control-sm" min="1"
                           value="<?= Security::e($old['supprimer_quantite'] ?? '') ?>">
                </div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-md-5">
                    <label class="form-label small">Remplacer par modèle plus économe</label>
                    <select name="remplacer_equipement_id" class="form-select form-select-sm">
                        <option value="">— Aucun —</option>
                        <?php foreach ($equipements as $eq): ?>
                            <option value="<?= (int) $eq['id'] ?>">
                                <?= Security::e($eq['nom']) ?>
                                (<?= Security::e($fmt((float) $eq['puissance_watts'], 0)) ?> W,
                                <?= Security::e($fmt((float) $eq['taux_utilisation'], 0)) ?> %)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Nouvelle puissance (W)</label>
                    <input type="text" name="nouvelle_puissance" class="form-control form-control-sm"
                           placeholder="ex. 350" value="<?= Security::e($old['nouvelle_puissance'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Nouveau taux (%)</label>
                    <input type="text" name="nouveau_taux" class="form-control form-control-sm"
                           placeholder="ex. 55" value="<?= Security::e($old['nouveau_taux'] ?? '') ?>">
                </div>
            </div>

            <h3 class="h6 text-brand">Photovoltaïque &amp; ensoleillement</h3>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small">Ajouter des panneaux</label>
                    <input type="number" name="ajouter_panneaux" class="form-control form-control-sm" min="0"
                           value="<?= Security::e($old['ajouter_panneaux'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Nouveau nombre de panneaux</label>
                    <input type="number" name="nouveau_nombre_panneaux" class="form-control form-control-sm" min="0"
                           placeholder="Actuel : <?= (int) $avant['nombre_panneaux'] ?>"
                           value="<?= Security::e($old['nouveau_nombre_panneaux'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Changer de ville</label>
                    <select name="ville" class="form-select form-select-sm">
                        <option value="">— Conserver —</option>
                        <?php foreach ($regions as $r): ?>
                            <option value="<?= Security::e($r['ville']) ?>">
                                <?= Security::e($r['ville']) ?> (<?= Security::e($fmt((float) $r['heures_ensoleillement'], 1)) ?> h)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mt-2">
                    <label class="form-label small">Heures ensoleillement / j</label>
                    <input type="text" name="heures_ensoleillement" class="form-control form-control-sm"
                           placeholder="Actuel : <?= Security::e($fmt((float) $avant['heures_ensoleillement'], 1)) ?>"
                           value="<?= Security::e($old['heures_ensoleillement'] ?? '') ?>">
                </div>
            </div>

            <?php if ($avant['type_panneau'] === null): ?>
                <div class="alert alert-warning py-2 small mt-3 mb-0">
                    Aucune installation PV dimensionnée : les modifications de panneaux n'auront d'effet
                    qu'après un dimensionnement photovoltaïque.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-brand btn-lg">
            <i class="fa-solid fa-flask me-1"></i> Lancer la simulation
        </button>
        <a href="<?= Security::e(Url::to('simulations')) ?>" class="btn btn-outline-secondary btn-lg ms-1">Annuler</a>
    </div>
</form>
<?php endif; ?>
