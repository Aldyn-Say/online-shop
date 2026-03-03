<?php
namespace Controllers;

use Models\Cart;
use Models\Model;
use Models\Order;
use Models\User;

class CheckoutController extends Model
{
    protected $cartModel;
    protected $orderModel;
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart($this->pdo);
        $this->orderModel = new Order($this->pdo);
        $this->userModel = new User($this->pdo);
    }

    /**
     * Страница оформления заказа: форма с именем, адресом, телефоном, комментарием.
     */
    public function showCheckout()
    {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return ['redirect' => '/login'];
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            return ['redirect' => '/login'];
        }

        $cartItems = $this->cartModel->getCartItems($userId);
        $cartTotal = $this->cartModel->getCartTotal($userId);

        if (empty($cartItems)) {
            $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Корзина пуста. Добавьте товары перед оформлением заказа.'];
            return ['redirect' => '/cart'];
        }

        $user = $this->userModel->getById($userId);
        $checkoutMessage = $_SESSION['checkout_message'] ?? null;
        $checkoutErrors = $_SESSION['checkout_errors'] ?? [];
        $checkoutForm = $_SESSION['checkout_form'] ?? [];
        if (empty($checkoutForm['name']) && $user) {
            $checkoutForm['name'] = $user['name'] ?? '';
        }
        unset($_SESSION['checkout_message'], $_SESSION['checkout_errors'], $_SESSION['checkout_form']);

        require_once __DIR__ . '/../Views/checkout.php';
    }

    /**
     * Обработка формы: валидация, запись в orders и order_products, очистка корзины.
     */
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
}
