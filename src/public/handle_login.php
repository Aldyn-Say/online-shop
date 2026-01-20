<?php
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';


    if (empty($email)) {
        $errors['email'] = 'Введите email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email';
    }

    if (empty($password)) {
        $errors['password'] = 'Введите пароль';
    }


    if (empty($errors)) {
        $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Поиск пользователя
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'] ?? $user['email'];
            $_SESSION['logged_in'] = true;

            // Редирект
            header('Location: /catalog.php');
            exit();
        } else {
            // Неверный пароль
            $errors['general'] = 'Неверный email или пароль';
        }


    }
}
require_once './login.php';