<?php
session_start();
$title = 'Корзина покупок';
include 'catalog_page.php';
?>

<div class="container">
    <h1>Корзина покупок</h1>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-info">
            Ваша корзина пуста. <a href="products.php">Перейти к покупкам</a>
        </div>
    <?php else: ?>
        <div class="cart-table">
            <table class="table">
                <thead>
                <tr>
                    <th>Товар</th>
                    <th>Название</th>
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Сумма</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $item):
                    $item_total = $item['price'] * $item['quantity'];
                    $total += $item_total;
                    ?>
                    <tr>
                        <td>
                            <?php if ($item['image']): ?>
                                <img src="images/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" width="50">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= number_format($item['price'], 2) ?> ₽</td>
                        <td>
                            <form method="POST" action="update_cart.php" class="d-inline">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>"
                                       min="1" max="99" style="width: 70px;">
                                <button type="submit" class="btn btn-sm btn-primary">Обновить</button>
                            </form>
                        </td>
                        <td><?= number_format($item_total, 2) ?> ₽</td>
                        <td>
                            <form method="POST" action="remove_from_cart.php">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="4" class="text-end"><strong>Итого:</strong></td>
                    <td><strong><?= number_format($total, 2) ?> ₽</strong></td>
                    <td></td>
                </tr>
                </tfoot>
            </table>

            <div class="text-end mt-4">
                <a href="products.php" class="btn btn-secondary">Продолжить покупки</a>
                <a href="checkout.php" class="btn btn-success">Оформить заказ</a>
                <form method="POST" action="clear_cart.php" class="d-inline">
                    <button type="submit" class="btn btn-outline-danger">Очистить корзину</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
