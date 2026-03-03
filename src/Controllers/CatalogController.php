<?php
namespace Controllers;

use Models\Cart;
use Models\Model;
use PDO;

class CatalogController extends Model
{
    private ?Cart $cartModel = null;

    public function __construct(PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->cartModel = new Cart($this->pdo);
    }

    public function getAllProducts()
    {
        $stmt = $this->pdo->query("SELECT * FROM products");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function showCatalog()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $products = $this->getAllProducts();
        $userName = $_SESSION['user_name'] ?? '';
        $cartCount = 0;
        if (!empty($_SESSION['user_id'])) {
            $cartCount = $this->cartModel->getCartItemsCount($_SESSION['user_id']);
        }
        require_once __DIR__ . '/../Views/catalog.php';
    }
}