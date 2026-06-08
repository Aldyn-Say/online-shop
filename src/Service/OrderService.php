<?php

declare(strict_types=1);

namespace Service;

use DTO\OrderCreateDTO;
use Models\Cart;
use Models\Order;
use Models\User;
use Service\Auth\AuthSessionService;

class OrderService
{
    private Order $orderModel;
    private Cart $cartModel;
    private User $userModel;
    private AuthSessionService $authService;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->cartModel = new Cart();
        $this->userModel = new User();
        $this->authService = new AuthSessionService();
    }

    public function getCheckoutData(int $userId): array
    {
        $this->cartModel->loadByUserId($userId);

        return [
            'cartItems' => $this->cartModel->getItems(),
            'cartTotal' => $this->cartModel->getTotal(),
            'user' => $this->userModel->getById($userId),
        ];
    }

    public function validateCheckoutForm(OrderCreateDTO $orderCreateDTO): array
    {
        $errors = [];
        $name = trim($orderCreateDTO->getName());
        $address = trim($orderCreateDTO->getAddress());
        $phone = trim($orderCreateDTO->getPhone());

        if ($name === '') {
            $errors['name'] = 'Укажите имя';
        }
        if ($address === '') {
            $errors['address'] = 'Укажите адрес доставки';
        }
        if ($phone === '') {
            $errors['phone'] = 'Укажите номер телефона';
        } elseif (!preg_match('/^[\d\s\+\-\(\)]{10,20}$/', $phone)) {
            $errors['phone'] = 'Некорректный формат телефона';
        }

        return $errors;
    }

    public function createOrder(OrderCreateDTO $orderCreateDTO): array
    {
        return $this->orderModel->createOrder(
            $orderCreateDTO->getUserId(),
            trim($orderCreateDTO->getName()),
            trim($orderCreateDTO->getAddress()),
            trim($orderCreateDTO->getPhone()),
            trim($orderCreateDTO->getComment())
        );
    }

    public function getOrdersByUserId(int $userId): array
    {
        return $this->orderModel->getOrdersByUserId($userId);
    }
}
