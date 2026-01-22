<?php
session_start();
require_once 'User.php';

// Проверка авторизации
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

$userId = $_SESSION['user_id'];
$user = new User();
$errors = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Обновление имени
    if (isset($_POST['update_name']) && !empty($_POST['name'])) {
        $name = trim($_POST['name']);
        $result = $user->updateName($userId, $name);

        if ($result['success']) {
            $success['name'] = $result['message'];
            $_SESSION['user_name'] = $name;
        } else {
            $errors['name'] = $result['message'];
        }
    }

    // Обновление email
    if (isset($_POST['update_email']) && !empty($_POST['email'])) {
        $email = trim($_POST['email']);
        $result = $user->updateEmail($userId, $email);

        if ($result['success']) {
            $success['email'] = $result['message'];
            $_SESSION['user_email'] = $email;
        } else {
            $errors['email'] = $result['message'];
        }
    }

    // Обновление пароля
    if (isset($_POST['update_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $result = $user->updatePassword($userId, $currentPassword, $newPassword, $confirmPassword);

        if ($result['success']) {
            $success['password'] = $result['message'];
        } else {
            $errors['password'] = $result['message'];
        }
    }
}

// Перенаправление обратно на страницу профиля с сообщениями
$_SESSION['profile_errors'] = $errors;
$_SESSION['profile_success'] = $success;
header('Location: /profile');
exit();
