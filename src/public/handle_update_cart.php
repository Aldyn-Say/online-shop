<?php
session_start();
require_once 'cart_functions.php';

// Проверка авторизации
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

$userId = $_SESSION['user_id'];
$errors = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? null;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Валидация
    if (empty($productId)) {
        $errors[] = 'Не указан товар';
    } elseif ($quantity < 0) {
        $errors[] = 'Количество не может быть отрицательным';
    } else {
        // Обновление количества товара
        $result = updateCartItemQuantity($userId, $productId, $quantity);
        
        if ($result['success']) {
            $success[] = $result['message'];
        } else {
            $errors[] = $result['message'];
        }
    }
    
    // Сохранение сообщений в сессию
    if (!empty($success)) {
        $_SESSION['cart_message'] = [
            'type' => 'success',
            'text' => implode(', ', $success)
        ];
    } elseif (!empty($errors)) {
        $_SESSION['cart_message'] = [
            'type' => 'error',
            'text' => implode(', ', $errors)
        ];
    }
}

// Редирект обратно на корзину
header('Location: /cart');
exit();

