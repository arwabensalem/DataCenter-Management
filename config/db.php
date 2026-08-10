<?php

declare(strict_types=1);

/**
 * Identifiants MySQL (XAMPP).
 * Fichier séparé de Database.php pour éviter le conflit de casse sous Windows.
 */
return [
    'host'    => '127.0.0.1',
    'port'    => '3306',
    'dbname'  => 'greendc_advisor',
    'charset' => 'utf8mb4',
    'user'    => 'root',
    'pass'    => '',
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
