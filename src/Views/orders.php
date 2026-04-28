<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заказы</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #F5E6D3;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        h2 {
            color: #3A281C;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #D4A373;
        }

        .order-card {
            background: #FFF8E7;
            border: 1px solid #D4A373;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #D4A373;
        }

        .order-number {
            font-weight: bold;
            color: #3A281C;
        }

        .order-status {
            color: #8B5A2B;
        }

        .field {
            margin-bottom: 12px;
        }

        .field-label {
            font-size: 14px;
            color: #8B5A2B;
            margin-bottom: 3px;
        }

        .field-value {
            color: #3A281C;
            padding: 8px;
            background: #F5E6D3;
            border-radius: 5px;
        }

        .products-list {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #D4A373;
        }

        .products-title {
            font-size: 14px;
            color: #8B5A2B;
            margin-bottom: 10px;
        }

        .product-item {
            padding: 5px 0;
            color: #3A281C;
            font-size: 14px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #8B5A2B;
        }

        .empty {
            text-align: center;
            padding: 40px;
            background: #FFF8E7;
            border: 1px dashed #D4A373;
            border-radius: 10px;
            color: #8B5A2B;
        }
            .navbar { background: #FFF8E7; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #D4A373; display: flex; gap: 15px; }
        .navbar a { color: #8B5A2B; text-decoration: none; }
        .navbar a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <div class="navbar">
        <a href="/catalog">Каталог</a>
        <a href="/cart">Корзина</a>
        <a href="/profile">Профиль</a>
    </div>
    <h2>Мои заказы</h2>

    <?php if (!empty($orderSuccess)): ?>
        <div class="message-success" style="background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($orderSuccess); ?>
        </div>
    <?php endif; ?>

    <!-- Контейнер для заказов из orders + order_products -->
    <div id="ordersContainer">
        <?php if (empty($orders)): ?>
            <div class="empty">У вас пока нет заказов</div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-number">Заказ №<?php echo $order->getId(); ?></span>
                        <span class="order-status"><?php echo htmlspecialchars($order->getStatus() ?? 'В обработке'); ?></span>
                    </div>

                    <div class="field">
                        <div class="field-label">Имя</div>
                        <div class="field-value"><?php echo htmlspecialchars($order->getName() ?? 'Не указано'); ?></div>
                    </div>

                    <div class="field">
                        <div class="field-label">Телефон</div>
                        <div class="field-value"><?php echo htmlspecialchars($order->getPhone() ?? 'Не указан'); ?></div>
                    </div>

                    <div class="field">
                        <div class="field-label">Адрес доставки</div>
                        <div class="field-value"><?php echo htmlspecialchars($order->getAddress() ?? 'Не указан'); ?></div>
                    </div>

                    <div class="field">
                        <div class="field-label">Комментарий к заказу</div>
                        <div class="field-value"><?php echo htmlspecialchars($order->getComment() ?: 'Нет комментария'); ?></div>
                    </div>

                    <div class="products-list">
                        <div class="products-title">Товары в заказе (order_products):</div>
                        <?php $orderProducts = $order->getProducts(); if (empty($orderProducts)): ?>
                            <div class="product-item">Нет товаров</div>
                        <?php else: ?>
                            <?php foreach ($orderProducts as $line): ?>
                                <div class="product-item">
                                    • <?php echo htmlspecialchars((string) ($line->getProductName() ?? '')); ?> — <?php echo number_format((float) ($line->getProductPrice() ?? 0), 2, '.', ' '); ?> ₽ × <?php echo $line->getAmount(); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>