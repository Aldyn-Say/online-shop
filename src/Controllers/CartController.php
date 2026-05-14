<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна
// PSR-12 §3: отсутствует пустая строка между <?php и namespace
namespace Controllers;

use Models\Cart;
use Models\Model;
use Service\CartService;
use Request\AddProductRequest;

class CartController extends BaseController
{
    protected CartService $cartService;
    private Cart $cartModel;

    public function __construct()
    {
        parent::__construct();
        $this->cartService = new CartService();
        $this->cartModel = new Cart();
    }

    private function isAjaxRequest(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // TODO (Бизнес-логика): этот метод содержит SQL-запрос прямо в контроллере —
    // запросы к БД должны быть вынесены в Model или Service (нарушение принципа разделения ответственности).
    // Метод дублирует логику Cart::getItems() — нужно использовать уже существующий метод модели.
    private function getUserProductQuantity(int $userId, int $productId): int
    {
        try {
            $stmt = Model::getPDO()->prepare(
                "SELECT quantity FROM user_products WHERE user_id = :user_id AND product_id = :product_id"
            );
            $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int) ($row['quantity'] ?? 0);
        } catch (\Throwable) {
            return 0;
        }
    }

    // TODO (PSR-1 §4.3): метод showCart() не имеет return type — нужно добавить: array|null
    public function showCart()
    {
        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }

        $userId = $this->authService->getCurrentUserId();
        if ($userId === 0) {
            return ['redirect' => '/login'];
        }

        $this->authService->startSession();
        $cartItems = $this->cartService->getUserProducts();
        $cartTotal = $this->cartService->sumUserProductLines($cartItems);
        $cartMessage = $_SESSION['cart_message'] ?? null;
        unset($_SESSION['cart_message']);

        require_once __DIR__ . '/../Views/cart.php';
    }

    // PSR-12 §4.4: открывающая { должна быть на следующей строке; PSR-1 §4.3: нет return type
    public function handleAddToCart(AddProductRequest $request) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['redirect' => '/catalog'];
        }
        $this->authService->startSession();

        if (!$this->authService->check()) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Необходимо войти в систему для добавления товаров в корзину',
                    'redirect' => '/login',
                ], 401);
            }
            $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Необходимо войти в систему для добавления товаров в корзину'];
            return ['redirect' => '/login'];
        }

        $userId = $this->authService->getCurrentUserId();
        if ($userId === 0) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Ошибка авторизации',
                    'redirect' => '/login',
                ], 401);
            }
            $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Ошибка авторизации'];
            return ['redirect' => '/login'];
        }

        $productId = $request->getProductId();
        $quantity = $request->getQuantity();
        $errors = $request->validate();

        if (empty($errors)) {
            $result = $this->cartService->addToCart((int) $userId, $productId, $quantity);

            if ($result['success'] ?? false) {
                if ($this->isAjaxRequest()) {
                    $this->cartModel->loadByUserId((int) $userId);
                    $this->json([
                        'success' => true,
                        'message' => $result['message'] ?? 'Товар добавлен в корзину',
                        'cartCount' => $this->cartModel->getItemsCount(),
                        'cartTotal' => $this->cartModel->getTotal(),
                        'quantity' => $this->getUserProductQuantity((int) $userId, (int) $productId),
                    ]);
                }
                $_SESSION['cart_message'] = [
                    'type' => 'success',
                    'text' => $result['message'] ?? 'Товар добавлен в корзину'
                ];
            } else {
                if ($this->isAjaxRequest()) {
                    $this->json([
                        'success' => false,
                        'message' => $result['message'] ?? 'Ошибка добавления товара в корзину',
                    ], 400);
                }
                $_SESSION['cart_message'] = [
                    'type' => 'error',
                    'text' => $result['message'] ?? 'Ошибка добавления товара в корзину'
                ];
            }
        } else {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => implode(', ', $errors),
                    'errors' => $errors,
                ], 422);
            }
            // Сохраняем ошибки валидации
            $_SESSION['cart_message'] = [
                'type' => 'error',
                'text' => implode(', ', $errors)
            ];
        }

        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/catalog'; // редирект
        if (filter_var($redirectUrl, FILTER_VALIDATE_URL) === false) {
            $redirectUrl = '/catalog';
        }

        return ['redirect' => $redirectUrl];
    }

    // PSR-12 §4.4: открывающая { должна быть на следующей строке; PSR-1 §4.3: нет return type
    public function handleUpdateCart() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['redirect' => '/cart'];
        }

        if (!$this->authService->check()) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Необходимо войти в систему',
                    'redirect' => '/login',
                ], 401);
            }
            return ['redirect' => '/login'];
        }

        $userId = $this->authService->getCurrentUserId();
        $productId = $_POST['product_id'] ?? null;
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

        $errors = [];
        if (empty($productId)) {
            $errors[] = 'Не указан товар';
        } else {
            $result = $this->cartService->updateCartItem((int) $userId, (int) $productId, $quantity);
            if (!($result['success'] ?? false)) {
                $errors[] = $result['message'] ?? 'Ошибка обновления количества';
            }
        }

        $this->authService->startSession();
        if (!empty($errors)) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => implode(', ', $errors),
                ], 400);
            }
            $_SESSION['cart_message'] = [
                'type' => 'error',
                'text' => implode(', ', $errors)
            ];
        } else {
            if ($this->isAjaxRequest()) {
                $this->cartModel->loadByUserId((int) $userId);
                $pid = (int) $productId;
                $this->json([
                    'success' => true,
                    'message' => $result['message'] ?? 'Количество товара обновлено',
                    'cartCount' => $this->cartModel->getItemsCount(),
                    'cartTotal' => $this->cartModel->getTotal(),
                    'quantity' => $this->getUserProductQuantity((int) $userId, $pid),
                    'productId' => $pid,
                ]);
            }
            $_SESSION['cart_message'] = [
                'type' => 'success',
                'text' => $result['message'] ?? 'Количество товара обновлено'
            ];
        }
        return ['redirect' => '/cart'];
    }

    // PSR-12 §4.4: открывающая { должна быть на следующей строке; PSR-1 §4.3: нет return type
    public function handleRemoveFromCart() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['redirect' => '/cart'];
        }

        if (!$this->authService->check()) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Необходимо войти в систему',
                    'redirect' => '/login',
                ], 401);
            }
            return ['redirect' => '/login'];
        }

        $userId = $this->authService->getCurrentUserId();
        $productId = $_POST['product_id'] ?? null;
        $errors = [];

        if (empty($productId)) {
            $errors[] = 'Не указан товар';
        } else {
            $result = $this->cartService->removeFromCart((int) $userId, (int) $productId);
            if (!($result['success'] ?? false)) {
                $errors[] = $result['message'] ?? 'Ошибка удаления товара из корзины';
            }
        }

        $this->authService->startSession();
        if (!empty($errors)) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => implode(', ', $errors),
                ], 400);
            }
            $_SESSION['cart_message'] = [
                'type' => 'error',
                'text' => implode(', ', $errors)
            ];
        } else {
            if ($this->isAjaxRequest()) {
                $this->cartModel->loadByUserId((int) $userId);
                $this->json([
                    'success' => true,
                    'message' => $result['message'] ?? 'Товар успешно удален из корзины',
                    'cartCount' => $this->cartModel->getItemsCount(),
                    'cartTotal' => $this->cartModel->getTotal(),
                ]);
            }
            $_SESSION['cart_message'] = [
                'type' => 'success',
                'text' => $result['message'] ?? 'Товар успешно удален из корзины'
            ];
        }

        return ['redirect' => '/cart'];
    }
}