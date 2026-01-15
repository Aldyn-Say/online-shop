<?php
session_start();
require_once 'User.php';

// Проверка авторизации
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

$userObj = new User();
$user = $userObj->getUserById($_SESSION['user_id']);

// Получение сообщений об ошибках и успехе из сессии
$errors = $_SESSION['profile_errors'] ?? [];
$success = $_SESSION['profile_success'] ?? [];
unset($_SESSION['profile_errors']);
unset($_SESSION['profile_success']);

require_once 'profile_page.php';


