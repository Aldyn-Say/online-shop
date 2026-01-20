<?php
session_start();
require_once 'user_functions.php';

// Проверка авторизации
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$errors = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Обновление имени
    if (isset($_POST['update_name']) && !empty($_POST['name'])) {
        $name = trim($_POST['name']);
        $nameErrors = validateName($name);
        
        if (empty($nameErrors)) {
            $result = updateUserName($userId, $name);
            if ($result['success']) {
                $success['name'] = $result['message'];
                $_SESSION['user_name'] = $name;
            } else {
                $errors['name'] = $result['message'];
            }
        } else {
            $errors['name'] = implode(', ', $nameErrors);
        }
    }
    
    // Обновление email
    if (isset($_POST['update_email']) && !empty($_POST['email'])) {
        $email = trim($_POST['email']);
        $emailErrors = validateEmail($email, $userId);
        
        if (empty($emailErrors)) {
            $result = updateUserEmail($userId, $email);
            if ($result['success']) {
                $success['email'] = $result['message'];
                $_SESSION['user_email'] = $email;
            } else {
                $errors['email'] = $result['message'];
            }
        } else {
            $errors['email'] = implode(', ', $emailErrors);
        }
    }
    
    // Обновление пароля
    if (isset($_POST['update_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Проверка текущего пароля
        if (!verifyCurrentPassword($userId, $currentPassword)) {
            $errors['password'] = 'Неверный текущий пароль';
        } else {
            $passwordErrors = validatePassword($newPassword, $confirmPassword);
            
            if (empty($passwordErrors)) {
                $result = updateUserPassword($userId, $newPassword);
                if ($result['success']) {
                    $success['password'] = $result['message'];
                } else {
                    $errors['password'] = $result['message'];
                }
            } else {
                $errors['password'] = implode(', ', $passwordErrors);
            }
        }
    }
}

// Перенаправление обратно на страницу профиля с сообщениями
$_SESSION['profile_errors'] = $errors;
$_SESSION['profile_success'] = $success;
header('Location: profile.php');
exit();

