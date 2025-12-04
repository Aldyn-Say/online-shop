<?php
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Валидация данных
    if (empty($username)) {
        $errors['username'] = 'Введите email';
    } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $errors['username'] = 'Введите корректный email';
    }

    if (empty($password)) {
        $errors['password'] = 'Введите пароль';
    }

    if (empty($errors)) {
        try {
            $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");

            // Поиск пользователя
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $username]);
            $user = $stmt->fetch();

            if ($user) {
                if (password_verify($password, $user['password'])) {
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
            } else {
                // Пользователь не найден
                $errors['general'] = 'Неверный email или пароль';
            }

        } catch (PDOException $e) {
            // Логирование ошибки (в продакшене не показывать пользователю)
            error_log("Database error: " . $e->getMessage());
            $errors['general'] = 'Ошибка сервера. Попробуйте позже.';
        }
    }
}
require_once './login.php';