<?php
session_start();
require_once '../Controllers/CartController.php';

// Проверка авторизации ДО всего остального
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Необходимо войти в систему для добавления товаров в корзину'];
    header('Location: /login');
    exit();
}

// Инициализация после проверки авторизации
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Ошибка авторизации'];
    header('Location: /login');
    exit();
}

$cart = new CartController();

// Обработка только POST-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    $errors = [];

    // Валидация (исправленная)
    if ($productId <= 0) {
        $errors[] = 'Не указан корректный товар';
    }

    if ($quantity <= 0) {
        $errors[] = 'Количество должно быть больше нуля';
    }

    // Если ошибок нет - добавляем в корзину
    if (empty($errors)) {
        $result = $cart->addToCart($userId, $productId, $quantity);

        if ($result['success']) {
            $_SESSION['cart_message'] = [
                'type' => 'success',
                'text' => $result['message']
            ];
        } else {
            // Логирование ошибки
            error_log("Error adding to cart: " . $result['message'] . " | UserController ID: " . $userId . " | Product ID: " . $productId);

            $_SESSION['cart_message'] = [
                'type' => 'error',
                'text' => $result['message'] ?? 'Ошибка добавления товара в корзину'
            ];
        }
    } else {
        // Сохраняем ошибки валидации
        $_SESSION['cart_message'] = [
            'type' => 'error',
            'text' => implode(', ', $errors)
        ];
    }
}

// Редирект (вне блока POST, но после всей обработки)
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/catalog';
// Безопасный редирект
if (filter_var($redirectUrl, FILTER_VALIDATE_URL) === false) {
    $redirectUrl = '/catalog';
}

header('Location: ' . $redirectUrl);
exit();