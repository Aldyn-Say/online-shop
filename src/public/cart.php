<?php
session_start();
require_once 'Cart.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

$userId = $_SESSION['user_id'] ?? null;
// Проверка авторизации

// Проверка наличия user_id
if (empty($userId)) {
    error_log("Cart: user_id is empty in session");
    header('Location: /login');
    exit();
}

// Создаем объект корзины
$cart = new Cart();

// Получение товаров из корзины
$cartItems = $cart->getCartItems($userId);

// Логирование для отладки
error_log("Cart: User ID = " . $userId . ", Items count = " . count($cartItems));

// Подсчет общей стоимости
$cartTotal = $cart->getCartTotal($userId);

// Получение сообщений из сессии
$cartMessage = $_SESSION['cart_message'] ?? null;
unset($_SESSION['cart_message']);

require_once 'cart_page.php';
