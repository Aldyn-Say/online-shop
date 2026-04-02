<?php

namespace Models;

use PDO;
use PDOException;
use RuntimeException;
use Service\Logger\LoggerService;

abstract class Model
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo !== null) {
            $this->pdo = $pdo;
            return;
        }
        try {
            $this->pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
        } catch (PDOException $e) {
            (new LoggerService())->error('DB connection failed: ' . $e->getMessage());
            throw new RuntimeException('Не удалось подключиться к базе данных', 0, $e);
        }
    }

    protected function logError(string $message): void
    {
        $logger = new LoggerService();
        $logger->error($message);
        $logger->errorToDb($message, 'error');
    }

    abstract protected function getTableName(): string;
}
