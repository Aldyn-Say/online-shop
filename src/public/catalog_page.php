
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
    </style>
</head>
<body>
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
            <button class="buy-btn" data-id="<?php echo $product['id'] ?? '' ?>">Купить</button>
        </div>
    <?php endforeach; ?>
</div>

<script>
    // Простая функция при клике на кнопку "Купить"
    document.querySelectorAll('.buy-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productElement = this.parentElement;
            const productName = productElement.querySelector('.product-title').textContent;
            const productPrice = productElement.querySelector('.product-price').textContent;
            const productId = this.getAttribute('data-id');

            alert(`Вы добавили в корзину: ${productName}\nЦена: ${productPrice}\nID товара: ${productId || 'не указан'}`);

            // Здесь можно добавить AJAX запрос для добавления в корзину
            // Например: addToCart(productId);
        });
    });
</script>
</body>
</html>