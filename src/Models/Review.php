<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна

namespace Models;

use PDO;
use PDOException;

class Review extends Model
{
    // PSR-1 §4.3: у свойств отсутствуют типы — нужно ?int, ?string
    private $id;
    private $productId;
    private $userId;
    private $rating;
    private $comment;
    private $createdAt;
    private $userName;

    public static function getTableName(): string
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

    // TODO (Бизнес-логика): метод getByProductId() не обёрнут в try-catch —
    // при ошибке БД выбросится необработанное исключение PDOException, что приведёт к 500.
    // Нужно обернуть в try-catch PDOException, как сделано в других методах этого класса.
    public function getByProductId(int $productId): array
    {
        $sql = "SELECT pr.*, u.name AS user_name
            FROM " . static::getTableName() . " pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.product_id = :product_id
            ORDER BY pr.created_at DESC";

        $stmt = self::getPDO()->prepare($sql);
        $stmt->execute(['product_id' => $productId]);

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $review = new self();
            $review->fillFromArray($row);
            $result[] = $review;
        }

        return $result;
    }

    // TODO (Бизнес-логика): метод create() не обёрнут в try-catch —
    // при ошибке БД (например, дубль записи) выбросится необработанное PDOException.
    // Нужно обернуть в try-catch PDOException и вернуть false при ошибке.
    public function create(int $productId, int $userId, int $rating, string $comment): bool
    {
        $stmt = self::getPDO()->prepare(
            "INSERT INTO " . static::getTableName() . " (product_id, user_id, rating, comment) 
         VALUES (:product_id, :user_id, :rating, :comment)"
        );

        return $stmt->execute([
            'product_id' => $productId,
            'user_id' => $userId,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }

    // TODO (Бизнес-логика): метод userAlreadyReviewed() не обёрнут в try-catch —
    // при ошибке БД выбросится необработанное PDOException.
    // Нужно обернуть в try-catch PDOException и возвращать false при ошибке.
    public function userAlreadyReviewed(int $productId, int $userId): bool
    {
        $stmt = self::getPDO()->prepare(
            "SELECT 1 FROM " . static::getTableName() . " WHERE product_id = :product_id AND user_id = :user_id LIMIT 1"
        );
        $stmt->execute(['product_id' => $productId, 'user_id' => $userId]);

        return $stmt->fetch() !== false;
    }
    // PSR-12 §4.4: методы на одной строке — тело должно быть на следующей строке; PSR-1 §4.3: нет return type
    // TODO (PSR-1 §4.3): каждому геттеру нужно добавить return type: ?int, ?string
    public function getId() { return $this->id; }
    public function getProductId() { return $this->productId; }
    public function getUserId() { return $this->userId; }
    public function getRating() { return $this->rating; }
    public function getComment() { return $this->comment; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUserName() { return $this->userName; }
}
