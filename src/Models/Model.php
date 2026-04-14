<?php

namespace Models;

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
            self::$pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
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
