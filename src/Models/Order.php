<?php
namespace Models;

use PDO;
use PDOException;

class Order extends Model
{
    /**
     * Оформление заказа: данные в orders, товары в order_products, корзина очищается.
     * @param int $userId
     * @param string $name Имя
     * @param string $address Адрес доставки
     * @param string $phone Телефон
     * @param string $comment Комментарий к заказу
     * @return array ['success' => bool, 'order_id' => int|null, 'message' => string]
     */
    public function createOrder(int $userId, string $name, string $address, string $phone, string $comment = ''): array
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                SELECT up.product_id, up.quantity, p.price, p.name
                FROM user_products up
                JOIN products p ON up.product_id = p.id
                WHERE up.user_id = :user_id
            ");
            $stmt->execute([':user_id' => $userId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($items)) {
                $this->pdo->rollBack();
                return ['success' => false, 'order_id' => null, 'message' => 'Корзина пуста'];
            }

            $total = 0;
            foreach ($items as $row) {
                $total += $row['price'] * $row['quantity'];
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO orders (user_id, name, address, phone, comment, total, status)
                VALUES (:user_id, :name, :address, :phone, :comment, :total, 'new')
                RETURNING id
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':name' => $name,
                ':address' => $address,
                ':phone' => $phone,
                ':comment' => $comment,
                ':total' => $total
            ]);
            $orderRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $orderId = (int) $orderRow['id'];

            $stmtItem = $this->pdo->prepare("
                INSERT INTO order_products (order_id, product_id, amount)
                VALUES (:order_id, :product_id, :amount)
            ");
            foreach ($items as $row) {
                $stmtItem->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $row['product_id'],
                    ':amount' => $row['quantity']
                ]);
            }

            $stmt = $this->pdo->prepare("DELETE FROM user_products WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);

            $this->pdo->commit();
            return ['success' => true, 'order_id' => $orderId, 'message' => 'Заказ успешно оформлен'];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Order::createOrder: " . $e->getMessage());
            return ['success' => false, 'order_id' => null, 'message' => 'Ошибка при оформлении заказа'];
        }
    }

    /**
     * Список заказов пользователя с товарами.
     */
    public function getOrdersByUserId(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, address, phone, comment, total, status, created_at
                FROM orders
                WHERE user_id = :user_id
                ORDER BY created_at DESC
            ");
            $stmt->execute([':user_id' => $userId]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($orders as &$order) {
                $order['products'] = $this->getOrderProducts((int) $order['id']);
            }
            unset($order);

            return $orders;
        } catch (PDOException $e) {
            error_log("Order::getOrdersByUserId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Товары заказа (из order_products + название и цена из products).
     */
    public function getOrderProducts(int $orderId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT op.product_id, op.amount, p.name, p.price
                FROM order_products op
                JOIN products p ON op.product_id = p.id
                WHERE op.order_id = :order_id
            ");
            $stmt->execute([':order_id' => $orderId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Order::getOrderProducts: " . $e->getMessage());
            return [];
        }
    }
}
