<?php
namespace Controllers;

use Models\Cart;
use Models\Model;

class CartController extends Model
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Cart($this->pdo);
    }

    public function addToCart($userId, $productId, $quantity = 1): array
    {
        return $this->model->addToCart($userId, $productId, $quantity);
    }

    public function getCartItems($userId): array
    {
        return $this->model->getCartItems($userId);
    }

    public function updateCartItemQuantity($userId, $productId, $quantity): array
    {
        return $this->model->updateCartItemQuantity($userId, $productId, $quantity);
    }

    public function removeFromCart($userId, $productId) {
        return $this->model->removeFromCart($userId, $productId);
    }

    public function clearCart($userId) {
        return $this->model->clearCart($userId);
    }

    public function getCartTotal($userId) {
        return $this->model->getCartTotal($userId);
    }

    public function getCartItemsCount($userId) {
        return $this->model->getCartItemsCount($userId);
    }

    public function getCartUniqueItemsCount($userId) {
        return $this->model->getCartUniqueItemsCount($userId);
    }


    public function showCart() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return ['redirect' => '/login'];
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            return ['redirect' => '/login'];
        }

        $cartItems = $this->getCartItems($userId);
        $cartTotal = $this->getCartTotal($userId);
        $cartMessage = $_SESSION['cart_message'] ?? null;
        unset($_SESSION['cart_message']);

        require_once __DIR__ . '/../Views/cart.php';
    }

    public function handleAddToCart() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['redirect' => '/catalog'];
        }

        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) { //проверка авторизации
            $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Необходимо войти в систему для добавления товаров в корзину'];
            return ['redirect' => '/login'];
        }

        $userId = $_SESSION['user_id'] ?? null; //инициализвация
        if (!$userId) {
            $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Ошибка авторизации'];
            return ['redirect' => '/login'];
        }

        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

        $errors = [];

        // Валидация
        if ($productId <= 0) {
            $errors[] = 'Не указан корректный товар';
        }

        if ($quantity <= 0) {
            $errors[] = 'Количество должно быть больше нуля';
        }

        // Если ошибок нет - добавляем в корзину
        if (empty($errors)) {
            $result = $this->addToCart($userId, $productId, $quantity);

            if ($result['success']) {
                $_SESSION['cart_message'] = [
                    'type' => 'success',
                    'text' => $result['message']
                ];
            } else {
                // Логирование ошибки
                error_log("Error adding to cart: " . $result['message'] . " | User ID: " . $userId . " | Product ID: " . $productId);

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

        // Редирект
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/catalog';
        // Безопасный редирект
        if (filter_var($redirectUrl, FILTER_VALIDATE_URL) === false) {
            $redirectUrl = '/catalog';
        }

        return ['redirect' => $redirectUrl];
    }

    public function handleUpdateCart() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['redirect' => '/cart'];
        }

        if (!isset($_SESSION)) {
            session_start();
        }

        // Проверка авторизации
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return ['redirect' => '/login'];
        }

        $userId = $_SESSION['user_id'];
        $errors = [];
        $success = [];

        $productId = $_POST['product_id'] ?? null;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

        // Валидация
        if (empty($productId)) {
            $errors[] = 'Не указан товар';
        } elseif ($quantity < 1) {
            $errors[] = 'Количество должно быть больше нуля';
        } else {
            // Обновление количества товара
            $result = $this->updateCartItemQuantity($userId, $productId, $quantity);

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
        return ['redirect' => '/cart'];
    }

    public function handleRemoveFromCart() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['redirect' => '/cart'];
        }

        if (!isset($_SESSION)) {
            session_start();
        }

        // Проверка авторизации
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return ['redirect' => '/login'];
        }

        $userId = $_SESSION['user_id'];
        $errors = [];
        $success = [];

        $productId = $_POST['product_id'] ?? null;

        // Валидация
        if (empty($productId)) {
            $errors[] = 'Не указан товар';
        } else {
            // Удаление товара из корзины
            $result = $this->removeFromCart($userId, $productId);

            if (isset($result['success']) && $result['success']) {
                $success[] = $result['message'] ?? 'Товар успешно удален из корзины';
            } else {
                $errors[] = $result['message'] ?? 'Ошибка удаления товара из корзины';
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

        // Редирект обратно на корзину
        return ['redirect' => '/cart'];
    }
}