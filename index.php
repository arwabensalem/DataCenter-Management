<?php

declare(strict_types=1);

/**
 * GreenDC Advisor — Front Controller
 */

require_once __DIR__ . '/config/bootstrap.php';

use App\Config\Router;

$app = require __DIR__ . '/config/app.php';

$router = new Router($app['url']);
$router->dispatch();
