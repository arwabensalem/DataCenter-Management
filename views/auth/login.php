<?php

declare(strict_types=1);

use App\Helpers\Security;

$app = require dirname(__DIR__, 2) . '/config/app.php';
$baseUrl = rtrim((string) $app['url'], '/');
?>
<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="login-card animate-fade-up">
            <div class="text-center mb-4">
                <div class="login-logo mx-auto mb-3">
                    <i class="fa-solid fa-solar-panel"></i>
                </div>
                <h1 class="h4 fw-bold text-brand mb-1">GreenDC Advisor</h1>
                <p class="text-muted small mb-0">Aide à la décision énergétique pour Data Centers</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2"><?= Security::e($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success py-2"><?= Security::e($success) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= Security::e($baseUrl) ?>/login" autocomplete="off" novalidate>
                <?= Security::csrfField() ?>

                <div class="mb-3">
                    <label for="email" class="form-label">Adresse e-mail</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="admin@greendc.tn" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-brand w-100 py-2">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Se connecter
                </button>
            </form>

            <p class="text-center text-muted small mt-4 mb-0">
                Plateforme sécurisée — rôles Administrateur &amp; Client
            </p>
        </div>
    </div>
</div>
