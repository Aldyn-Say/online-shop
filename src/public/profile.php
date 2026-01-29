<?php
session_start();
require_once '../Controllers/UserController.php';

// Проверка авторизации
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

$userObj = new UserController();

// Получение сообщений об ошибках и успехе из сессии
$errors = $_SESSION['profile_errors'] ?? [];
$user = $userObj->getUserById($_SESSION['user_id']);
$success = $_SESSION['profile_success'] ?? [];
unset($_SESSION['profile_errors']);
unset($_SESSION['profile_success']);

require_once '../Views/profile.php';

