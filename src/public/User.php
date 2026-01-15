<?php
if (!function_exists('getDBConnection')) {
    function getDBConnection() {
        $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "aldun", "0000");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}

class User {
    private $pdo;
    
    public function __construct($pdo = null) {
        // Если подключение передано - используем его, иначе создаем новое
        $this->pdo = $pdo ?? getDBConnection();
    }

    private function validateName($name) {
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
    
    private function validateEmail($email, $checkUnique = false, $currentUserId = null) {
        $errors = [];
        
        if (empty($email)) {
            $errors[] = 'Введите Email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Неверный формат Email';
        } elseif ($checkUnique && $this->emailExists($email, $currentUserId)) {
            $errors[] = 'Пользователь с таким email уже существует';
        }
        
        return $errors;
    }
    
    private function validatePassword($password, $passwordConfirm = null) {
        $errors = [];
        
        if (empty($password)) {
            $errors[] = 'Пароль должен быть заполнен';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Пароль должен быть минимум из 6 символов';
        }
        
        if ($passwordConfirm !== null && $password !== $passwordConfirm) {
            $errors[] = 'Пароли не совпадают';
        }
        
        return $errors;
    }
    
    private function emailExists($email, $excludeUserId = null) {
        try {
            if ($excludeUserId !== null) {
                $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
                $stmt->execute([':email' => $email, ':user_id' => $excludeUserId]);
            } else {
                $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->execute([':email' => $email]);
            }
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Database error in emailExists: " . $e->getMessage());
            return false;
        }
    }

    public function validateRegistration($data) {
        $errors = [];
        
        // Валидация имени
        $nameErrors = $this->validateName($data['name'] ?? '');
        if (!empty($nameErrors)) {
            $errors['name'] = $nameErrors;
        }
        
        // Валидация email (с проверкой уникальности)
        $emailErrors = $this->validateEmail($data['email'] ?? '', true);
        if (!empty($emailErrors)) {
            $errors['email'] = $emailErrors;
        }
        
        // Валидация пароля
        $passwordErrors = $this->validatePassword(
            $data['password'] ?? '', 
            $data['password_confirm'] ?? null
        );
        if (!empty($passwordErrors)) {
            $errors['password'] = $passwordErrors;
        }
        
        return $errors;
    }
    
    public function register($data) {
        // Сначала валидируем данные
        $errors = $this->validateRegistration($data);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $name = trim($data['name']);
            $email = trim($data['email']);
            $password = $data['password'];
            
            // Вставляем нового пользователя
            $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT)
            ]);
            
            return ['success' => true, 'message' => 'Пользователь успешно зарегистрирован'];
        } catch (PDOException $e) {
            error_log("Database error in register: " . $e->getMessage());
            return ['success' => false, 'errors' => ['database' => 'Ошибка сервера. Попробуйте позже.']];
        }
    }

    public function validateLogin($email, $password) {
        $errors = [];
        
        if (empty($email)) {
            $errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Введите корректный email';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Введите пароль';
        }
        
        return $errors;
    }
    
    public function login($email, $password) {
        // Валидация
        $errors = $this->validateLogin($email, $password);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            // Поиск пользователя
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'name' => $user['name'] ?? $user['email']
                    ]
                ];
            } else {
                return ['success' => false, 'errors' => ['general' => 'Неверный email или пароль']];
            }
        } catch (PDOException $e) {
            error_log("Database error in login: " . $e->getMessage());
            return ['success' => false, 'errors' => ['database' => 'Ошибка сервера. Попробуйте позже.']];
        }
    }

    public function updateName($userId, $name) {
        // Валидация
        $errors = $this->validateName($name);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }
        
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET name = :name WHERE id = :id");
            $stmt->execute([':name' => $name, ':id' => $userId]);
            return ['success' => true, 'message' => 'Имя успешно обновлено'];
        } catch (PDOException $e) {
            error_log("Database error in updateName: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении имени'];
        }
    }
    
    public function updateEmail($userId, $email) {
        // Валидация (с проверкой уникальности, исключая текущего пользователя)
        $errors = $this->validateEmail($email, true, $userId);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }
        
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET email = :email WHERE id = :id");
            $stmt->execute([':email' => $email, ':id' => $userId]);
            return ['success' => true, 'message' => 'Email успешно обновлен'];
        } catch (PDOException $e) {
            error_log("Database error in updateEmail: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении email'];
        }
    }
    
    public function updatePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
        // Проверка текущего пароля
        if (!$this->verifyCurrentPassword($userId, $currentPassword)) {
            return ['success' => false, 'message' => 'Неверный текущий пароль'];
        }
        
        // Валидация нового пароля
        $errors = $this->validatePassword($newPassword, $confirmPassword);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }
        
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute([':password' => $hashedPassword, ':id' => $userId]);
            return ['success' => true, 'message' => 'Пароль успешно обновлен'];
        } catch (PDOException $e) {
            error_log("Database error in updatePassword: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении пароля'];
        }
    }
    
    public function updateAvatar($userId, $avatarFileName) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
            $stmt->execute([':avatar' => $avatarFileName, ':id' => $userId]);
            return ['success' => true, 'message' => 'Аватар успешно обновлен'];
        } catch (PDOException $e) {
            error_log("Database error in updateAvatar: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении аватара'];
        }
    }

    public function getUserById($userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getUserById: " . $e->getMessage());
            return null;
        }
    }
    
    public function verifyCurrentPassword($userId, $currentPassword) {
        try {
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($currentPassword, $user['password'])) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Database error in verifyCurrentPassword: " . $e->getMessage());
            return false;
        }
    }
}

