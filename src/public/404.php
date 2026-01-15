<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Ошибка</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
            text-align: center;
            padding: 20px;
        }

        .content {
            max-width: 500px;
        }

        .animation {
            position: relative;
            height: 200px;
            margin-bottom: 40px;
        }

        .ghost {
            width: 120px;
            height: 150px;
            background: white;
            border-radius: 60px 60px 0 0;
            position: relative;
            margin: 0 auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            animation: float 3s ease-in-out infinite;
        }

        .ghost:before, .ghost:after {
            content: '';
            position: absolute;
            background: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            bottom: -12px;
        }

        .ghost:before {
            left: 30px;
        }

        .ghost:after {
            right: 30px;
        }

        .eyes {
            display: flex;
            justify-content: center;
            gap: 30px;
            padding-top: 40px;
        }

        .eye {
            width: 25px;
            height: 25px;
            background: #333;
            border-radius: 50%;
            position: relative;
        }

        .eye:before {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
            top: 5px;
            left: 5px;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        p {
            font-size: 18px;
            margin-bottom: 30px;
            color: #7f8c8d;
            line-height: 1.6;
        }

        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #3498db;
            color: white;
            border: 2px solid #3498db;
        }

        .btn-primary:hover {
            background: #2980b9;
            border-color: #2980b9;
        }

        .btn-secondary {
            background: transparent;
            color: #3498db;
            border: 2px solid #3498db;
        }

        .btn-secondary:hover {
            background: #3498db;
            color: white;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        @media (max-width: 480px) {
            h1 { font-size: 28px; }
            p { font-size: 16px; }
            .buttons { flex-direction: column; }
            .btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
<div class="content">
    <div class="animation">
        <div class="ghost">
            <div class="eyes">
                <div class="eye"></div>
                <div class="eye"></div>
            </div>
        </div>
    </div>
    <h1>Упс! Страница пропала</h1>
    <p>
        Кажется, мы не можем найти страницу, которую вы ищете.
        Возможно, она была перемещена или удалена.
    </p>
</div>
</body>
</html>