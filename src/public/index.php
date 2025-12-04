<?php
$pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
$pdo->exec("INSERT INTO users (name, email, password) VALUES ('Ivan', 'ivan@gmail.com', 'password')");
$statement = $pdo->query("SELECT * FROM users");
$data = $statement->fetchAll();
print_r($data);