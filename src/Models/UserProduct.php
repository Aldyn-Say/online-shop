<?php

declare(strict_types=1);

namespace Models;

use PDO;
use PDOException;

class UserProduct extends Model
{
    private ?int $id = null;
    private ?int $userId = null;
    private ?int $productId = null;
    private ?int $quantity = null;
    private ?Product $product = null;
    private ?float $totalSum = null;

    protected static function getTableName(): string
    {
        return 'user_products';
    }

    public function fillFromArray(array $row): void
    {
        if (array_key_exists('id', $row)) {
            $this->id = $row['id'] !== null ? (int) $row['id'] : null;
        }
        if (array_key_exists('user_id', $row)) {
            $this->userId = $row['user_id'] !== null ? (int) $row['user_id'] : null;
        }
        if (array_key_exists('product_id', $row)) {
            $this->productId = (int) $row['product_id'];
        }
        if (array_key_exists('quantity', $row)) {
            $this->quantity = (int) $row['quantity'];
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getProductId(): int
    {
        return (int) $this->productId;
    }

    public function getAmount(): int
    {
        return (int) $this->quantity;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): void
    {
        $this->product = $product;
    }

    public function getTotalSum(): ?float
    {
        return $this->totalSum;
    }

    public function setTotalSum(?float $totalSum): void
    {
        $this->totalSum = $totalSum;
    }

    public function getUserProductsWithProductData(int $userId): array
    {
        try {
            $sql = "
                SELECT
                    up.id,
                    up.user_id,
                    up.product_id,
                    up.quantity,
                    p.name AS product_name,
                    p.price AS product_price,
                    p.description AS product_description,
                    p.image_url AS product_image_url,
                    (up.quantity * p.price) AS line_total
                FROM user_products up
                INNER JOIN products p ON up.product_id = p.id
                WHERE up.user_id = :user_id
                ORDER BY up.id DESC
            ";

            $stmt = self::getPDO()->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->hydrateUserProductsWithProducts($rows);
        } catch (PDOException $e) {
            self::logError('UserProduct::getUserProductsWithProductData: ' . $e->getMessage());
            return [];
        }
    }

    private function hydrateUserProductsWithProducts(array $rows): array
    {
        $result = [];

        foreach ($rows as $row) {
            $product = new Product();
            $product->setId(isset($row['product_id']) ? (int) $row['product_id'] : null);
            $product->setName($row['product_name'] ?? null);
            $product->setPrice(isset($row['product_price']) ? (float) $row['product_price'] : null);
            $product->setDescription($row['product_description'] ?? null);
            $product->setImageUrl($row['product_image_url'] ?? null);

            $line = new self();
            $line->fillFromArray($row);
            $line->setProduct($product);

            $line->setTotalSum(isset($row['line_total']) ? (float) $row['line_total'] : 0.0);

            $result[] = $line;
        }

        return $result;
    }

    public function getCartTotal(int $userId): float
    {
        try {
            $sql = "
                SELECT COALESCE(SUM(up.quantity * p.price), 0) AS total
                FROM user_products up
                INNER JOIN products p ON up.product_id = p.id
                WHERE up.user_id = :user_id
            ";
            $stmt = self::getPDO()->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return (float) ($row['total'] ?? 0);
        } catch (PDOException $e) {
            self::logError('UserProduct::getCartTotal: ' . $e->getMessage());
            return 0.0;
        }
    }

    public function getCartItemsCount(int $userId): int
    {
        try {
            $sql = "
                SELECT COALESCE(SUM(up.quantity), 0) AS count
                FROM user_products up
                WHERE up.user_id = :user_id
            ";
            $stmt = self::getPDO()->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int) ($row['count'] ?? 0);
        } catch (PDOException $e) {
            self::logError('UserProduct::getCartItemsCount: ' . $e->getMessage());
            return 0;
        }
    }

    public function getAllUserProductsByUserId(int $userId): array
    {
        try {
            $stmt = self::getPDO()->prepare("
                SELECT id, user_id, product_id, quantity
                FROM " . static::getTableName() . "
                WHERE user_id = :user_id
                ORDER BY id DESC
            ");
            $stmt->execute([':user_id' => $userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [];
            foreach ($rows as $row) {
                $line = new self();
                $line->fillFromArray($row);
                $result[] = $line;
            }

            return $result;
        } catch (PDOException $e) {
            self::logError('UserProduct::getAllUserProductsByUserId: ' . $e->getMessage());

            return [];
        }
    }
}
