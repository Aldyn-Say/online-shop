<?php
require_once '../Controllers/UserController.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new UserController();
    $result = $user->register($_POST);

    if ($result['success']) {
        header('Location: /login');
        exit();
    } else {
        $errors = $result['errors'];
    }
}
require_once '../Views/registration_form.php';
?>