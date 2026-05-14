<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна
// PSR-12 §3: отсутствует пустая строка между <?php и namespace
namespace Controllers;

use DTO\OrderCreateDTO;
use Models\User;
use Models\UserProduct;
use Service\CartService;
use Service\OrderService;

class OrderController extends BaseController
{
    protected OrderService $orderService;
    protected CartService $cartService;

    public function __construct()
    {
        parent::__construct();
        $this->orderService = new OrderService();
        $this->cartService = new CartService();
    }

    // TODO (Бизнес-логика): метод getOrderProducts() объявлен как protected, но нигде не вызывается
    // внутри класса или наследников — либо удалить метод, либо реализовать его использование.
    // Также импортируются Models\User и Models\UserProduct, но они не используются в этом файле — удалить.
    protected function getOrderProducts(User $user): array
    {
        return $this->cartService->getOrderProducts($user);
    }

    // TODO (PSR-1 §4.3): методы showCheckout(), handleCheckout(), showOrders() не имеют return type —
    // нужно добавить: array|null (или разделить на отдельные методы с чёткими типами)
    public function showCheckout()
    {
        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }
        $userId = $this->authService->getCurrentUserId();
        if ($userId === 0) {
            return ['redirect' => '/login'];
        }

        $this->authService->startSession();
        $checkoutData = $this->orderService->getCheckoutData((int) $userId);
        $cartItems = $checkoutData['cartItems'];
        $cartTotal = $checkoutData['cartTotal'];

        if (empty($cartItems)) {
            $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Корзина пуста. Добавьте товары перед оформлением заказа.'];
            return ['redirect' => '/cart'];
        }

        $user = $checkoutData['user'];
        $checkoutMessage = $_SESSION['checkout_message'] ?? null;
        $checkoutErrors = $_SESSION['checkout_errors'] ?? [];
        $checkoutForm = $_SESSION['checkout_form'] ?? [];
        if (empty($checkoutForm['name']) && $user) {
            $checkoutForm['name'] = $user->getName() ?? '';
        }
        unset($_SESSION['checkout_message'], $_SESSION['checkout_errors'], $_SESSION['checkout_form']);

        require_once __DIR__ . '/../Views/checkout.php';
    }

    public function handleCheckout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['redirect' => '/checkout'];
        }

        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }
        $userId = $this->authService->getCurrentUserId();
        if ($userId === 0) {
            return ['redirect' => '/login'];
        }
        $this->authService->startSession();

        $orderCreateDTO = new OrderCreateDTO(
            (int) $userId,
            trim($_POST['name'] ?? ''),
            trim($_POST['address'] ?? ''),
            trim($_POST['phone'] ?? ''),
            trim($_POST['comment'] ?? '')
        );

        $errors = $this->orderService->validateCheckoutForm($orderCreateDTO);

        if (!empty($errors)) {
            $_SESSION['checkout_errors'] = $errors;
            $_SESSION['checkout_form'] = [
                'name' => $orderCreateDTO->getName(),
                'address' => $orderCreateDTO->getAddress(),
                'phone' => $orderCreateDTO->getPhone(),
                'comment' => $orderCreateDTO->getComment()
            ];
            return ['redirect' => '/checkout'];
        }

        $result = $this->orderService->createOrder($orderCreateDTO);

        if ($result['success']) {
            $_SESSION['order_success'] = $result['message'];
            return ['redirect' => '/orders'];
        }

        $_SESSION['checkout_message'] = ['type' => 'error', 'text' => $result['message']];
        return ['redirect' => '/checkout'];
    }

    public function showOrders()
    {
        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }
        $userId = $this->authService->getCurrentUserId();
        if ($userId === 0) {
            return ['redirect' => '/login'];
        }
        $this->authService->startSession();

        $orders = $this->orderService->getOrdersByUserId((int) $userId);
        $orderSuccess = $_SESSION['order_success'] ?? null;
        unset($_SESSION['order_success']);

        require_once __DIR__ . '/../Views/orders.php';
    }
}
