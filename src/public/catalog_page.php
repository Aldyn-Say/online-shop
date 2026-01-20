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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .header {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #2c3e50;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .nav-item:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        .nav-item.active {
            background: #3498db;
            color: white;
        }

        .cart-badge {
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .username {
            color: #2c3e50;
            font-weight: 500;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
            font-size: 2.5rem;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            max-width: 600px;
            margin: 0 auto 30px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .catalog {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .product {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .product:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .product-img {
            width: 100%;
            height: 200px;
            border-radius: 10px;
            margin-bottom: 15px;
            overflow: hidden;
            background: #f8f9fa;
        }

        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .product:hover .product-img img {
            transform: scale(1.05);
        }

        .product-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c3e50;
            line-height: 1.3;
        }

        .product-price {
            color: #27ae60;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .product-description {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.5;
            flex-grow: 1;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            border: none;
            background: #3498db;
            color: white;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            transition: background 0.3s;
        }

        .quantity-btn:hover {
            background: #2980b9;
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
        }

        .buy-btn {
            background: linear-gradient(135deg, #27ae60 0%, #219653 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .buy-btn:hover {
            background: linear-gradient(135deg, #219653 0%, #1e8449 100%);
            transform: translateY(-2px);
        }

        .cart-icon {
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }

            .catalog {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .catalog {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<!-- Шапка с навигацией -->
<div class="header">
    <a href="/" class="logo">🛍️ Магазин</a>

    <div class="nav-menu">
        <a href="/catalog" class="nav-item active">🏪 Каталог</a>
        <a href="/cart" class="nav-item">
            🛒 Корзина
            <?php if ($cartCount > 0): ?>
                <span class="cart-badge"><?php echo $cartCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="/profile" class="nav-item">👤 Личный кабинет</a>
    </div>

    <div class="user-info">
        <span class="username">Привет, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
        <form action="/logout" method="POST" style="display: inline;">
            <button type="submit" class="logout-btn">Выход</button>
        </form>
    </div>
</div>

<div class="container">
    <!-- Сообщения -->
    <?php if (isset($cartMessage) && !empty($cartMessage)): ?>
        <div class="message <?php echo strpos($cartMessage, 'успешно') !== false ? 'success-message' : 'error-message'; ?>">
            <?php echo htmlspecialchars($cartMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <h1>Каталог товаров</h1>

    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 50px;">
            <h2>Товары не найдены</h2>
            <p>Попробуйте позже</p>
        </div>
    <?php else: ?>
        <div class="catalog">
            <?php foreach ($products as $product): ?>
                <div class="product" id="product-<?php echo $product['id']; ?>">
                    <div class="product-img">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? '/images/default.jpg'); ?>"
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>

                    <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>

                    <div class="product-price">
                        <?php echo number_format($product['price'], 2, '.', ' '); ?> ₽
                    </div>

                    <div class="product-description">
                        <?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 150)); ?>
                        <?php if (strlen($product['description'] ?? '') > 150): ?>...<?php endif; ?>
                    </div>

                    <!-- Форма для добавления в корзину -->
                    <form action="/add_to_cart.php" method="POST" class="add-to-cart-form"
                          data-product-id="<?php echo $product['id']; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn decrement"
                                    onclick="decrementQuantity(<?php echo $product['id']; ?>)">-</button>

                            <input type="number" name="quantity" value="1" min="1" max="99"
                                   class="quantity-input" id="quantity-<?php echo $product['id']; ?>">

                            <button type="button" class="quantity-btn increment"
                                    onclick="incrementQuantity(<?php echo $product['id']; ?>)">+</button>
                        </div>

                        <button type="submit" class="buy-btn">
                            <span class="cart-icon">🛒</span>
                            Добавить в корзину
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>