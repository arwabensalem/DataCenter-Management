<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;

$app = require dirname(__DIR__, 2) . '/config/app.php';
$baseUrl = Url::base();
$pageTitle = isset($title) ? $title . ' | ' . $app['name'] : $app['name'];
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
    <link href="<?= Security::e($baseUrl) ?>/assets/css/print.css" rel="stylesheet">
</head>
<body class="print-body">
    <div class="print-toolbar no-print">
        <div class="container d-flex justify-content-between align-items-center py-2">
            <a href="<?= Security::e(Url::to('rapports')) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Retour
            </a>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-brand btn-sm" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i> Imprimer / PDF
                </button>
            </div>
        </div>
    </div>

    <main class="print-document container py-4">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="<?= Security::e($baseUrl) ?>/assets/js/charts.js"></script>
</body>
</html>
