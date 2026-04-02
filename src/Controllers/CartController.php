<?php
namespace Controllers;

use Service\CartService;
use Request\AddProductRequest;

class CartController extends BaseController
{
    protected CartService $cartService;

    public function __construct()
    {
        parent::__construct();
        $this->cartService = new CartService();
    }

    public function showCart()
    {
        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }

        $userId = $this->authService->getCurrentUserId();
        if ($userId === 0) {
            return ['redirect' => '/login'];
        }

        $this->authService->startSession();
        $cartItems = $this->cartService->getUserProducts();
        $cartTotal = $this->cartService->sumUserProductLines($cartItems);
        $cartMessage = $_SESSION['cart_message'] ?? null;
        unset($_SESSION['cart_message']);

        require_once __DIR__ . '/../Views/cart.php';
    }

    public function handleAddToCart(AddProductRequest $request) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['redirect' => '/catalog'];
        }
        $this->authService->startSession();

        if (!$this->authService->check()) {
            $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Необходимо войти в систему для добавления товаров в корзину'];
            return ['redirect' => '/login'];
        }

        $userId = $this->authService->getCurrentUserId();
        if ($userId === 0) {
            $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Ошибка авторизации'];
            return ['redirect' => '/login'];
        }

        $productId = $request->getProductId();
        $quantity = $request->getQuantity();
        $errors = $request->validate();

        if (empty($errors)) {
            $result = $this->cartService->addToCart((int) $userId, $productId, $quantity);

            if ($result['success'] ?? false) {
                $_SESSION['cart_message'] = [
                    'type' => 'success',
                    'text' => $result['message'] ?? 'Товар добавлен в корзину'
                ];
            } else {
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

        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }

        $userId = $this->authService->getCurrentUserId();
        $productId = $_POST['product_id'] ?? null;
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

        $errors = [];
        if (empty($productId)) {
            $errors[] = 'Не указан товар';
        } else {
            $result = $this->cartService->updateCartItem((int) $userId, (int) $productId, $quantity);
            if (!($result['success'] ?? false)) {
                $errors[] = $result['message'] ?? 'Ошибка обновления количества';
            }
        }

        $this->authService->startSession();
        if (!empty($errors)) {
            $_SESSION['cart_message'] = [
                'type' => 'error',
                'text' => implode(', ', $errors)
            ];
        } else {
            $_SESSION['cart_message'] = [
                'type' => 'success',
                'text' => $result['message'] ?? 'Количество товара обновлено'
            ];
        }
        return ['redirect' => '/cart'];
    }

    public function handleRemoveFromCart() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['redirect' => '/cart'];
        }

        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }

        $userId = $this->authService->getCurrentUserId();
        $productId = $_POST['product_id'] ?? null;
        $errors = [];

        if (empty($productId)) {
            $errors[] = 'Не указан товар';
        } else {
            $result = $this->cartService->removeFromCart((int) $userId, (int) $productId);
            if (!($result['success'] ?? false)) {
                $errors[] = $result['message'] ?? 'Ошибка удаления товара из корзины';
            }
        }

        $this->authService->startSession();
        if (!empty($errors)) {
            $_SESSION['cart_message'] = [
                'type' => 'error',
                'text' => implode(', ', $errors)
            ];
        } else {
            $_SESSION['cart_message'] = [
                'type' => 'success',
                'text' => $result['message'] ?? 'Товар успешно удален из корзины'
            ];
        }

        return ['redirect' => '/cart'];
    }
}