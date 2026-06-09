<?php

declare(strict_types=1);

namespace Models;

use PDO;
use PDOException;

class Review extends Model
{
    private ?int $id = null;
    private ?int $productId = null;
    private ?int $userId = null;
    private ?int $rating = null;
    private ?string $comment = null;
    private ?string $createdAt = null;
    private ?string $userName = null;

    public static function getTableName(): string
    {
        return 'reviews';
    }

    private function fillFromArray(array $row): void
    {
        $this->id = isset($row['id']) ? (int) $row['id'] : null;
        $this->productId = isset($row['product_id']) ? (int) $row['product_id'] : null;
        $this->userId = isset($row['user_id']) ? (int) $row['user_id'] : null;
        $this->rating = isset($row['rating']) ? (int) $row['rating'] : null;
        $this->comment = $row['comment'] ?? null;
        $this->createdAt = $row['created_at'] ?? null;
        $this->userName = $row['user_name'] ?? null;
    }

    public function getByProductId(int $productId): array
    {
        try {
            $sql = "SELECT pr.*, u.name AS user_name
                FROM " . static::getTableName() . " pr
                JOIN users u ON pr.user_id = u.id
                WHERE pr.product_id = :product_id
                ORDER BY pr.created_at DESC";

            $stmt = self::getPDO()->prepare($sql);
            $stmt->execute([':product_id' => $productId]);

            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $review = new self();
                $review->fillFromArray($row);
                $result[] = $review;
            }

            return $result;
        } catch (PDOException $e) {
            self::logError('Review::getByProductId: ' . $e->getMessage());
            return [];
        }
    }

    public function create(int $productId, int $userId, int $rating, string $comment): bool
    {
        try {
            $stmt = self::getPDO()->prepare(
                "INSERT INTO " . static::getTableName() . " (product_id, user_id, rating, comment)
                 VALUES (:product_id, :user_id, :rating, :comment)"
            );

            return $stmt->execute([
                ':product_id' => $productId,
                ':user_id' => $userId,
                ':rating' => $rating,
                ':comment' => $comment,
            ]);
        } catch (PDOException $e) {
            self::logError('Review::create: ' . $e->getMessage());
            return false;
        }
    }

    public function userAlreadyReviewed(int $productId, int $userId): bool
    {
        try {
            $stmt = self::getPDO()->prepare(
                "SELECT 1 FROM " . static::getTableName() . " WHERE product_id = :product_id AND user_id = :user_id LIMIT 1"
            );
            $stmt->execute([
                ':product_id' => $productId,
                ':user_id' => $userId,
            ]);

            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            self::logError('Review::userAlreadyReviewed: ' . $e->getMessage());
            return false;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }
}
