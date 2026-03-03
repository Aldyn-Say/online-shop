<?php
namespace Controllers;

use Models\Model;
use Models\Order;

class OrderController extends Model
{
    protected $orderModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order($this->pdo);
    }

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
