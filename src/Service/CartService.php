<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна

namespace Service;

use Models\Cart;
use Models\User;
use Models\UserProduct;
use Service\Auth\AuthSessionService;
use Service\Logger\LoggerService;

class CartService
{
    private Cart $cartModel;
    private UserProduct $userProductModel;
    private AuthSessionService $authService;

    public function __construct()
    {
        $this->cartModel = new Cart();
        $this->userProductModel = new UserProduct();
        $this->authService = new AuthSessionService();
    }

    public function getUserProducts(): array
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return [];
        }

        return $this->userProductModel->getUserProductsWithProductData((int) $user->getId());
    }

    public function getOrderProducts(User $user): array
    {
        $userId = (int) $user->getId();
        if ($userId === 0) {
            return [];
        }

        return $this->userProductModel->getUserProductsWithProductData($userId);
    }

    public function sumUserProductLines(array $userProducts): float
    {
        $sum = 0.0;
        foreach ($userProducts as $line) {
            $t = $line->getTotalSum();
            if ($t !== null) {
                $sum += $t;
            }
        }
        return $sum;
    }

    public function getCartData(int $userId): array
    {
        $this->loadUserCart($userId);

        return [
            'items' => $this->cartModel->getItems(),
            'total' => $this->cartModel->getTotal(),
        ];
    }

    public function addToCart(int $userId, int $productId, int $quantity): array
    {
        $this->loadUserCart($userId);

        $result = $this->cartModel->addToCart($productId, $quantity);
        if (!($result['success'] ?? false)) {
            $logger = new LoggerService();
            $message =
                'Error adding to cart: ' . ($result['message'] ?? 'Unknown error')
                . ' | User ID: ' . $userId
                . ' | Product ID: ' . $productId
            ;
            $logger->error($message);
            $logger->errorToDb($message, 'error');
        }

        return $result;
    }

    public function updateCartItem(int $userId, int $productId, int $quantity): array
    {
        $this->loadUserCart($userId);
        return $this->cartModel->updateQuantity($productId, $quantity);
    }

    public function removeFromCart(int $userId, int $productId): array
    {
        $this->loadUserCart($userId);
        return $this->cartModel->remove($productId);
    }

    private function loadUserCart(int $userId): void
    {
        $this->cartModel->loadByUserId($userId);
    }
}
