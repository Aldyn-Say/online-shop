<?php
session_start();
require_once '../Controllers/CartController.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

$userId = $_SESSION['user_id'] ?? null;
// Проверка авторизации

// Проверка наличия user_id
if (empty($userId)) {
    error_log("CartController: user_id is empty in session");
    header('Location: /login');
    exit();
}

// Создаем объект корзины
$cart = new CartController();

// Получение товаров из корзины
$cartItems = $cart->getCartItems($userId);

// Логирование для отладки
error_log("CartController: UserController ID = " . $userId . ", Items count = " . count($cartItems));

// Подсчет общей стоимости
$cartTotal = $cart->getCartTotal($userId);

// Получение сообщений из сессии
$cartMessage = $_SESSION['cart_message'] ?? null;
unset($_SESSION['cart_message']);

require_once '../Views/cart.php';
