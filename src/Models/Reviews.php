<?php

namespace Models;

use PDO;
use PDOException;

class Reviews extends Model
{
    private $id;
    private $productId;
    private $userId;
    private $rating;
    private $comment;
    private $createdAt;
    private $userName;

    public function getTableName(): string
    {
        return 'reviews';
    }

    private function fillFromArray(array $row): void
    {
        $this->id = (int) ($row['id'] ?? 0);
        $this->productId = (int) ($row['product_id'] ?? 0);
        $this->userId = (int) ($row['user_id'] ?? 0);
        $this->rating = (int) ($row['rating'] ?? 0);
        $this->comment = $row['comment'] ?? '';
        $this->createdAt = $row['created_at'] ?? '';
        $this->userName = $row['user_name'] ?? '';
    }

    public function getByProductId(int $productId): array
    {
        $sql = "SELECT pr.*, u.name AS user_name 
            FROM {$this->getTableName()} pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.product_id = :product_id 
            ORDER BY pr.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['product_id' => $productId]);

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $review = new self();
            $review->fillFromArray($row);
            $result[] = $review;
        }

        return $result;
    }

    public function create(int $productId, int $userId, int $rating, string $comment): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->getTableName()} (product_id, user_id, rating, comment) 
         VALUES (:product_id, :user_id, :rating, :comment)"
        );

        return $stmt->execute([
            'product_id' => $productId,
            'user_id' => $userId,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }

    public function userAlreadyReviewed(int $productId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM {$this->getTableName()} WHERE product_id = :product_id AND user_id = :user_id LIMIT 1"
        );
        $stmt->execute(['product_id' => $productId, 'user_id' => $userId]);

        return $stmt->fetch() !== false;
    }
    public function getId() { return $this->id; }
    public function getProductId() { return $this->productId; }
    public function getUserId() { return $this->userId; }
    public function getRating() { return $this->rating; }
    public function getComment() { return $this->comment; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUserName() { return $this->userName; }
}
