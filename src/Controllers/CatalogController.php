<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна
// PSR-12 §3: отсутствует пустая строка между <?php и namespace
namespace Controllers;

use Models\Cart;
use Models\Product;

// PSR-12 §3: две пустые строки между блоком use и классом — должна быть одна
class CatalogController extends BaseController
{
    private Cart $cartModel;
    private Product $productModel;

    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }

    // TODO (PSR-1 §4.3): метод showCatalog() не имеет return type — нужно добавить: void
    public function showCatalog()
    {
        $products = $this->productModel->getAll();
        $loggedIn = $this->authService->check();

        $userName = '';
        $cartCount = 0;

        if ($loggedIn) {
            $userId = $this->authService->getCurrentUserId();
            if ($userId > 0) {
                $this->cartModel->loadByUserId($userId);
                $cartCount = $this->cartModel->getItemsCount();
                $userName = $this->authService->getCurrentUserName();
            }
        }

        require_once __DIR__ . '/../Views/catalog.php';
    }
}