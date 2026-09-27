<?php

declare(strict_types=1);

use App\Helpers\Lang;
use App\Helpers\Security;
use App\Helpers\Url;

$app = require dirname(__DIR__, 2) . '/config/app.php';
$baseUrl = rtrim((string) $app['url'], '/');
$pageTitle = isset($title) ? $title . ' | ' . $app['name'] : $app['name'];
$langSwitch = static function (string $code): string {
    return Url::to('lang/' . $code) . '?redirect=' . rawurlencode('login');
};
?>
<!DOCTYPE html>
<html lang="<?= Security::e(Lang::htmlLang()) ?>">
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
    <div class="guest-lang">
        <div class="lang-switch" role="group" aria-label="<?= Security::e(Lang::t('lang.label')) ?>">
            <a class="lang-btn <?= Lang::is('fr') ? 'active' : '' ?>" href="<?= Security::e($langSwitch('fr')) ?>">FR</a>
            <a class="lang-btn <?= Lang::is('en') ? 'active' : '' ?>" href="<?= Security::e($langSwitch('en')) ?>">EN</a>
        </div>
    </div>
    <main class="container guest-main">
        <?= $content ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
