<?php
session_start();
require_once '../Controllers/CartController.php';

// Проверка авторизации
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

$userId = $_SESSION['user_id'];
$cart = new CartController();

$errors = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? null;

    // Валидация
    if (empty($productId)) {
        $errors[] = 'Не указан товар';
    } else {
        // Удаление товара из корзины
        $result = $cart->removeFromCart($userId, $productId);

        if (isset($result['success']) && $result['success']) {
            $success[] = $result['message'] ?? 'Товар успешно удален из корзины';
        } else {
            $errors[] = $result['message'] ?? 'Ошибка удаления товара из корзины';
        }
    }
} else {
    $errors[] = 'Некорректный метод запроса';
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

// Редирект обратно на корзину
header('Location: /cart');
exit();