<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background-color: #f5f5f5; padding: 20px; }
        .checkout-container { max-width: 900px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
        .checkout-header { margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .checkout-header h1 { color: #333; margin-bottom: 10px; }
        .message { padding: 12px 15px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; }
        .message-success { background-color: #e8f5e9; color: #4CAF50; border-left: 3px solid #4CAF50; }
        .message-error { background-color: #ffebee; color: #f44336; border-left: 3px solid #f44336; }
        .form-section { margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        .form-group textarea { min-height: 80px; resize: vertical; }
        .form-group .error { color: #f44336; font-size: 13px; margin-top: 4px; }
        .order-summary { background: #f9f9f9; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
        .order-summary h3 { margin-bottom: 15px; color: #333; }
        .summary-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .summary-item:last-child { border-bottom: none; }
        .summary-total { font-size: 18px; font-weight: bold; color: #333; padding-top: 10px; margin-top: 10px; border-top: 2px solid #ddd; }
        .btn { padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; transition: background 0.3s; }
        .btn-back { background: #757575; color: white; margin-right: 10px; }
        .btn-back:hover { background: #616161; }
        .btn-submit { background: #32CD32; color: white; }
        .btn-submit:hover { background: #28a428; }
        .navbar { background: white; padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 30px; border-radius: 8px; max-width: 900px; margin-left: auto; margin-right: auto; display: flex; justify-content: space-between; align-items: center; }
        .navbar h2 { color: #333; font-size: 20px; }
        .navbar-links { display: flex; gap: 15px; align-items: center; }
        .navbar-links a { color: #333; text-decoration: none; padding: 8px 15px; border-radius: 5px; transition: background 0.3s; font-size: 14px; }
        .navbar-links a:hover { background: #f0f0f0; }
        .navbar-links .catalog-link { background: #2196F3; color: white; }
        .navbar-links .catalog-link:hover { background: #0b7dda; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>Онлайн магазин</h2>
    <div class="navbar-links">
        <a href="/catalog" class="catalog-link">Каталог</a>
        <a href="/profile">Профиль</a>
        <a href="/cart">Корзина</a>
        <a href="/orders">Мои заказы</a>
    </div>
</div>

<div class="checkout-container">
    <div class="checkout-header">
        <h1>Оформление заказа</h1>
        <?php if (!empty($checkoutMessage)): ?>
            <div class="message message-<?php echo $checkoutMessage['type']; ?>">
                <?php echo htmlspecialchars($checkoutMessage['text']); ?>
            </div>
        <?php endif; ?>
    </div>

    <form method="POST" action="/checkout">
        <div class="form-section">
            <div class="form-group">
                <label for="name">Имя *</label>
                <input type="text" id="name" name="name" required
                       value="<?php echo htmlspecialchars($checkoutForm['name'] ?? ''); ?>"
                       placeholder="Ваше имя">
                <?php if (!empty($checkoutErrors['name'])): ?>
                    <span class="error"><?php echo htmlspecialchars($checkoutErrors['name']); ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="address">Адрес доставки *</label>
                <input type="text" id="address" name="address" required
                       value="<?php echo htmlspecialchars($checkoutForm['address'] ?? ''); ?>"
                       placeholder="Город, улица, дом, квартира">
                <?php if (!empty($checkoutErrors['address'])): ?>
                    <span class="error"><?php echo htmlspecialchars($checkoutErrors['address']); ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="phone">Телефон *</label>
                <input type="text" id="phone" name="phone" required
                       value="<?php echo htmlspecialchars($checkoutForm['phone'] ?? ''); ?>"
                       placeholder="+7 (999) 123-45-67">
                <?php if (!empty($checkoutErrors['phone'])): ?>
                    <span class="error"><?php echo htmlspecialchars($checkoutErrors['phone']); ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="comment">Комментарий к заказу</label>
                <textarea id="comment" name="comment" placeholder="Пожелания по доставке"><?php echo htmlspecialchars($checkoutForm['comment'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="order-summary">
            <h3>Ваш заказ</h3>
            <?php foreach ($cartItems as $item): ?>
                <div class="summary-item">
                    <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo (int) $item['quantity']; ?></span>
                    <span><?php echo number_format($item['item_total'], 2, '.', ' '); ?> ₽</span>
                </div>
            <?php endforeach; ?>
            <div class="summary-total">Итого: <?php echo number_format($cartTotal, 2, '.', ' '); ?> ₽</div>
        </div>

        <div class="form-section">
            <a href="/cart" class="btn btn-back">Вернуться в корзину</a>
            <button type="submit" class="btn btn-submit">Подтвердить заказ</button>
        </div>
    </form>
</div>
</body>
</html>
