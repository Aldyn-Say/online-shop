<?php
function validateUser($data) {
    $errors = [];

    // Проверка имени
    if (isset($data['name'])) {
        $name = trim($data["name"]);
        if (empty($name)) {
            $errors['name'] = 'Заполните поле Name';
        } elseif (strlen($name) < 2) {
            $errors['name'] = 'Имя должно быть не меньше 2 символов';
        } elseif(!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $name)) {
            $errors['name'] = 'Имя может содержать только буквы, пробелы и дефисы';
        }
    } else {
        $errors['name'] = 'Заполните поле Name';
    }

    // Проверка email
    if (isset($data['email'])) {
        $email = trim($data["email"]);
        if (empty($email)) {
            $errors['email'] = 'Введите Email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Неверный формат Email';
        }
    } else {
        $errors['email'] = 'Введите Email';
    }

    // Проверка пароля
    if (isset($data['password'])) {
        $password = $data["password"];
        if (empty($password)) {
            $errors['password'] = 'Пароль должен быть заполнен';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Пароль должен быть минимум из 6 символов';
        }
    } else {
        $errors['password'] = 'Пароль должен быть заполнен';
    }

    // Проверка подтверждения пароля
    if (isset($data['password_confirm'])) {
        $password_confirm = $data["password_confirm"];
        if (empty($password_confirm)) {
            $errors['password_confirm'] = 'Подтвердите пароль';
        } elseif (isset($password) && $password !== $password_confirm) {
            $errors['password_confirm'] = 'Пароли не совпадают';
        }
    } else {
        $errors['password_confirm'] = 'Подтвердите пароль';
    }

    return $errors;
}

// Проверяем, что форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateUser($_POST);

    if (empty($errors)) {
        // Берем очищенные данные
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        try {
            $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Проверяем, не существует ли уже пользователь с таким email
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);

            if ($stmt->fetch()) {
                $errors['email'] = 'Пользователь с таким email уже существует';
            } else {
                // Вставляем нового пользователя
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':password' => password_hash($password, PASSWORD_DEFAULT)
                ]);

                exit;
            }
        } catch (PDOException $e) {
            $errors['database'] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

require_once './registration_form.php';
?>