<?php
namespace Models;

use PDO;
use PDOException;

class Order extends Model
{
    private $id;
    private $userId;
    private $name;
    private $address;
    private $phone;
    private $comment;
    private $total;
    private $status;
    private $createdAt;
    private array $products = [];

    public static function getTableName(): string {
        return 'orders';
    }

    public function fillFromArray(array $row): void
    {
        $this->id = isset($row['id']) ? (int) $row['id'] : null;
        $this->userId = isset($row['user_id']) ? (int) $row['user_id'] : null;
        $this->name = $row['name'] ?? null;
        $this->address = $row['address'] ?? null;
        $this->phone = $row['phone'] ?? null;
        $this->comment = $row['comment'] ?? null;
        $this->total = isset($row['total']) ? (float) $row['total'] : null;
        $this->status = $row['status'] ?? null;
        $this->createdAt = $row['created_at'] ?? null;
    }

    public function setProducts(array $products): void
    {
        $this->products = $products;
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): ?int { return $this->userId; }
    public function getName(): ?string { return $this->name; }
    public function getAddress(): ?string { return $this->address; }
    public function getPhone(): ?string { return $this->phone; }
    public function getComment(): ?string { return $this->comment; }
    public function getTotal(): ?float { return $this->total; }
    public function getStatus(): ?string { return $this->status; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function getProducts(): array { return $this->products; }


    public function createOrder(int $userId, string $name, string $address, string $phone, string $comment = ''): array
    {
        try {
            self::getPDO()->beginTransaction();

            $stmt = self::getPDO()->prepare("
                SELECT up.product_id, up.quantity, p.price, p.name
                FROM user_products up
                JOIN products p ON up.product_id = p.id
                WHERE up.user_id = :user_id
            ");
            $stmt->execute([':user_id' => $userId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($items)) {
                self::getPDO()->rollBack();
                return ['success' => false, 'order_id' => null, 'message' => 'Корзина пуста'];
            }

            $total = 0;
            foreach ($items as $row) {
                $total += $row['price'] * $row['quantity'];
            }

            $stmt = self::getPDO()->prepare("
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

            $stmtItem = self::getPDO()->prepare("
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

            $stmt = self::getPDO()->prepare("DELETE FROM user_products WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);

            self::getPDO()->commit();
            return ['success' => true, 'order_id' => $orderId, 'message' => 'Заказ успешно оформлен'];
        } catch (PDOException $e) {
            if (self::getPDO()->inTransaction()) {
                self::getPDO()->rollBack();
            }
            self::logError('Order::createOrder: ' . $e->getMessage());
            return ['success' => false, 'order_id' => null, 'message' => 'Ошибка при оформлении заказа'];
        }
    }

    public function getOrdersByUserId(int $userId): array
    {
        try {
            $stmt = self::getPDO()->prepare("
                SELECT id, user_id, name, address, phone, comment, total, status, created_at
                FROM " . static::getTableName() . "
                WHERE user_id = :user_id
                ORDER BY created_at DESC
            ");
            $stmt->execute([':user_id' => $userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($rows === []) {
                return [];
            }

            $ordersById = [];
            foreach ($rows as $row) {
                $order = new self();
                $order->fillFromArray($row);
                $ordersById[(int) $row['id']] = $order;
            }

            $linesByOrderId = $this->fetchOrderLinesWithProductsGrouped(array_keys($ordersById));
            foreach ($ordersById as $orderId => $order) {
                $order->setProducts($linesByOrderId[$orderId] ?? []);
            }

            return array_values($ordersById);
        } catch (PDOException $e) {
            self::logError('Order::getOrdersByUserId: ' . $e->getMessage());
            return [];
        }
    }

    private function fetchOrderLinesWithProductsGrouped(array $orderIds): array
    {
        if ($orderIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $stmt = self::getPDO()->prepare("
            SELECT
                op.order_id,
                op.product_id,
                op.amount,
                p.name,
                p.price,
                (op.amount * p.price) AS line_total
            FROM order_products op
            JOIN products p ON op.product_id = p.id
            WHERE op.order_id IN ($placeholders)
            ORDER BY op.order_id, op.id
        ");
        $stmt->execute(array_values($orderIds));

        $grouped = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $oid = (int) $row['order_id'];
            $line = new OrderProduct();
            $line->fillFromArray($row);
            $grouped[$oid][] = $line;
        }

        return $grouped;
    }

    public function getOrderProducts(int $orderId): array
    {
        try {
            $stmt = self::getPDO()->prepare("
                SELECT op.order_id, op.product_id, op.amount, p.name, p.price,
                       (op.amount * p.price) AS line_total
                FROM order_products op
                JOIN products p ON op.product_id = p.id
                WHERE op.order_id = :order_id
            ");
            $stmt->execute([':order_id' => $orderId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [];
            foreach ($rows as $row) {
                $line = new OrderProduct();
                $line->fillFromArray($row);
                $result[] = $line;
            }
            return $result;
        } catch (PDOException $e) {
            self::logError('Order::getOrderProducts: ' . $e->getMessage());
            return [];
        }
    }
}
