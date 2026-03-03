<?php
namespace Models;

use PDO;
use PDOException;

class Cart extends Model
{
    private $userId;

    public function loadByUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getItems(): array
    {
        if ($this->userId === null) {
            return [];
        }
        try {
            $stmt = $this->pdo->prepare("
                SELECT up.id, up.user_id, up.product_id, up.quantity,
                    p.name, p.price, p.image_url, p.description,
                    (up.quantity * p.price) as item_total
                FROM user_products up
                JOIN products p ON up.product_id = p.id
                WHERE up.user_id = :user_id
                ORDER BY up.id DESC
            ");
            $stmt->execute([':user_id' => $this->userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Cart::getItems: " . $e->getMessage());
            return [];
        }
    }

    public function getTotal(): float
    {
        if ($this->userId === null) {
            return 0.0;
        }
        try {
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(up.quantity * p.price), 0) as total
                FROM user_products up
                JOIN products p ON up.product_id = p.id
                WHERE up.user_id = :user_id
            ");
            $stmt->execute([':user_id' => $this->userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float) ($row['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Cart::getTotal: " . $e->getMessage());
            return 0.0;
        }
    }

    public function getItemsCount(): int
    {
        if ($this->userId === null) {
            return 0;
        }
        try {
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as total_count FROM user_products WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $this->userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) ($row['total_count'] ?? 0);
        } catch (PDOException $e) {
            error_log("Cart::getItemsCount: " . $e->getMessage());
            return 0;
        }
    }

    public function addToCart(int $productId, int $quantity = 1): array
    {
        if ($this->userId === null) {
            return ['success' => false, 'message' => 'Корзина не привязана к пользователю'];
        }
        try {
            $stmt = $this->pdo->prepare("SELECT quantity FROM user_products WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->execute([':user_id' => $this->userId, ':product_id' => $productId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $newQty = (int) $existing['quantity'] + $quantity;
                $stmt = $this->pdo->prepare("UPDATE user_products SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
                $stmt->execute([':quantity' => $newQty, ':user_id' => $this->userId, ':product_id' => $productId]);
                return ['success' => true, 'action' => 'updated', 'message' => 'Количество товара обновлено'];
            }
            $stmt = $this->pdo->prepare("INSERT INTO user_products (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
            $stmt->execute([':user_id' => $this->userId, ':product_id' => $productId, ':quantity' => $quantity]);
            return $stmt->rowCount() > 0
                ? ['success' => true, 'action' => 'added', 'message' => 'Товар добавлен в корзину']
                : ['success' => false, 'message' => 'Не удалось добавить товар в корзину'];
        } catch (PDOException $e) {
            error_log("Cart::addToCart: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при добавлении товара в корзину'];
        }
    }

    public function updateQuantity(int $productId, int $quantity): array
    {
        if ($this->userId === null) {
            return ['success' => false, 'message' => 'Корзина не привязана к пользователю'];
        }
        if ($quantity <= 0) {
            return $this->remove($productId);
        }
        try {
            $stmt = $this->pdo->prepare("UPDATE user_products SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->execute([':quantity' => $quantity, ':user_id' => $this->userId, ':product_id' => $productId]);
            return $stmt->rowCount() > 0
                ? ['success' => true, 'message' => 'Количество товара обновлено']
                : ['success' => false, 'message' => 'Товар не найден в корзине'];
        } catch (PDOException $e) {
            error_log("Cart::updateQuantity: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении количества'];
        }
    }

    public function remove(int $productId): array
    {
        if ($this->userId === null) {
            return ['success' => false, 'message' => 'Корзина не привязана к пользователю'];
        }
        try {
            $stmt = $this->pdo->prepare("DELETE FROM user_products WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->execute([':user_id' => $this->userId, ':product_id' => $productId]);
            return $stmt->rowCount() > 0
                ? ['success' => true, 'message' => 'Товар удален из корзины']
                : ['success' => false, 'message' => 'Товар не найден в корзине'];
        } catch (PDOException $e) {
            error_log("Cart::remove: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при удалении товара'];
        }
    }

    public function clear(): array
    {
        if ($this->userId === null) {
            return ['success' => false, 'message' => 'Корзина не привязана к пользователю'];
        }
        try {
            $stmt = $this->pdo->prepare("DELETE FROM user_products WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $this->userId]);
            return ['success' => true, 'message' => 'Корзина очищена'];
        } catch (PDOException $e) {
            error_log("Cart::clear: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при очистке корзины'];
        }
    }
}
