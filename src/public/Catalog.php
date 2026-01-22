<?php

if (!function_exists('getDBConnection')) {
    function getDBConnection() {
        $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}

class Catalog
{
    private $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo ?? getDBConnection();
    }

    public function getAllProducts()
    {
        $stmt = $this->pdo->query("SELECT * FROM products");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}