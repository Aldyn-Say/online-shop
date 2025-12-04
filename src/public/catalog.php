<?php
//$user_id = $_SESSION['user_id'];

if (!isset($_COOKIE["user_id"])) {
    header('Location: login.php');
}

$pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();
//print_r($products);

require_once './catalog_page.php';
