<?php

declare(strict_types=1);

namespace Models;

use PDO;
use PDOException;

class Product extends Model
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $description = null;
    private ?string $imageUrl = null;
    private ?float $price = null;

    public static function getTableName(): string
    {
        return 'products';
    }

    private function fillFromArray(array $row): void
    {
        $this->id = isset($row['id']) ? (int) $row['id'] : null;
        $this->name = $row['name'] ?? null;
        $this->description = $row['description'] ?? null;
        $this->imageUrl = $row['image_url'] ?? null;
        $this->price = isset($row['price']) ? (float) $row['price'] : null;
    }

    public function getAll(): array
    {
        try {
            $stmt = self::getPDO()->query("SELECT * FROM " . static::getTableName());
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [];
            foreach ($rows as $row) {
                $product = new self();
                $product->fillFromArray($row);
                $result[] = $product;
            }

            return $result;
        } catch (PDOException $e) {
            self::logError('Product::getAll: ' . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): ?self
    {
        try {
            $stmt = self::getPDO()->prepare("SELECT * FROM " . static::getTableName() . " WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }
            $product = new self();
            $product->fillFromArray($row);
            return $product;
        } catch (PDOException $e) {
            self::logError('Product::getById: ' . $e->getMessage());
            return null;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }
}
