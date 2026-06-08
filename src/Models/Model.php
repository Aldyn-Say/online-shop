<?php
declare(strict_types=1);

namespace Models;

use Core\Config\Env;
use PDO;
use PDOException;
use RuntimeException;
use Service\Logger\LoggerService;

abstract class Model
{
    protected static ?PDO $pdo = null;

    public static function getPDO(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        try {
            $host = Env::get('DB_HOST', 'db');
            $port = Env::get('DB_PORT', '5432');
            $dbName = Env::get('DB_NAME');
            $user = Env::get('DB_USER');
            $password = Env::get('DB_PASSWORD');

            if ($dbName === null || $user === null || $password === null) {
                throw new RuntimeException('Database credentials are not configured in .env');
            }

            self::$pdo = new PDO(
                "pgsql:host={$host};port={$port};dbname={$dbName}",
                $user,
                $password
            );
            return self::$pdo;
        } catch (PDOException $e) {
            (new LoggerService())->error('DB connection failed: ' . $e->getMessage());
            throw new RuntimeException('Не удалось подключиться к базе данных', 0, $e);
        }
    }

    protected static function logError(string $message): void
    {
        $logger = new LoggerService();
        $logger->error($message);
        $logger->errorToDb($message, 'error');
    }

    abstract protected static function getTableName(): string;
}
