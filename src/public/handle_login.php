<?php
session_start();
require_once '../Controllers/UserController.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = new UserController();
    $result = $user->login($email, $password);

    if ($result['success']) {
        // Сохраняем данные пользователя в сессию
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['user_email'] = $result['user']['email'];
        $_SESSION['user_name'] = $result['user']['name'];
        $_SESSION['logged_in'] = true;

        // Редирект
        header('Location: /catalog');
        exit();
    } else {
        $errors = $result['errors'];
    }
}
require_once './login.php';