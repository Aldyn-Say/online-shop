<?php

use Controllers\CartController;
use Controllers\CatalogController;
use Controllers\CheckoutController;
use Controllers\OrderController;
use Controllers\UserController;
use Core\App;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$autoload = function (string $className)
{
    $path = str_replace('\\', '/', $className);
    $path =  "./../$path.php";
    if (file_exists($path)) {
        require_once $path;
        return true;
    }
    return false;
};
spl_autoload_register($autoload);

$app = new App();
$app->addRoute('/registration', 'GET', UserController::class , 'showRegistrationForm');
$app->addRoute('/registration', 'POST', UserController::class , 'handleRegistration');
$app->addRoute('/login', 'GET', UserController::class , 'showLoginForm');
$app->addRoute('/login', 'POST', UserController::class , 'handleLogin');
$app->addRoute('/catalog', 'GET', CatalogController::class , 'showCatalog');
$app->addRoute('/profile', 'GET', UserController::class , 'showProfile');
$app->addRoute('/profile', 'POST', UserController::class , 'handleProfileUpdate');
$app->addRoute('/upload_avatar', 'POST', UserController::class , 'handleAvatarUpload');
$app->addRoute('/cart', 'GET', CartController::class , 'showCart');
$app->addRoute('/add-to-cart', 'POST', CartController::class , 'handleAddToCart');
$app->addRoute('/update-cart', 'POST', CartController::class , 'handleUpdateCart');
$app->addRoute('/remove-from-cart', 'POST', CartController::class , 'handleRemoveFromCart');
$app->addRoute('/checkout', 'GET', CheckoutController::class, 'showCheckout');
$app->addRoute('/checkout', 'POST', CheckoutController::class, 'handleCheckout');
$app->addRoute('/orders', 'GET', OrderController::class, 'showOrders');
$app->addRoute('/logout', 'GET', UserController::class , 'logout');
$app->run();