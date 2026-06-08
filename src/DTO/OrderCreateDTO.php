<?php

declare(strict_types=1);

namespace DTO;
class OrderCreateDTO
{
    public function __construct(
        private int $userId,
        private string $name,
        private string $address,
        private string $phone,
        private string $comment = ''
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}