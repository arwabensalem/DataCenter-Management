<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Url;

$app = require dirname(__DIR__, 2) . '/config/app.php';
$baseUrl = Url::base();
$pageTitle = isset($title) ? $title . ' | ' . $app['name'] : $app['name'];
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?= Security::e($baseUrl) ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="app-body">
<div class="d-flex" id="wrapper">
    <aside class="sidebar text-white">
        <div class="sidebar-brand px-3 py-4">
            <div class="brand-icon"><i class="fa-solid fa-solar-panel"></i></div>
            <div>
                <strong>GreenDC</strong>
                <small class="d-block opacity-75">Advisor</small>
            </div>
        </div>
        <nav class="nav flex-column px-2">
            <a class="nav-link <?= Url::is('dashboard') ? 'active' : '' ?>" href="<?= Security::e(Url::to('dashboard')) ?>">
                <i class="fa-solid fa-gauge-high me-2"></i> Tableau de bord
            </a>
            <a class="nav-link <?= Url::is('entreprises') ? 'active' : '' ?>" href="<?= Security::e(Url::to('entreprises')) ?>">
                <i class="fa-solid fa-building me-2"></i>
                <?= Auth::isAdmin() ? 'Entreprises' : 'Mon entreprise' ?>
            </a>
            <a class="nav-link <?= Url::is('data-centers') ? 'active' : '' ?>" href="<?= Security::e(Url::to('data-centers')) ?>">
                <i class="fa-solid fa-server me-2"></i> Data Centers
            </a>
            <a class="nav-link <?= Url::is('equipements') ? 'active' : '' ?>" href="<?= Security::e(Url::to('equipements')) ?>">
                <i class="fa-solid fa-microchip me-2"></i> Équipements
            </a>
            <a class="nav-link <?= Url::is('photovoltaique') ? 'active' : '' ?>" href="<?= Security::e(Url::to('photovoltaique')) ?>">
                <i class="fa-solid fa-sun me-2"></i> Photovoltaïque
            </a>
            <a class="nav-link <?= Url::is('simulations') ? 'active' : '' ?>" href="<?= Security::e(Url::to('simulations')) ?>">
                <i class="fa-solid fa-flask me-2"></i> Simulations
            </a>
            <a class="nav-link <?= Url::is('recommandations') ? 'active' : '' ?>" href="<?= Security::e(Url::to('recommandations')) ?>">
                <i class="fa-solid fa-lightbulb me-2"></i> Recommandations
            </a>
            <a class="nav-link <?= Url::is('rapports') ? 'active' : '' ?>" href="<?= Security::e(Url::to('rapports')) ?>">
                <i class="fa-solid fa-file-lines me-2"></i> Rapports
            </a>
            <span class="nav-section">Avancé</span>
            <a class="nav-link <?= Url::is('carte') ? 'active' : '' ?>" href="<?= Security::e(Url::to('carte')) ?>">
                <i class="fa-solid fa-map-location-dot me-2"></i> Carte énergétique
            </a>
            <a class="nav-link <?= Url::is('scenarios') ? 'active' : '' ?>" href="<?= Security::e(Url::to('scenarios')) ?>">
                <i class="fa-solid fa-code-compare me-2"></i> Scénarios
            </a>
            <a class="nav-link <?= Url::is('decision') ? 'active' : '' ?>" href="<?= Security::e(Url::to('decision')) ?>">
                <i class="fa-solid fa-brain me-2"></i> Aide à la décision
            </a>
            <a class="nav-link <?= Url::is('exports') ? 'active' : '' ?>" href="<?= Security::e(Url::to('exports')) ?>">
                <i class="fa-solid fa-file-export me-2"></i> Exports
            </a>
        </nav>
        <div class="sidebar-footer mt-auto px-3 py-3">
            <a class="btn btn-outline-light btn-sm w-100" href="<?= Security::e(Url::to('logout')) ?>">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Déconnexion
            </a>
        </div>
    </aside>

    <div class="flex-grow-1 main-panel">
        <header class="topbar d-flex align-items-center justify-content-between px-4 py-3">
            <div>
                <h1 class="h5 mb-0"><?= Security::e($title ?? 'GreenDC Advisor') ?></h1>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge role-badge">
                    <?= Auth::isAdmin() ? 'Administrateur' : 'Client' ?>
                </span>
                <div class="user-chip">
                    <i class="fa-solid fa-circle-user me-1"></i>
                    <?= Security::e(Auth::fullName()) ?>
                </div>
            </div>
        </header>

        <main class="content-area p-4">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= Security::e($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= Security::e($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= Security::e($baseUrl) ?>/assets/js/app.js"></script>
<script src="<?= Security::e($baseUrl) ?>/assets/js/charts.js"></script>
</body>
</html>
