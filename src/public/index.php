<?php

use Controllers\CartController;
use Controllers\CatalogController;
use Controllers\OrderController;
use Controllers\ReviewsController;
use Controllers\UserController;
use Core\App;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../Core/Autoload/Autoloader.php';
$path = dirname(__DIR__);
\Core\Autoload\Autoloader::register($path);

$app = new App();
$app->get('/registration', UserController::class , 'showRegistrationForm');
$app->post('/registration', UserController::class , 'handleRegistration');
$app->get('/login', UserController::class , 'showLoginForm');
$app->post('/login',  UserController::class , 'handleLogin');
$app->get('/catalog',  CatalogController::class , 'showCatalog');
$app->get('/reviews', ReviewsController::class, 'showProduct');
$app->post('/reviews', ReviewsController::class, 'showProduct');
$app->post('/add-review', ReviewsController::class, 'handleAddReview');
$app->get('/profile',  UserController::class , 'showProfile');
$app->post('/profile', UserController::class , 'handleProfileUpdate');
$app->post('/upload_avatar',  UserController::class , 'handleAvatarUpload');
$app->get('/cart', CartController::class , 'showCart');
$app->post('/add-to-cart', CartController::class , 'handleAddToCart');
$app->post('/update-cart', CartController::class , 'handleUpdateCart');
$app->post('/remove-from-cart', CartController::class , 'handleRemoveFromCart');
$app->get('/checkout', OrderController::class, 'showCheckout');
$app->post('/checkout', OrderController::class, 'handleCheckout');
$app->get('/orders', OrderController::class, 'showOrders');
$app->get('/logout', UserController::class , 'logout');
$app->run();