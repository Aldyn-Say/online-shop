<?php
session_start();

$pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();
//print_r($products);

require_once '../Views/catalog.php';
