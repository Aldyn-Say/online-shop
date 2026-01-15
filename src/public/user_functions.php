<?php
function getDBConnection() {
    $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}
function validateName($name) {
    $errors = [];
    if (empty($name)) {
        $errors[] = 'Заполните поле имени';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Имя должно быть не меньше 2 символов';
    } elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $name)) {
        $errors[] = 'Имя может содержать только буквы, пробелы и дефисы';
    }
    return $errors;
}

function validateEmail($email, $currentUserId = null) {
    $errors = [];
    if (empty($email)) {
        $errors[] = 'Введите Email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Неверный формат Email';
    } else {
        // Проверка на уникальность email (кроме текущего пользователя)
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
        $stmt->execute([':email' => $email, ':user_id' => $currentUserId]);
        if ($stmt->fetch()) {
            $errors[] = 'Пользователь с таким email уже существует';
        }
    }
    return $errors;
}

function validatePassword($password, $password_confirm = null) {
    $errors = [];
    if (empty($password)) {
        $errors[] = 'Пароль должен быть заполнен';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Пароль должен быть минимум из 6 символов';
    }
    
    if ($password_confirm !== null && $password !== $password_confirm) {
        $errors[] = 'Пароли не совпадают';
    }
    
    return $errors;
}

function updateUserName($userId, $name) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET name = :name WHERE id = :id");
        $stmt->execute([':name' => $name, ':id' => $userId]);
        return ['success' => true, 'message' => 'Имя успешно обновлено'];
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Ошибка при обновлении имени'];
    }
}

function updateUserEmail($userId, $email) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET email = :email WHERE id = :id");
        $stmt->execute([':email' => $email, ':id' => $userId]);
        return ['success' => true, 'message' => 'Email успешно обновлен'];
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Ошибка при обновлении email'];
    }
}

function updateUserPassword($userId, $newPassword) {
    try {
        $pdo = getDBConnection();
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([':password' => $hashedPassword, ':id' => $userId]);
        return ['success' => true, 'message' => 'Пароль успешно обновлен'];
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Ошибка при обновлении пароля'];
    }
}

function updateUserAvatar($userId, $avatarFileName) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
        $stmt->execute([':avatar' => $avatarFileName, ':id' => $userId]);
        return ['success' => true, 'message' => 'Аватар успешно обновлен'];
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Ошибка при обновлении аватара'];
    }
}

function getUserById($userId) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return null;
    }
}

function verifyCurrentPassword($userId, $currentPassword) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($currentPassword, $user['password'])) {
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

