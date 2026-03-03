<?php

namespace Models;

use PDO;

class Model
{
    protected PDO $pdo;

    public function __construct(PDO $pdo = null)
    {
        $this->pdo = $pdo ?? new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
    }
}