<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\EnergyCalculator;
use App\Helpers\Security;
use App\Helpers\Url;

$isCreate = ($mode ?? '') === 'create';
$old = $old ?? [];
$errors = $errors ?? [];

$val = static function (string $key, ?array $equipement, array $old): string {
    if (array_key_exists($key, $old) && $old[$key] !== '') {
        return (string) $old[$key];
    }
    if ($equipement !== null && isset($equipement[$key])) {
        return (string) $equipement[$key];
    }
    return '';
};

$action = $isCreate
    ? Url::to('equipements/store')
    : Url::to('equipements/' . $equipement['id'] . '/update');

$dcId = $val('data_center_id', $equipement, $old);

// Aperçu calcul côté client (JS) — valeurs initiales
$preview = null;
if (!$isCreate && $equipement !== null) {
    $preview = EnergyCalculator::enrich($equipement);
}
?>
<div class="mb-3">
    <a href="<?= Security::e($isCreate ? Url::to('equipements') : Url::to('equipements/' . $equipement['id'])) ?>"
       class="text-decoration-none text-muted">
        <i class="fa-solid fa-arrow-left me-1"></i> Retour
    </a>
</div>

<form method="post" action="<?= Security::e($action) ?>" class="row g-4" id="equipement-form" novalidate>
    <?= Security::csrfField() ?>

    <div class="col-lg-8">
        <div class="form-card">
            <h2 class="h6 text-uppercase text-muted mb-3">
                <i class="fa-solid fa-microchip me-1"></i> Caractéristiques
            </h2>

            <div class="mb-3">
                <label class="form-label" for="data_center_id">Data Center *</label>
                <select id="data_center_id" name="data_center_id"
                        class="form-select <?= isset($errors['data_center_id']) ? 'is-invalid' : '' ?>" required>
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($dataCenters as $dc): ?>
                        <option value="<?= (int) $dc['id'] ?>"
                            <?= (string) $dc['id'] === $dcId ? 'selected' : '' ?>>
                            <?= Security::e($dc['nom']) ?>
                            <?php if (Auth::isAdmin()): ?>
                                — <?= Security::e($dc['entreprise_nom']) ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['data_center_id'])): ?>
                    <div class="invalid-feedback"><?= Security::e($errors['data_center_id']) ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label" for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom"
                           class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('nom', $equipement, $old)) ?>" required>
                    <?php if (isset($errors['nom'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['nom']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="categorie">Catégorie *</label>
                    <select id="categorie" name="categorie"
                            class="form-select <?= isset($errors['categorie']) ? 'is-invalid' : '' ?>" required>
                        <?php foreach ($categories as $code => $label): ?>
                            <option value="<?= Security::e($code) ?>"
                                <?= $val('categorie', $equipement, $old) === $code ? 'selected' : '' ?>>
                                <?= Security::e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['categorie'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['categorie']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="fabricant">Fabricant *</label>
                    <input type="text" id="fabricant" name="fabricant"
                           class="form-control <?= isset($errors['fabricant']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('fabricant', $equipement, $old)) ?>" required>
                    <?php if (isset($errors['fabricant'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['fabricant']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="modele">Modèle *</label>
                    <input type="text" id="modele" name="modele"
                           class="form-control <?= isset($errors['modele']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('modele', $equipement, $old)) ?>" required>
                    <?php if (isset($errors['modele'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['modele']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="quantite">Quantité *</label>
                    <input type="number" id="quantite" name="quantite" min="1" step="1"
                           class="form-control calc-input <?= isset($errors['quantite']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('quantite', $equipement, $old) ?: '1') ?>" required>
                    <?php if (isset($errors['quantite'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['quantite']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="puissance_watts">Puissance (W) *</label>
                    <input type="text" id="puissance_watts" name="puissance_watts"
                           class="form-control calc-input <?= isset($errors['puissance_watts']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('puissance_watts', $equipement, $old)) ?>" required>
                    <?php if (isset($errors['puissance_watts'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['puissance_watts']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="taux_utilisation">Taux util. (%) *</label>
                    <input type="text" id="taux_utilisation" name="taux_utilisation"
                           class="form-control calc-input <?= isset($errors['taux_utilisation']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('taux_utilisation', $equipement, $old) ?: '70') ?>" required>
                    <?php if (isset($errors['taux_utilisation'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['taux_utilisation']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="heures_fonctionnement">Heures / jour *</label>
                    <input type="text" id="heures_fonctionnement" name="heures_fonctionnement"
                           class="form-control calc-input <?= isset($errors['heures_fonctionnement']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('heures_fonctionnement', $equipement, $old) ?: '24') ?>" required>
                    <?php if (isset($errors['heures_fonctionnement'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['heures_fonctionnement']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-card calc-preview h-100">
            <h2 class="h6 text-uppercase text-muted mb-3">
                <i class="fa-solid fa-calculator me-1"></i> Consommation estimée
            </h2>
            <p class="small text-muted">Calculée automatiquement — non saisissable.</p>
            <dl class="mb-0">
                <dt class="text-muted small">Journalière</dt>
                <dd class="fs-5 fw-bold text-brand" id="preview-jour">
                    <?= $preview ? Security::e(number_format((float) $preview['conso_journaliere'], 2, ',', ' ')) : '—' ?> kWh
                </dd>
                <dt class="text-muted small">Mensuelle</dt>
                <dd class="fs-6 fw-semibold" id="preview-mois">
                    <?= $preview ? Security::e(number_format((float) $preview['conso_mensuelle'], 2, ',', ' ')) : '—' ?> kWh
                </dd>
                <dt class="text-muted small">Annuelle</dt>
                <dd class="fs-6 fw-semibold mb-0" id="preview-an">
                    <?= $preview ? Security::e(number_format((float) $preview['conso_annuelle'], 2, ',', ' ')) : '—' ?> kWh
                </dd>
            </dl>
        </div>
    </div>

    <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-brand">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <?= $isCreate ? 'Créer l\'équipement' : 'Enregistrer' ?>
        </button>
        <a href="<?= Security::e(Url::to('equipements')) ?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
