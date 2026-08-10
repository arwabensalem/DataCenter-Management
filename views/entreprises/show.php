<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Url;
?>
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <?php if (Auth::isAdmin()): ?>
            <a href="<?= Security::e(Url::to('entreprises')) ?>" class="text-decoration-none text-muted small">
                <i class="fa-solid fa-arrow-left me-1"></i> Liste des entreprises
            </a>
        <?php endif; ?>
        <h2 class="h4 mb-0 mt-1"><?= Security::e($entreprise['nom']) ?></h2>
        <p class="text-muted mb-0"><?= Security::e($entreprise['ville']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= Security::e(Url::to('entreprises/' . $entreprise['id'] . '/edit')) ?>" class="btn btn-brand">
            <i class="fa-solid fa-pen me-1"></i> Modifier
        </a>
        <?php if (Auth::isAdmin()): ?>
            <form method="post"
                  action="<?= Security::e(Url::to('entreprises/' . $entreprise['id'] . '/delete')) ?>"
                  onsubmit="return confirm('Supprimer définitivement cette entreprise ?');">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fa-solid fa-trash me-1"></i> Supprimer
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Coordonnées</h3>
            <dl class="row mb-0 detail-list">
                <dt class="col-sm-4">Adresse</dt>
                <dd class="col-sm-8"><?= Security::e($entreprise['adresse']) ?></dd>
                <dt class="col-sm-4">Ville</dt>
                <dd class="col-sm-8"><?= Security::e($entreprise['ville']) ?></dd>
                <dt class="col-sm-4">Téléphone</dt>
                <dd class="col-sm-8"><?= Security::e($entreprise['telephone']) ?></dd>
                <dt class="col-sm-4">E-mail</dt>
                <dd class="col-sm-8"><?= Security::e($entreprise['email']) ?></dd>
                <dt class="col-sm-4">Créée le</dt>
                <dd class="col-sm-8"><?= Security::e(date('d/m/Y H:i', strtotime($entreprise['date_creation']))) ?></dd>
            </dl>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-card h-100">
            <h3 class="h6 text-uppercase text-muted mb-3">Compte client lié</h3>
            <dl class="row mb-0 detail-list">
                <dt class="col-sm-4">Contact</dt>
                <dd class="col-sm-8">
                    <?= Security::e($entreprise['client_prenom'] . ' ' . $entreprise['client_nom']) ?>
                </dd>
                <dt class="col-sm-4">E-mail login</dt>
                <dd class="col-sm-8"><?= Security::e($entreprise['client_email']) ?></dd>
                <dt class="col-sm-4">Téléphone</dt>
                <dd class="col-sm-8"><?= Security::e($entreprise['client_telephone'] ?: '—') ?></dd>
                <dt class="col-sm-4">Statut</dt>
                <dd class="col-sm-8">
                    <?php if ($entreprise['client_statut'] === 'actif'): ?>
                        <span class="badge bg-soft-green">Actif</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactif</span>
                    <?php endif; ?>
                </dd>
            </dl>
        </div>
    </div>

    <div class="col-12">
        <div class="form-card d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="h6 mb-1">Data Centers</h3>
                <p class="small text-muted mb-0">Gérer les infrastructures de cette entreprise.</p>
            </div>
            <a href="<?= Security::e(Url::to('data-centers')) ?>" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-server me-1"></i> Voir les Data Centers
            </a>
        </div>
    </div>
</div>
