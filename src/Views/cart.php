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
            background: #32CD32;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .cart-empty a:hover {
            background: #32CD32;
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
            background: #32CD32;
            color: white;
        }

        .btn-checkout:hover {
            background: #32CD32;
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

<div class="navbar">
    <h2>Онлайн магазин</h2>
    <div class="navbar-links">
        <a href="/catalog" class="catalog-link">Каталог</a>
        <a href="/profile">Профиль</a>
        <a href="/cart">Корзина</a>
        <a href="/orders">Мои заказы</a>
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
                <?php
                $product = $item->getProduct();
                if ($product === null) {
                    continue;
                }
                $pid = $item->getProductId();
                $lineTotal = $item->getTotalSum() ?? 0.0;
                $img = $product->getImageUrl() ?: 'images/placeholder.jpg';
                ?>
                <div class="cart-item" data-product-id="<?php echo (int) $pid; ?>" data-line-total="<?php echo (float) $lineTotal; ?>"  data-price="<?php echo (float) $product->getPrice(); ?>">
                    <img src="<?php echo htmlspecialchars($img); ?>"
                         alt="<?php echo htmlspecialchars((string) $product->getName()); ?>"
                         class="item-image">

                    <div class="item-info">
                        <div class="item-name"><?php echo htmlspecialchars((string) $product->getName()); ?></div>
                        <div class="item-price"><?php echo number_format((float) $product->getPrice(), 2, '.', ' '); ?> ₽</div>
                    </div>

                    <div class="item-quantity">
                        <button type="button" class="quantity-btn"
                                data-action="dec"
                                data-product-id="<?php echo (int) $pid; ?>">-</button>
                        <input type="number"
                               name="quantity"
                               value="<?php echo (int) $item->getAmount(); ?>"
                               min="1"
                               class="quantity-input"
                               data-product-id="<?php echo (int) $pid; ?>">
                        <button type="button" class="quantity-btn"
                                data-action="inc"
                                data-product-id="<?php echo (int) $pid; ?>">+</button>
                    </div>

                    <div class="item-total" data-role="line-total">
                        <?php echo number_format($lineTotal, 2, '.', ' '); ?> ₽
                    </div>

                    <form action="/remove-from-cart" method="POST" class="js-remove-from-cart" style="display: inline;">
                        <input type="hidden" name="product_id" value="<?php echo (int) $pid; ?>">
                        <button type="submit" class="item-remove">Удалить</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <div class="cart-total">
                <span class="cart-total-label">Итого:</span>
                <span id="cartTotalValue"><?php echo number_format($cartTotal, 2, '.', ' '); ?> ₽</span>
            </div>
            <div class="cart-actions">
                <a href="/catalog" class="btn btn-continue">Продолжить покупки</a>
                <a href="/checkout" class="btn btn-checkout">Оформить заказ</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function formatRub(amount) {
        const n = Number(amount || 0);
        return n.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽';
    }

    async function ajaxPost(url, params) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(params)
        });
        const data = await res.json();
        return { ok: res.ok, data };
    }

    function updateCartTotal(cartTotal) {
        const totalEl = document.getElementById('cartTotalValue');
        if (totalEl) totalEl.textContent = formatRub(cartTotal);
    }

    async function removeItem(productId, itemEl) {
        const { ok, data } = await ajaxPost('/remove-from-cart', { product_id: String(productId) });
        if (!ok || !data.success) {
            if (data.redirect) { window.location.href = data.redirect; return; }
            alert(data.message || 'Ошибка');
            return;
        }
        if (itemEl) itemEl.remove();
        updateCartTotal(data.cartTotal);
        if (Number(data.cartCount || 0) === 0) {
            window.location.reload();
        }
    }

    async function updateQuantity(productId, quantity, itemEl, qtyInput) {
        const prev = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;
        const { ok, data } = await ajaxPost('/update-cart', {
            product_id: String(productId),
            quantity: String(quantity)
        });
        if (!ok || !data.success) {
            if (qtyInput) qtyInput.value = String(prev);
            if (data.redirect) { window.location.href = data.redirect; return; }
            alert(data.message || 'Ошибка');
            return;
        }
        if (qtyInput) qtyInput.value = String(data.quantity || quantity);
        const price = parseFloat(itemEl ? (itemEl.dataset.price || '0') : '0');
        const lineTotalEl = itemEl ? itemEl.querySelector('[data-role="line-total"]') : null;
        if (lineTotalEl) lineTotalEl.textContent = formatRub(price * (data.quantity || quantity));
        updateCartTotal(data.cartTotal);
    }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('button.quantity-btn[data-action]');
        if (!btn) return;
        const productId = btn.dataset.productId;
        if (!productId) return;
        const itemEl = btn.closest('.cart-item');
        const qtyInput = itemEl ? itemEl.querySelector('input[name="quantity"]') : null;
        const current = parseInt(qtyInput ? qtyInput.value : '1', 10) || 1;
        const next = btn.dataset.action === 'inc' ? current + 1 : current - 1;

        btn.disabled = true;
        try {
            if (next <= 0) {
                if (!confirm('Удалить товар из корзины?')) return;
                await removeItem(productId, itemEl);
            } else {
                if (qtyInput) qtyInput.value = String(next);
                await updateQuantity(productId, next, itemEl, qtyInput);
            }
        } catch (err) {
            if (qtyInput) qtyInput.value = String(current);
            alert('Ошибка сети');
        } finally {
            btn.disabled = false;
        }
    });

    let inputTimer = null;
    document.addEventListener('change', async (e) => {
        const input = e.target.closest('input.quantity-input[data-product-id]');
        if (!input) return;
        clearTimeout(inputTimer);
        inputTimer = setTimeout(async () => {
            const productId = input.dataset.productId;
            const quantity = parseInt(input.value, 10);
            const itemEl = input.closest('.cart-item');
            if (!productId) return;
            if (isNaN(quantity) || quantity < 1) {
                input.value = '1';
                return;
            }
            try {
                await updateQuantity(productId, quantity, itemEl, input);
            } catch (err) {
                alert('Ошибка сети');
            }
        }, 300);
    });

    document.querySelectorAll('form.js-remove-from-cart').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!confirm('Удалить товар из корзины?')) return;
            const btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
            const productIdInput = form.querySelector('input[name="product_id"]');
            const productId = productIdInput ? productIdInput.value : null;
            const itemEl = form.closest('.cart-item');
            try {
                if (productId) await removeItem(productId, itemEl);
            } catch (err) {
                alert('Ошибка сети');
            } finally {
                if (btn) btn.disabled = false;
            }
        });
    });
</script>
</body>
</html>
