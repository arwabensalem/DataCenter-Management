<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Singleton PDO pour la connexion à la base de données.
 */
final class Database
{
    private static ?PDO $pdo = null;

    private function __construct()
    {
    }

    /**
     * Retourne l'instance PDO unique.
     */
    public static function getConnection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = require __DIR__ . '/db.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        try {
            self::$pdo = new PDO(
                $dsn,
                $config['user'],
                $config['pass'],
                $config['options']
            );
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Impossible de se connecter à la base de données : ' . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        return self::$pdo;
    }
}
