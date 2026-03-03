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

    public function showCart()
    {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return ['redirect' => '/login'];
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            return ['redirect' => '/login'];
        }

        $this->model->loadByUserId($userId);
        $cartItems = $this->model->getItems();
        $cartTotal = $this->model->getTotal();
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

        if (empty($errors)) {
            $this->model->loadByUserId((int) $userId);
            $result = $this->model->addToCart($productId, $quantity);

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

        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/catalog'; // редирект
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

        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) { //проеряем авторизацию
            return ['redirect' => '/login'];
        }

        $userId = $_SESSION['user_id'];
        $errors = [];
        $success = [];

        $productId = $_POST['product_id'] ?? null;
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

        if (empty($productId)) {
            $errors[] = 'Не указан товар';
        } elseif ($quantity < 1) {
            $errors[] = 'Количество должно быть больше нуля';
        } else {
            $this->model->loadByUserId((int) $userId);
            $result = $this->model->updateQuantity((int) $productId, $quantity);
        }

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
        return ['redirect' => '/cart']; // редирект обратно на корзину
    }

    public function handleRemoveFromCart() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['redirect' => '/cart'];
        }

        if (!isset($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) { // проверка авторизации
            return ['redirect' => '/login'];
        }

        $userId = $_SESSION['user_id'];
        $errors = [];
        $success = [];

        $productId = $_POST['product_id'] ?? null;

        if (empty($productId)) {
            $errors[] = 'Не указан товар';
        } else {
            $this->model->loadByUserId((int) $userId);
            $result = $this->model->remove((int) $productId);

            if (isset($result['success']) && $result['success']) {
                $success[] = $result['message'] ?? 'Товар успешно удален из корзины';
            } else {
                $errors[] = $result['message'] ?? 'Ошибка удаления товара из корзины';
            }
        }

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

        return ['redirect' => '/cart']; // Редирект обратно на корзину
    }
}