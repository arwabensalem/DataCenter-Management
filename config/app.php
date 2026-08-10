<?php

declare(strict_types=1);

/**
 * Paramètres globaux de l'application GreenDC Advisor.
 */
return [
    'name'      => 'GreenDC Advisor',
    'env'       => 'local',
    'debug'     => true,
    'url'       => '/Cert',
    'timezone'  => 'Africa/Tunis',
    'locale'    => 'fr_FR',

    // Facteur d'émission CO₂ du réseau électrique (kg / kWh) — Tunisie (indicatif)
    'co2_kg_per_kwh' => 0.55,

    // Durée de session (secondes)
    'session_lifetime' => 7200,
];
