<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Url;

$isCreate = ($mode ?? '') === 'create';
$val = static function (string $key, ?array $entreprise, array $old) {
    if (array_key_exists($key, $old) && $old[$key] !== '') {
        return (string) $old[$key];
    }
    if ($entreprise !== null && isset($entreprise[$key])) {
        return (string) $entreprise[$key];
    }
    return '';
};

$old = $old ?? [];
$errors = $errors ?? [];
$action = $isCreate
    ? Url::to('entreprises/store')
    : Url::to('entreprises/' . $entreprise['id'] . '/update');
?>
<div class="mb-3">
    <a href="<?= Security::e($isCreate ? Url::to('entreprises') : Url::to('entreprises/' . $entreprise['id'])) ?>"
       class="text-decoration-none text-muted">
        <i class="fa-solid fa-arrow-left me-1"></i> Retour
    </a>
</div>

<form method="post" action="<?= Security::e($action) ?>" class="row g-4" novalidate>
    <?= Security::csrfField() ?>

    <div class="col-lg-<?= $isCreate ? '6' : '8' ?>">
        <div class="form-card h-100">
            <h2 class="h6 text-uppercase text-muted mb-3">
                <i class="fa-solid fa-building me-1"></i> Informations entreprise
            </h2>

            <div class="mb-3">
                <label class="form-label" for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
                       value="<?= Security::e($val('nom', $entreprise, $old)) ?>" required>
                <?php if (isset($errors['nom'])): ?>
                    <div class="invalid-feedback"><?= Security::e($errors['nom']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="adresse">Adresse *</label>
                <input type="text" id="adresse" name="adresse" class="form-control <?= isset($errors['adresse']) ? 'is-invalid' : '' ?>"
                       value="<?= Security::e($val('adresse', $entreprise, $old)) ?>" required>
                <?php if (isset($errors['adresse'])): ?>
                    <div class="invalid-feedback"><?= Security::e($errors['adresse']) ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="ville">Ville *</label>
                    <input type="text" id="ville" name="ville" class="form-control <?= isset($errors['ville']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('ville', $entreprise, $old)) ?>" required>
                    <?php if (isset($errors['ville'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['ville']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="telephone">Téléphone *</label>
                    <input type="text" id="telephone" name="telephone" class="form-control <?= isset($errors['telephone']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('telephone', $entreprise, $old)) ?>" required>
                    <?php if (isset($errors['telephone'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['telephone']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label" for="email">E-mail entreprise *</label>
                <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       value="<?= Security::e($val('email', $entreprise, $old)) ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?= Security::e($errors['email']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($isCreate): ?>
    <div class="col-lg-6">
        <div class="form-card h-100">
            <h2 class="h6 text-uppercase text-muted mb-3">
                <i class="fa-solid fa-user me-1"></i> Compte client (connexion)
            </h2>
            <p class="small text-muted">Un compte <strong>Client</strong> sera créé et lié à cette entreprise.</p>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="client_prenom">Prénom *</label>
                    <input type="text" id="client_prenom" name="client_prenom"
                           class="form-control <?= isset($errors['client_prenom']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('client_prenom', null, $old)) ?>" required>
                    <?php if (isset($errors['client_prenom'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['client_prenom']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="client_nom">Nom *</label>
                    <input type="text" id="client_nom" name="client_nom"
                           class="form-control <?= isset($errors['client_nom']) ? 'is-invalid' : '' ?>"
                           value="<?= Security::e($val('client_nom', null, $old)) ?>" required>
                    <?php if (isset($errors['client_nom'])): ?>
                        <div class="invalid-feedback"><?= Security::e($errors['client_nom']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="client_email">E-mail de connexion *</label>
                <input type="email" id="client_email" name="client_email"
                       class="form-control <?= isset($errors['client_email']) ? 'is-invalid' : '' ?>"
                       value="<?= Security::e($val('client_email', null, $old)) ?>" required>
                <?php if (isset($errors['client_email'])): ?>
                    <div class="invalid-feedback"><?= Security::e($errors['client_email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="client_telephone">Téléphone contact</label>
                <input type="text" id="client_telephone" name="client_telephone" class="form-control"
                       value="<?= Security::e($val('client_telephone', null, $old)) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label" for="client_password">Mot de passe *</label>
                <input type="password" id="client_password" name="client_password"
                       class="form-control <?= isset($errors['client_password']) ? 'is-invalid' : '' ?>"
                       minlength="8" required>
                <?php if (isset($errors['client_password'])): ?>
                    <div class="invalid-feedback"><?= Security::e($errors['client_password']) ?></div>
                <?php else: ?>
                    <div class="form-text">Minimum 8 caractères.</div>
                <?php endif; ?>
            </div>

            <div class="mb-0">
                <label class="form-label" for="client_password_confirm">Confirmation *</label>
                <input type="password" id="client_password_confirm" name="client_password_confirm"
                       class="form-control <?= isset($errors['client_password_confirm']) ? 'is-invalid' : '' ?>"
                       minlength="8" required>
                <?php if (isset($errors['client_password_confirm'])): ?>
                    <div class="invalid-feedback"><?= Security::e($errors['client_password_confirm']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-brand">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <?= $isCreate ? 'Créer l\'entreprise' : 'Enregistrer' ?>
        </button>
        <a href="<?= Security::e(Auth::isAdmin() ? Url::to('entreprises') : Url::to('dashboard')) ?>"
           class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
