<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            padding: 20px;
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .cart-header {
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }

        .cart-header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .message {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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

        .cart-empty {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .cart-empty h2 {
            margin-bottom: 15px;
            color: #333;
        }

        .cart-empty a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .cart-empty a:hover {
            background: #45a049;
        }

        .cart-items {
            margin-bottom: 30px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
            gap: 20px;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            background: #f0f0f0;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .item-price {
            color: #D1001C;
            font-size: 16px;
            font-weight: bold;
        }

        .item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            font-size: 16px;
        }

        .quantity-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        .quantity-btn:hover {
            background: #f0f0f0;
        }

        .item-total {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            min-width: 120px;
            text-align: right;
        }

        .item-remove {
            padding: 8px 15px;
            background: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        .item-remove:hover {
            background: #d32f2f;
        }

        .cart-summary {
            border-top: 2px solid #eee;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-total {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .cart-total-label {
            font-size: 18px;
            color: #666;
            margin-right: 10px;
        }

        .cart-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }

        .btn-continue {
            background: #757575;
            color: white;
        }

        .btn-continue:hover {
            background: #616161;
        }

        .btn-checkout {
            background: #4CAF50;
            color: white;
        }

        .btn-checkout:hover {
            background: #45a049;
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
        }

        .navbar-links a:hover {
            background: #f0f0f0;
        }

        .navbar-links .catalog-link {
            background: #2196F3;
            color: white;
        }

        .navbar-links .catalog-link:hover {
            background: #0b7dda;
        }

        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .item-total {
                text-align: left;
                min-width: auto;
            }

            .cart-summary {
                flex-direction: column;
                gap: 20px;
                align-items: stretch;
            }

            .cart-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<?php
if (!isset($_SESSION)) {
    session_start();
}
$userName = $_SESSION['user_name'] ?? 'Пользователь';
?>
<div class="navbar">
    <h2>Онлайн магазин</h2>
    <div class="navbar-links">
        <a href="/catalog" class="catalog-link">Каталог</a>
        <a href="/profile">Профиль</a>
        <a href="/cart">Корзина</a>
    </div>
</div>
<div class="cart-container">
    <div class="cart-header">
        <h1>Корзина</h1>
        <?php if ($cartMessage): ?>
            <div class="message message-<?php echo $cartMessage['type']; ?>">
                <?php echo htmlspecialchars($cartMessage['text']); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="cart-empty">
            <h2>Ваша корзина пуста</h2>
            <p>Добавьте товары из каталога</p>
            <a href="/catalog">Перейти в каталог</a>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'images/placeholder.jpg'); ?>"
                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                         class="item-image">

                    <div class="item-info">
                        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="item-price"><?php echo number_format($item['price'], 2, '.', ' '); ?> ₽</div>
                    </div>

                    <div class="item-quantity">
                        <form action="/update-cart" method="POST" style="display: flex; align-items: center; gap: 10px;">
                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                            <button type="button" class="quantity-btn" onclick="decreaseQuantity(<?php echo $item['product_id']; ?>)">-</button>
                            <input type="number"
                                   name="quantity"
                                   value="<?php echo $item['quantity']; ?>"
                                   min="1"
                                   class="quantity-input"
                                   onchange="this.form.submit()">
                            <button type="button" class="quantity-btn" onclick="increaseQuantity(<?php echo $item['product_id']; ?>)">+</button>
                        </form>
                    </div>

                    <div class="item-total">
                        <?php echo number_format($item['item_total'], 2, '.', ' '); ?> ₽
                    </div>

                    <form action="/remove-from-cart" method="POST" style="display: inline;">
                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                        <button type="submit" class="item-remove" onclick="return confirm('Удалить товар из корзины?')">Удалить</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <div class="cart-total">
                <span class="cart-total-label">Итого:</span>
                <?php echo number_format($cartTotal, 2, '.', ' '); ?> ₽
            </div>
            <div class="cart-actions">
                <a href="/catalog" class="btn btn-continue">Продолжить покупки</a>
                <button class="btn btn-checkout" onclick="alert('Функция оформления заказа будет добавлена позже')">Оформить заказ</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function increaseQuantity(productId) {
        const form = event.target.closest('form');
        const input = form.querySelector('input[name="quantity"]');
        input.value = parseInt(input.value) + 1;
        form.submit();
    }

    function decreaseQuantity(productId) {
        const form = event.target.closest('form');
        const input = form.querySelector('input[name="quantity"]');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
            form.submit();
        }
    }
</script>
</body>
</html>
