<?php
namespace Controllers;

use Models\Cart;
use Models\Model;
use Models\Order;
use Models\User;

class OrderController extends Model
{
    protected $orderModel;
    protected $cartModel;
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order($this->pdo);
        $this->cartModel = new Cart($this->pdo);
        $this->userModel = new User($this->pdo);
    }

    /** Страница оформления заказа (форма). */
    public function showCheckout()
    {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return ['redirect' => '/login'];
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            return ['redirect' => '/login'];
        }

        $this->cartModel->loadByUserId((int) $userId);
        $cartItems = $this->cartModel->getItems();
        $cartTotal = $this->cartModel->getTotal();

        if (empty($cartItems)) {
            $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Корзина пуста. Добавьте товары перед оформлением заказа.'];
            return ['redirect' => '/cart'];
        }

        $user = $this->userModel->getById($userId);
        $checkoutMessage = $_SESSION['checkout_message'] ?? null;
        $checkoutErrors = $_SESSION['checkout_errors'] ?? [];
        $checkoutForm = $_SESSION['checkout_form'] ?? [];
        if (empty($checkoutForm['name']) && $user) {
            $checkoutForm['name'] = $user->getName() ?? '';
        }
        unset($_SESSION['checkout_message'], $_SESSION['checkout_errors'], $_SESSION['checkout_form']);

        require_once __DIR__ . '/../Views/checkout.php';
    }

    /** Обработка отправки формы оформления заказа. */
    public function handleCheckout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['redirect' => '/checkout'];
        }

        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return ['redirect' => '/login'];
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            return ['redirect' => '/login'];
        }

        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $comment = trim($_POST['comment'] ?? '');

        $errors = [];
        if (empty($name)) {
            $errors['name'] = 'Укажите имя';
        }
        if (empty($address)) {
            $errors['address'] = 'Укажите адрес доставки';
        }
        if (empty($phone)) {
            $errors['phone'] = 'Укажите номер телефона';
        } elseif (!preg_match('/^[\d\s\+\-\(\)]{10,20}$/', $phone)) {
            $errors['phone'] = 'Некорректный формат телефона';
        }

        if (!empty($errors)) {
            $_SESSION['checkout_errors'] = $errors;
            $_SESSION['checkout_form'] = ['name' => $name, 'address' => $address, 'phone' => $phone, 'comment' => $comment];
            return ['redirect' => '/checkout'];
        }

        $result = $this->orderModel->createOrder($userId, $name, $address, $phone, $comment);

        if ($result['success']) {
            $_SESSION['order_success'] = $result['message'];
            return ['redirect' => '/orders'];
        }

        $_SESSION['checkout_message'] = ['type' => 'error', 'text' => $result['message']];
        return ['redirect' => '/checkout'];
    }

    /** Список заказов пользователя. */
    public function showOrders()
    {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return ['redirect' => '/login'];
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            return ['redirect' => '/login'];
        }

        $orders = $this->orderModel->getOrdersByUserId($userId);
        $orderSuccess = $_SESSION['order_success'] ?? null;
        unset($_SESSION['order_success']);

        require_once __DIR__ . '/../Views/orders.php';
    }
}
