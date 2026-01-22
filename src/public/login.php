<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #4A2C2A 50%, #7B3F10 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
        }

        .login-box {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .login-box:hover {
            transform: translateY(-5px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            color: #555;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            outline: none;
        }

        .input-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .input-group input::placeholder {
            color: #aaa;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #555;
        }

        .remember-me input {
            width: 16px;
            height: 16px;
        }

        .forgot-password {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3C2F2F 0%, #4A2C2A 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 14px;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .register-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
            display: none;
        }


        .social-login p {
            color: #666;
            margin-bottom: 15px;
            font-size: 14px;
            position: relative;
        }

        .social-login p::before,
        .social-login p::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 30%;
            height: 1px;
            background: #e1e1e1;
        }

        .social-login p::before {
            left: 0;
        }

        .social-login p::after {
            right: 0;
        }


        @media (max-width: 480px) {
            .login-box {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-box">
        <div class="login-header">
            <h1>Добро пожаловать</h1>
            <p>Введите ваши данные для входа</p>
        </div>

        <div class="error-message" id="errorMessage">
            Неверный email или пароль. Попробуйте снова.
        </div>

        <form id="loginForm" action="/login" method="POST">
            <div class="input-group">
                <label for="email">Email</label>
                <?php if (isset($errors['email'])): ?>
                    <label style="color: brown" ><?php echo $errors['email']; ?></label>
                <?php endif; ?>
                <input type="email" id="email" name="email" placeholder="Введите ваш email" required>
            </div>

            <div class="input-group">
                <label for="password">Пароль</label>
                <?php if (isset($errors['password'])): ?>
                    <label style="color: brown" ><?php echo $errors['password']; ?></label>
                <?php endif; ?>
                <input type="password" id="password" name="password" placeholder="Введите ваш пароль" required>
            </div>

            <div class="remember-forgot">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> Запомнить меня
                </label>
                <a href="#" class="forgot-password">Забыли пароль?</a>
            </div>

            <button type="submit" class="login-button">Войти</button>
        </form>

        <div class="register-link">
            Ещё нет аккаунта? <a href="#">Зарегистрироваться</a>
        </div>
    </div>
</div>

</body>
</html>