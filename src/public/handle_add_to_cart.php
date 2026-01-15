<?php
session_start();
require_once 'Cart.php';

// Проверка авторизации
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Необходимо войти в систему для добавления товаров в корзину'];
    header('Location: /login');
    exit();
}

$userId = $_SESSION['user_id'];
$cart = new Cart();
$errors = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Валидация
    if (empty($productId) || $productId <= 0) {
        $errors[] = 'Не указан товар';
    } elseif ($quantity <= 0) {
        $errors[] = 'Количество должно быть больше нуля';
    } else {
        // Добавление товара в корзину
        $result = $cart->addToCart($userId, $productId, $quantity);
        
        if ($result['success']) {
            $success[] = $result['message'];
            // Сохраняем информацию о действии для отображения
            $_SESSION['cart_message'] = [
                'type' => 'success',
                'text' => $result['message']
            ];
        } else {
            $errors[] = $result['message'];
            // Сохраняем ошибку для отладки
            error_log("Error adding to cart: " . $result['message'] . " | User ID: " . $userId . " | Product ID: " . $productId);
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['cart_message'] = [
            'type' => 'error',
            'text' => implode(', ', $errors)
        ];
    }
}

// Редирект обратно на каталог или на страницу, откуда пришли
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/catalog';
header('Location: ' . $redirectUrl);
exit();
