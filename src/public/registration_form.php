<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .registration-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .registration-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .registration-container {
                padding: 30px 20px;
            }

            .registration-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="registration-container">
    <h1 class="registration-title">Регистрация</h1>
    <form action="/registration" method="POST">
        <div class="form-group">
            <label for="name">Имя</label>
            <?php if (isset($errors['name'])): ?>
            <label style="color: brown" ><?php echo $errors['name']; ?></label>
            <?php endif; ?>
            <input type="text" id="name" name="name" placeholder="Введите ваше имя">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <?php if (isset($errors['email'])): ?>
            <label style="color: brown" ><?php echo $errors['email']; ?></label>
            <?php endif; ?>
            <input type="text" id="email" name="email" placeholder="Введите ваш email">
        </div>

        <div class="form-group">
            <label for="password">Пароль</label>
            <?php if (isset($errors['password'])): ?>
            <label style="color: brown" ><?php echo $errors['password']; ?></label>
            <?php endif; ?>
            <input type="password" id="password" name="password" placeholder="Введите пароль">
        </div>

        <div class="form-group">
            <label for="password_confirm">Подтвердите пароль</label>
            <?php if (isset($errors['password_confirm'])): ?>
                <label style="color: brown" ><?php echo $errors['password_confirm']; ?></label>
            <?php endif; ?>
            <input type="password" id="password_confirm" name="password_confirm" placeholder="Повторите пароль">
        </div>

        <button type="submit" class="submit-btn">Зарегистрироваться</button>
    </form>

    <div class="login-link">
        Уже есть аккаунт? <a href="/login">Войти</a>
    </div>
</div>
</body>
</html>
