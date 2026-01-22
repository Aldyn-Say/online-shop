
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            padding: 20px;
            background-color: #f5f5f5;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .catalog {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .product:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-img {
            width: 100%;
            height: 200px;
            background-color: #e0e0e0;
            border-radius: 5px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .product-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .product-price {
            color: #D1001C;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .product-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .buy-btn {
            background-color: #7B3F00;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .buy-btn:hover {
            background-color: #45a049;
        }

        .navbar {
            background: white;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-radius: 8px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            color: #333;
            font-size: 20px;
        }

        .navbar-links {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .navbar-links a {
            color: #333;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
            font-size: 14px;
            position: relative;
        }

        .navbar-links a:hover {
            background: #f0f0f0;
        }

        .navbar-links .cart-link {
            background: #4CAF50;
            color: white;
        }

        .navbar-links .cart-link:hover {
            background: #45a049;
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #f44336;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .message {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            font-size: 14px;
        }

        .message-success {
            background-color: #e8f5e9;
            color: #4CAF50;
            border-left: 3px solid #4CAF50;
        }

        .message-error {
            background-color: #ffebee;
            color: #f44336;
            border-left: 3px solid #f44336;
        }
    </style>
</head>
<body>
<?php
if (!isset($_SESSION)) {
    session_start();
}
$userName = $_SESSION['user_name'] ?? 'Пользователь';
$cartMessage = $_SESSION['cart_message'] ?? null;
unset($_SESSION['cart_message']);

// Получаем количество товаров в корзине
$cartCount = 0;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id'])) {
    require_once 'Cart.php';
    $cart = new Cart();
    $cartCount = $cart->getCartUniqueItemsCount($_SESSION['user_id']);
}
?>
<div class="navbar">
    <h2>Онлайн магазин</h2>
    <div class="navbar-links">
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <span style="color: #666; font-size: 14px;">Привет, <?php echo htmlspecialchars($userName); ?>!</span>
            <a href="/catalog">Каталог</a>
            <a href="/profile">Профиль</a>
            <a href="/cart" class="cart-link" style="position: relative;">
                Корзина
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="/logout">Выйти</a>
        <?php else: ?>
            <a href="/login">Войти</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($cartMessage): ?>
    <div class="message message-<?php echo $cartMessage['type']; ?>">
        <?php echo htmlspecialchars($cartMessage['text']); ?>
    </div>
<?php endif; ?>

<h1>Каталог товаров</h1>

<div class="catalog">
    <?php foreach ($products as $product) : ?>
        <div class="product">
            <div class="product-img">
                <img src="<?php echo $product['image_url'] ?>" alt="<?php echo $product['name'] ?>">
            </div>
            <div class="product-title"><?php echo $product['name'] ?></div>
            <div class="product-price"><?php echo $product['price'] ?> ₽</div>
            <div class="product-description"><?php echo $product['description'] ?></div>
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <form action="/add-to-cart" method="POST" style="margin: 0;">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="buy-btn">В корзину</button>
                </form>
            <?php else: ?>
                <a href="/login" class="buy-btn" style="display: block; text-align: center; text-decoration: none; line-height: 38px;">В корзину</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>