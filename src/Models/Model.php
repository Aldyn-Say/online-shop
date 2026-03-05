<?php

namespace Models;

use PDO;

abstract class Model
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = $pdo ?? new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
    }

    abstract protected function getTableName(): string;
}