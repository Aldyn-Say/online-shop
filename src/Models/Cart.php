<?php
namespace Models;

use PDO;
use PDOException;

class Cart extends Model {

    public function addToCart($userId, $productId, $quantity = 1) { //добавление товара в корзину
        try {
            // Приводим к целым числам
            $userId = intval($userId);
            $productId = intval($productId);
            $quantity = intval($quantity);

            // Проверяем, есть ли уже такой товар в корзине
            $stmt = $this->pdo->prepare("SELECT quantity FROM user_products WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
            $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingItem) {
                // Товар уже есть - увеличиваем quantity
                $newQuantity = $existingItem['quantity'] + $quantity;
                $stmt = $this->pdo->prepare("UPDATE user_products SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
                $stmt->execute([
                    ':quantity' => $newQuantity,
                    ':user_id' => $userId,
                    ':product_id' => $productId
                ]);
                return ['success' => true, 'action' => 'updated', 'message' => 'Количество товара обновлено'];
            } else {
                // Товара нет - создаем новую запись
                $stmt = $this->pdo->prepare("INSERT INTO user_products (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                $stmt->execute([
                    ':user_id' => $userId,
                    ':product_id' => $productId,
                    ':quantity' => $quantity
                ]);

                if ($stmt->rowCount() > 0) {
                    error_log("CartModel: Successfully added product " . $productId . " for user " . $userId);
                    return ['success' => true, 'action' => 'added', 'message' => 'Товар добавлен в корзину'];
                } else {
                    error_log("CartModel: Failed to insert - rowCount = 0");
                    return ['success' => false, 'message' => 'Не удалось добавить товар в корзину'];
                }
            }
        } catch (PDOException $e) {
            error_log("CartModel error in addToCart: " . $e->getMessage() . " | User ID: " . $userId . " | Product ID: " . $productId);
            return ['success' => false, 'message' => 'Ошибка при добавлении товара в корзину: ' . $e->getMessage()];
        }
    }

    public function getCartItems($userId) {    //получаем товары добавленные в корзину
        try {
            $userId = intval($userId);

            $stmt = $this->pdo->prepare("
                SELECT 
                    up.id,
                    up.user_id,
                    up.product_id,
                    up.quantity,
                    p.name,
                    p.price,
                    p.image_url,
                    p.description,
                    (up.quantity * p.price) as item_total
                FROM user_products up
                JOIN products p ON up.product_id = p.id
                WHERE up.user_id = :user_id
                ORDER BY up.id DESC
            ");
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("CartModel error in getCartItems: " . $e->getMessage());
            return [];
        }
    }

    public function updateCartItemQuantity($userId, $productId, $quantity) { //обновляем количество товаров в корзине
        try {
            if ($quantity <= 0) {
                // Если quantity <= 0, удаляем товар
                return $this->removeFromCart($userId, $productId);
            }

            $stmt = $this->pdo->prepare("UPDATE user_products SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->execute([
                ':quantity' => $quantity,
                ':user_id' => $userId,
                ':product_id' => $productId
            ]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Количество товара обновлено'];
            } else {
                return ['success' => false, 'message' => 'Товар не найден в корзине'];
            }
        } catch (PDOException $e) {
            error_log("CartModel error in updateCartItemQuantity: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении количества'];
        }
    }

    public function removeFromCart($userId, $productId) { //удаление товара из корзины
        try {
            $stmt = $this->pdo->prepare("DELETE FROM user_products WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->execute([
                ':user_id' => $userId,
                ':product_id' => $productId
            ]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Товар удален из корзины'];
            } else {
                return ['success' => false, 'message' => 'Товар не найден в корзине'];
            }
        } catch (PDOException $e) {
            error_log("CartModel error in removeFromCart: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при удалении товара'];
        }
    }

    public function clearCart($userId) { //очистка корзины
        try {
            $stmt = $this->pdo->prepare("DELETE FROM user_products WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);

            return ['success' => true, 'message' => 'Корзина очищена'];
        } catch (PDOException $e) {
            error_log("CartModel error in clearCart: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при очистке корзины'];
        }
    }

    public function getCartTotal($userId) { //получение общей стоимости товаров в корзине
        try {
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(up.quantity * p.price), 0) as total
                FROM user_products up
                JOIN products p ON up.product_id = p.id
                WHERE up.user_id = :user_id
            ");
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return floatval($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("CartModel error in getCartTotal: " . $e->getMessage());
            return 0;
        }
    }

    public function getCartItemsCount($userId) { //получение количества всех товаров
        try {
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as total_count FROM user_products WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return intval($result['total_count'] ?? 0);
        } catch (PDOException $e) {
            error_log("CartModel error in getCartItemsCount: " . $e->getMessage());
            return 0;
        }
    }

    public function getCartUniqueItemsCount($userId) { //получение количества уникальных товаров
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM user_products WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return intval($result['count'] ?? 0);
        } catch (PDOException $e) {
            error_log("CartModel error in getCartUniqueItemsCount: " . $e->getMessage());
            return 0;
        }
    }
}