<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Url;

$isCreate = ($mode ?? '') === 'create';
$old = $old ?? [];
$errors = $errors ?? [];

$val = static function (string $key, ?array $dataCenter, array $old): string {
    if (array_key_exists($key, $old) && $old[$key] !== '') {
        return (string) $old[$key];
    }
    if ($dataCenter !== null && isset($dataCenter[$key])) {
        return (string) $dataCenter[$key];
    }
    return '';
};

$action = $isCreate
    ? Url::to('data-centers/store')
    : Url::to('data-centers/' . $dataCenter['id'] . '/update');

$defaultEntrepriseId = '';
if (!$isCreate && $dataCenter !== null) {
    $defaultEntrepriseId = (string) $dataCenter['entreprise_id'];
} elseif (Auth::isClient() && count($entreprises) === 1) {
    $defaultEntrepriseId = (string) $entreprises[0]['id'];
}
$entrepriseId = $val('entreprise_id', null, $old) !== ''
    ? $val('entreprise_id', null, $old)
    : $defaultEntrepriseId;
?>
<div class="mb-3">
    <a href="<?= Security::e($isCreate ? Url::to('data-centers') : Url::to('data-centers/' . $dataCenter['id'])) ?>"
       class="text-decoration-none text-muted">
        <i class="fa-solid fa-arrow-left me-1"></i> Retour
    </a>
</div>

<form method="post" action="<?= Security::e($action) ?>" class="row g-4" novalidate>
    <?= Security::csrfField() ?>

    <div class="col-lg-8">
        <div class="form-card">
            <h2 class="h6 text-uppercase text-muted mb-3">
                <i class="fa-solid fa-server me-1"></i> Caractéristiques techniques
            </h2>

            <div class="mb-3">
                <label class="form-label" for="entreprise_id">Entreprise *</label>
                <?php if (Auth::isClient()): ?>
                    <input type="hidden" name="entreprise_id" value="<?= Security::e($entrepriseId) ?>">
                    <input type="text" class="form-control" value="<?= Security::e($entreprises[0]['nom'] ?? '') ?>" disabled>
                <?php else: ?>
                    <select id="entreprise_id" name="entreprise_id"
                            class="form-select <?= isset($errors['entreprise_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($entreprises as $ent): ?>
                            <option value="<?= (int) $ent['id'] ?>"
                                <?= (string) $ent['id'] === $entrepriseId ? 'selected' : '' ?>>
                                <?= Security::e($ent['nom']) ?> (<?= Security::e($ent['ville']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['entreprise_id'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['entreprise_id']) ?></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="nom">Nom du Data Center *</label>
                <input type="text" id="nom" name="nom"
                       class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
                       value="<?= Security::e($val('nom', $dataCenter, $old)) ?>" required>
                <?php if (isset($errors['nom'])): ?>
                    <div class="invalid-feedback"><?= Security::e($errors['nom']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="localisation">Localisation *</label>
                <input type="text" id="localisation" name="localisation"
                       class="form-control <?= isset($errors['localisation']) ? 'is-invalid' : '' ?>"
                       value="<?= Security::e($val('localisation', $dataCenter, $old)) ?>"
                       placeholder="Adresse / site" required>
                <?php if (isset($errors['localisation'])): ?>
                    <div class="invalid-feedback"><?= Security::e($errors['localisation']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="gouvernorat_id">Gouvernorat (carte énergétique) *</label>
                <?php
                $gouvernorats = $gouvernorats ?? [];
                $govId = $val('gouvernorat_id', $dataCenter, $old);
                if ($govId === '' && isset($_GET['gouvernorat_id']) && ctype_digit((string) $_GET['gouvernorat_id'])) {
                    $govId = (string) $_GET['gouvernorat_id'];
                }
                ?>
                <select id="gouvernorat_id" name="gouvernorat_id"
                        class="form-select <?= isset($errors['gouvernorat_id']) ? 'is-invalid' : '' ?>" required>
                    <option value="">— Sélectionner sur la carte —</option>
                    <?php foreach ($gouvernorats as $g): ?>
                        <option value="<?= (int) $g['id'] ?>"
                            <?= (string) $g['id'] === $govId ? 'selected' : '' ?>
                            data-heures="<?= Security::e((string) $g['heures_ensoleillement']) ?>"
                            data-irradiation="<?= Security::e((string) $g['irradiation_kwh_m2_an']) ?>">
                            <?= Security::e($g['nom']) ?>
                            (<?= Security::e(number_format((float) $g['heures_ensoleillement'], 1, ',', '')) ?> h/j
                            · <?= Security::e(number_format((float) $g['irradiation_kwh_m2_an'], 0, ',', ' ')) ?> kWh/m²)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['gouvernorat_id'])): ?>
                    <div class="invalid-feedback"><?= Security::e($errors['gouvernorat_id']) ?></div>
                <?php else: ?>
                    <div class="form-text">
                        Les heures d'ensoleillement seront appliquées automatiquement au dimensionnement PV.
                        <a href="<?= Security::e(Url::to('carte')) ?>">Voir la carte</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="surface_totale">Surface totale (m²) *</label>
                    <input type="text" id="surface_totale" name="surface_totale"
                           class="form-control <?= isset($errors['surface_totale']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('surface_totale', $dataCenter, $old)) ?>" required>
                    <?php if (isset($errors['surface_totale'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['surface_totale']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="surface_disponible_pv">Surface disponible PV (m²) *</label>
                    <input type="text" id="surface_disponible_pv" name="surface_disponible_pv"
                           class="form-control <?= isset($errors['surface_disponible_pv']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('surface_disponible_pv', $dataCenter, $old)) ?>" required>
                    <?php if (isset($errors['surface_disponible_pv'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['surface_disponible_pv']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="prix_kwh_steg">Prix du kWh STEG (TND) *</label>
                    <input type="text" id="prix_kwh_steg" name="prix_kwh_steg"
                           class="form-control <?= isset($errors['prix_kwh_steg']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('prix_kwh_steg', $dataCenter, $old) ?: '0.2500') ?>" required>
                    <?php if (isset($errors['prix_kwh_steg'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['prix_kwh_steg']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="heures_fonctionnement">Heures de fonctionnement / jour *</label>
                    <input type="text" id="heures_fonctionnement" name="heures_fonctionnement"
                           class="form-control <?= isset($errors['heures_fonctionnement']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('heures_fonctionnement', $dataCenter, $old) ?: '24') ?>" required>
                    <?php if (isset($errors['heures_fonctionnement'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['heures_fonctionnement']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-card h-100">
            <h2 class="h6 text-uppercase text-muted mb-3">Aide</h2>
            <ul class="small text-muted mb-0 ps-3">
                <li class="mb-2">La surface PV doit être ≤ à la surface totale.</li>
                <li class="mb-2">Le prix kWh STEG sert au calcul des coûts et du ROI.</li>
                <li class="mb-2">Les Data Centers tournent souvent 24 h/24.</li>
                <li>Les équipements seront ajoutés dans le module suivant.</li>
            </ul>
        </div>
    </div>

    <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-brand">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <?= $isCreate ? 'Créer le Data Center' : 'Enregistrer' ?>
        </button>
        <a href="<?= Security::e(Url::to('data-centers')) ?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
