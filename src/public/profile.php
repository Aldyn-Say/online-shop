<?php
session_start();
require_once 'user_functions.php';

// Проверка авторизации
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Получение сообщений об ошибках и успехе из сессии
$errors = $_SESSION['profile_errors'] ?? [];
$success = $_SESSION['profile_success'] ?? [];
unset($_SESSION['profile_errors']);
unset($_SESSION['profile_success']);

require_once 'profile_page.php';


