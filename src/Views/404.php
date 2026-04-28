<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Страница не найдена</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }
        .error-container {
            max-width: 600px;
            padding: 40px;
        }
        h1 {
            font-size: 72px;
            color: #333;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 24px;
            color: #666;
            margin-bottom: 30px;
        }
        a {
            display: inline-block;
            padding: 12px 30px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        a:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
<div class="error-container">
    <h1>404</h1>
    <h2>Страница не найдена</h2>
    <p>Запрашиваемая страница не существует.</p>
    <br>
    <a href="/">Вернуться на главную</a>
</div>
</body>
</html>


