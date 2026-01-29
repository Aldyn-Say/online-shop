<?php
require_once 'database.php';
require_once '../Models/User.php';

class UserController {
    private $pdo;
    private $userModel;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?? getDBConnection();
        $this->userModel = new User($this->pdo);
    }

    private function validateName($name) {
        $errors = [];

        if (empty($name)) {
            $errors[] = 'Заполните поле имени';
        } elseif (strlen($name) < 2) {
            $errors[] = 'Имя должно быть не меньше 2 символов';
        } elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁїЇєЄіІ\s\-]+$/u', $name)) {
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
        } elseif ($checkUnique && $this->userModel->emailExists($email, $currentUserId)) {
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

            // Вставляем нового пользователя через модель
            $success = $this->userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            if ($success) {
                return ['success' => true, 'message' => 'Пользователь успешно зарегистрирован'];
            } else {
                return ['success' => false, 'errors' => ['database' => 'Ошибка при создании пользователя']];
            }
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
            // Поиск пользователя через модель
            $user = $this->userModel->getByEmail($email);

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
            $success = $this->userModel->updateName($userId, $name);
            if ($success) {
                return ['success' => true, 'message' => 'Имя успешно обновлено'];
            } else {
                return ['success' => false, 'message' => 'Ошибка при обновлении имени'];
            }
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
            $success = $this->userModel->updateEmail($userId, $email);
            if ($success) {
                return ['success' => true, 'message' => 'Email успешно обновлен'];
            } else {
                return ['success' => false, 'message' => 'Ошибка при обновлении email'];
            }
        } catch (PDOException $e) {
            error_log("Database error in updateEmail: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении email'];
        }
    }

    public function updatePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
        // Проверка текущего пароля через модель
        if (!$this->userModel->verifyPassword($userId, $currentPassword)) {
            return ['success' => false, 'message' => 'Неверный текущий пароль'];
        }

        // Валидация нового пароля
        $errors = $this->validatePassword($newPassword, $confirmPassword);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }

        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $success = $this->userModel->updatePassword($userId, $hashedPassword);
            if ($success) {
                return ['success' => true, 'message' => 'Пароль успешно обновлен'];
            } else {
                return ['success' => false, 'message' => 'Ошибка при обновлении пароля'];
            }
        } catch (PDOException $e) {
            error_log("Database error in updatePassword: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении пароля'];
        }
    }

    public function updateAvatar($userId, $avatarFileName) {
        try {
            $success = $this->userModel->updateAvatar($userId, $avatarFileName);
            if ($success) {
                return ['success' => true, 'message' => 'Аватар успешно обновлен'];
            } else {
                return ['success' => false, 'message' => 'Ошибка при обновлении аватара'];
            }
        } catch (PDOException $e) {
            error_log("Database error in updateAvatar: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении аватара'];
        }
    }

    public function getUserById($userId) {
        return $this->userModel->getById($userId);
    }
}