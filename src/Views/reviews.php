<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product->getName() ?? 'Товар'); ?> — отзывы</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .product-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .product-info .product-img {
            width: 220px;
            height: 200px;
            background-color: #e0e0e0;
            border-radius: 5px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .product-info .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .product-info h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .product-info p {
            color: #666;
            line-height: 1.6;
        }

        .price {
            font-size: 24px;
            color: #27ae60;
            font-weight: bold;
            margin-top: 10px;
        }

        .reviews-section {
            margin-bottom: 40px;
        }

        .reviews-section h3 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .review {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .review-author {
            font-weight: bold;
            color: #2c3e50;
        }

        .review-rating {
            color: #f39c12;
        }

        .review-text {
            color: #555;
            line-height: 1.6;
        }

        .review-form {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
        }

        .review-form h3 {
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #3498db;
        }

        button {
            background-color: #3498db;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        .required {
            color: #e74c3c;
            margin-left: 3px;
        }
    </style>
</head>
<body>
<div class="container">
    <p style="margin-bottom: 15px;"><a href="/catalog" style="color: #3498db;">← Вернуться в каталог</a></p>

    <h1><?php echo htmlspecialchars($product->getName() ?? 'Товар'); ?></h1>

    <div class="product-info">
        <?php if ($product->getImageUrl()): ?>
            <div class="product-img">
                <img src="<?php echo htmlspecialchars($product->getImageUrl()); ?>" alt="<?php echo htmlspecialchars($product->getName()); ?>">
            </div>
        <?php endif; ?>
        <h2><?php echo htmlspecialchars($product->getName()); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($product->getDescription() ?? '')); ?></p>
        <div class="price"><?php echo htmlspecialchars($product->getPrice()); ?> ₽</div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="message message--<?php echo htmlspecialchars($message['type']); ?>" style="padding: 12px; border-radius: 8px; margin-bottom: 20px; background: <?php echo $message['type'] === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message['type'] === 'success' ? '#155724' : '#721c24'; ?>;">
            <?php echo htmlspecialchars($message['text']); ?>
        </div>
    <?php endif; ?>

    <div class="reviews-section">
        <h3>Отзывы (<?php echo count($reviews ?? []); ?>)</h3>
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <div class="review-header">
                        <span class="review-author"><?php echo htmlspecialchars($review->getUserName()); ?></span>
                        <span class="review-rating"><?php echo str_repeat('★', (int)$review->getRating()) . str_repeat('☆', 5 - (int)$review->getRating()); ?> (<?php echo (int)$review->getRating(); ?>)</span>
                    </div>
                    <?php if ($review->getCreatedAt()): ?>
                        <div style="font-size: 12px; color: #999; margin-bottom: 8px;"><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($review->getCreatedAt()))); ?></div>
                    <?php endif; ?>
                    <div class="review-text"><?php echo nl2br(htmlspecialchars($review->getComment() ?? '')); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #777;">Пока нет отзывов. Будьте первым!</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($canReview)): ?>
        <div class="review-form">
            <h3>Оставить отзыв</h3>
            <form action="/add-review" method="post">
                <input type="hidden" name="product_id" value="<?php echo (int)$product->getId(); ?>">
                <div class="form-group">
                    <label for="rating">Оценка <span class="required">*</span></label>
                    <select id="rating" name="rating" required>
                        <option value="">Выберите оценку</option>
                        <option value="5">★★★★★ Отлично</option>
                        <option value="4">★★★★☆ Хорошо</option>
                        <option value="3">★★★☆☆ Нормально</option>
                        <option value="2">★★☆☆☆ Плохо</option>
                        <option value="1">★☆☆☆☆ Ужасно</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="comment">Ваш отзыв</label>
                    <textarea id="comment" name="comment" placeholder="Расскажите о своём опыте использования товара..."></textarea>
                </div>
                <button type="submit">Отправить отзыв</button>
            </form>
        </div>
    <?php elseif (!empty($loggedIn)): ?>
        <p style="color: #777;">Вы уже оставили отзыв на этот товар.</p>
    <?php else: ?>
        <p style="color: #777;"><a href="/login" style="color: #3498db;">Войдите</a>, чтобы оставить отзыв.</p>
    <?php endif; ?>
</div>
</body>
</html>