<?php

declare(strict_types=1);

namespace Controllers;

use Models\Product;
use Models\Review;

class ReviewsController extends BaseController
{
    private Product $productModel;
    private Review $reviewModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->reviewModel = new Review();
    }

    public function showProduct(): array|null
    {
        $productId = (int) ($_POST['product_id'] ?? $_GET['id'] ?? 0);

        if ($productId <= 0 || !$product = $this->productModel->getById($productId)) {
            return ['redirect' => '/catalog'];
        }

        $reviews = $this->reviewModel->getByProductId($productId);

        $this->authService->startSession();

        $loggedIn = $this->authService->check();
        $userId = $loggedIn ? $this->authService->getCurrentUserId() : 0;
        $canReview = $loggedIn && $userId > 0 && !$this->reviewModel->userAlreadyReviewed($productId, $userId);
        $message = $_SESSION['product_message'] ?? null;

        unset($_SESSION['product_message']);

        require __DIR__ . '/../Views/reviews.php';
        return null;
    }

    public function handleAddReview(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['redirect' => '/catalog'];
        }

        $this->authService->startSession();

        if (!$this->authService->check()) {
            $_SESSION['product_message'] = ['type' => 'error', 'text' => 'Войдите, чтобы оставить отзыв.'];
            return ['redirect' => '/login'];
        }

        $userId = $this->authService->getCurrentUserId();
        $productId = (int) ($_POST['product_id'] ?? 0);
        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($productId <= 0) {
            $_SESSION['product_message'] = ['type' => 'error', 'text' => 'Не указан товар.'];
            return ['redirect' => '/catalog'];
        }

        if ($rating < 1 || $rating > 5) {
            $_SESSION['product_message'] = ['type' => 'error', 'text' => 'Оценка должна быть от 1 до 5.'];
            return ['redirect' => "/reviews?id=$productId"];
        }

        if ($this->reviewModel->userAlreadyReviewed($productId, $userId)) {
            $_SESSION['product_message'] = ['type' => 'error', 'text' => 'Вы уже оставляли отзыв.'];
            return ['redirect' => "/reviews?id=$productId"];
        }

        $success = $this->reviewModel->create($productId, $userId, $rating, $comment);

        $_SESSION['product_message'] = $success
            ? ['type' => 'success', 'text' => 'Отзыв добавлен!']
            : ['type' => 'error', 'text' => 'Ошибка при сохранении.'];

        return ['redirect' => "/reviews?id=$productId"];
    }
}
