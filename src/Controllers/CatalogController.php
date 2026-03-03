<?php
namespace Controllers;

use Models\Cart;
use Models\Model;
use Models\Product;
use PDO;

class CatalogController extends Model
{
    private Cart $cartModel;
    private Product $productModel;

    public function __construct(PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->cartModel = new Cart($this->pdo);
        $this->productModel = new Product($this->pdo);
    }

    public function showCatalog()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $products = $this->productModel->getAll();
        $userName = $_SESSION['user_name'] ?? '';
        $cartCount = 0;
        if (!empty($_SESSION['user_id'])) {
            $this->cartModel->loadByUserId((int) $_SESSION['user_id']);
            $cartCount = $this->cartModel->getItemsCount();
        }
        require_once __DIR__ . '/../Views/catalog.php';
    }
}