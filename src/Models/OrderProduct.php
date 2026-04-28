<?php

namespace Models;

class OrderProduct extends Model
{
    private ?int $id = null;
    private ?int $orderId = null;
    private ?int $productId = null;
    private ?int $amount = null;
    private ?string $productName = null;
    private ?float $productPrice = null;

    public static function getTableName(): string
    {
        return 'order_products';
    }

    public function fillFromArray(array $row): void
    {
        if (array_key_exists('id', $row)) {
            $this->setId($row['id'] !== null ? (int) $row['id'] : null);
        }
        if (array_key_exists('order_id', $row)) {
            $this->setOrderId($row['order_id'] !== null ? (int) $row['order_id'] : null);
        }
        if (array_key_exists('product_id', $row)) {
            $this->setProductId((int) $row['product_id']);
        }
        if (array_key_exists('amount', $row)) {
            $this->setAmount((int) $row['amount']);
        }
        if (array_key_exists('name', $row)) {
            $this->setProductName($row['name'] !== null ? (string) $row['name'] : null);
        }
        if (array_key_exists('price', $row)) {
            $this->setProductPrice($row['price'] !== null ? (float) $row['price'] : null);
        }
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setOrderId(?int $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function setProductName(?string $productName): void
    {
        $this->productName = $productName;
    }

    public function setProductPrice(?float $productPrice): void
    {
        $this->productPrice = $productPrice;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function getProductId(): int
    {
        return (int) $this->productId;
    }

    public function getAmount(): int
    {
        return (int) $this->amount;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function getProductPrice(): ?float
    {
        return $this->productPrice;
    }

    public function getLineTotal(): float
    {
        if ($this->productPrice === null) {
            return 0.0;
        }
        return $this->getAmount() * $this->productPrice;
    }
}
