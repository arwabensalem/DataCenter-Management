<?php

declare(strict_types=1);

use App\Helpers\Security;

$app = require dirname(__DIR__, 2) . '/config/app.php';
$baseUrl = rtrim((string) $app['url'], '/');
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
</head>
<body class="guest-body">
    <div class="guest-overlay"></div>
    <main class="container guest-main">
        <?= $content ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
