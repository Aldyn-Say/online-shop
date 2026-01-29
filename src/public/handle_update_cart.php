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
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // Валидация
    if (empty($productId)) {
        $errors[] = 'Не указан товар';
    } elseif ($quantity < 1) { // Изменено с < 0 на < 1, так как количество не может быть 0 или отрицательным
        $errors[] = 'Количество должно быть больше нуля';
    } else {
        // Обновление количества товара
        $result = $cart->updateCartItemQuantity($userId, $productId, $quantity);

        if (isset($result['success']) && $result['success']) {
            $success[] = $result['message'] ?? 'Количество товара успешно обновлено';
        } else {
            $errors[] = $result['message'] ?? 'Ошибка обновления количества товара';
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