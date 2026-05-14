<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна

namespace Request;

class AddProductRequest
{
    public function __construct(private array $data)
    {
    }

    public function getProductId(): int
    {
        return (int) ($this->data['product_id'] ?? 0);
    }

    public function getQuantity(): int
    {
        return (int) ($this->data['quantity'] ?? 1);
    }

    public function validate(): array
    {
        // TODO (Качество кода): первая строка $errors = [] лишняя — значение сразу перезаписывается
        // через array_merge(). Нужно упростить:
        //   return array_merge($this->validateProductId(), $this->validateQuantity());
        $errors = [];
        $errors = array_merge($errors, $this->validateProductId());
        $errors = array_merge($errors, $this->validateQuantity());

        return $errors;
    }


    private function validateProductId(): array
    {
        if ($this->getProductId() <= 0) {
            return ['Не указан корректный товар'];
        }

        return [];
    }

    private function validateQuantity(): array
    {
        if ($this->getQuantity() <= 0) {
            return ['Количество должно быть больше нуля'];
        }

        return [];
    }
}
