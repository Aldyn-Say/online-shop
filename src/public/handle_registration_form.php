<?php
require_once 'User.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();
    $result = $user->register($_POST);
    
    if ($result['success']) {
        header('Location: /login');
        exit();
    } else {
        $errors = $result['errors'];
    }
}
require_once './registration_form.php';