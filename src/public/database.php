<?php
/**
 * Функция для получения подключения к базе данных
 * Используется классами Cart, User, Catalog
 */
function getDBConnection() {
    $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}
