<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна
// PSR-12 §3: отсутствует пустая строка между <?php и namespace
namespace Models;

use PDO;
use PDOException;

class Product extends Model
{
    // PSR-1 §4.3: у свойств отсутствуют типы — нужно объявить ?int, ?string, ?float
    private $id;
    private $name;
    private $description;
    private $imageUrl;
    private $price;

    public static function getTableName(): string
    {
       return "products";
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

    // PSR-12 §4.4: методы на одной строке — тело должно быть на следующей строке; PSR-1 §4.3: нет return type
    // TODO (PSR-1 §4.3): каждому геттеру нужно добавить return type: ?int, ?string, ?float
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getImageUrl() { return $this->imageUrl; }
    public function getPrice()  { return $this->price; }

    // PSR-12 §4.4: методы на одной строке — тело должно быть на следующей строке
    public function setId(?int $id): void { $this->id = $id; }
    public function setName(?string $name): void { $this->name = $name; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function setImageUrl(?string $imageUrl): void { $this->imageUrl = $imageUrl; }
    public function setPrice(?float $price): void { $this->price = $price; }
}
