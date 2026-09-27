<?php

declare(strict_types=1);

/**
 * Paramètres globaux de l'application GreenDC Advisor.
 * Surchargeables via variables d'environnement (déploiement).
 */
$env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'local');
$debug = getenv('APP_DEBUG');
if ($debug === false || $debug === '') {
    $debug = $_ENV['APP_DEBUG'] ?? ($env === 'local' ? '1' : '0');
}
$url = getenv('APP_URL');
if ($url === false || $url === '') {
    $url = $_ENV['APP_URL'] ?? '/Cert';
}

$co2 = getenv('CO2_KG_PER_KWH');
if ($co2 === false || $co2 === '') {
    $co2 = $_ENV['CO2_KG_PER_KWH'] ?? '0.55';
}

return [
    'name'      => 'GreenDC Advisor',
    'env'       => $env,
    'debug'     => filter_var($debug, FILTER_VALIDATE_BOOLEAN),
    'url'       => rtrim((string) $url, '/') ?: '',
    'timezone'  => 'Africa/Tunis',
    'locale'    => 'fr_FR',

    // Facteur d'émission CO₂ du réseau électrique (kg / kWh) — Tunisie (indicatif)
    'co2_kg_per_kwh' => (float) $co2,

    // Durée de session (secondes)
    'session_lifetime' => 7200,
];
