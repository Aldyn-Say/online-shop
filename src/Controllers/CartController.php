<?php
require_once '../Models/Cart.php';

class CartController
{
    private $model;

    public function __construct($pdo = null)
    {
        // Создаем модель, передавая подключение к БД
        $this->model = new CartModel($pdo);
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

    // Дополнительные методы контроллера для работы с HTTP-запросами

    public function handleAddToCartRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Неверный метод запроса'];
        }

        $userId = $_POST['user_id'] ?? $_SESSION['user_id'] ?? null;
        $productId = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;

        if (!$userId || !$productId) {
            return ['success' => false, 'message' => 'Не указаны обязательные параметры'];
        }

        return $this->addToCart($userId, $productId, $quantity);
    }

    public function renderCartView($userId) {
        $cartItems = $this->getCartItems($userId);
        $cartTotal = $this->getCartTotal($userId);
        $itemsCount = $this->getCartItemsCount($userId);

        // Здесь обычно идет рендеринг представления
        // Например, require_once __DIR__ . '/../Views/cart.php';

        return [
            'items' => $cartItems,
            'total' => $cartTotal,
            'items_count' => $itemsCount
        ];
    }
}