<?php
namespace Controllers;

use Models\Cart;
use Models\Product;


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

    public function showCatalog()
    {
        $products = $this->productModel->getAll();
        $loggedIn = $this->authService->check();
        $userName = '';
        $cartCount = 0;
        if ($loggedIn) {
            $userName = $this->authService->getCurrentUserName();
            $userId = $this->authService->getCurrentUserId();
            if ($userId > 0) {
                $this->cartModel->loadByUserId($userId);
                $cartCount = $this->cartModel->getItemsCount();
            }
        }
        require_once __DIR__ . '/../Views/catalog.php';
    }
}