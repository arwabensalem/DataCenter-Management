<?php

declare(strict_types=1);

use App\Helpers\Security;

$app = require dirname(__DIR__, 2) . '/config/app.php';
$baseUrl = rtrim((string) $app['url'], '/');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable | GreenDC Advisor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= Security::e($baseUrl) ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="guest-body">
    <div class="container guest-main text-center">
        <div class="login-card d-inline-block p-5">
            <h1 class="display-5 text-brand">404</h1>
            <p class="mb-4">La page demandée n'existe pas.</p>
            <a href="<?= Security::e($baseUrl) ?>/login" class="btn btn-brand">Retour à la connexion</a>
        </div>
    </div>
</body>
</html>
