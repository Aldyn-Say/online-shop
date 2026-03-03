<?php
namespace Controllers;

use Models\Model;
use Models\User;

class UserController extends Model {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User($this->pdo);
    }

    private function isAuthenticated(): bool {
        return !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    private function validateRegistration(array $data): array {
        $errors = [];
        $nameErrors = $this->validateName($data['name'] ?? '');
        if (!empty($nameErrors)) {
            $errors['name'] = $nameErrors;
        }
        $emailErrors = $this->validateEmail($data['email'] ?? '', true, $data['current_user_id'] ?? null);
        if (!empty($emailErrors)) {
            $errors['email'] = $emailErrors;
        }
        $passwordErrors = $this->validatePassword($data['password'] ?? '', $data['password_confirm'] ?? null);
        if (!empty($passwordErrors)) {
            $errors['password'] = $passwordErrors;
        }
        return $errors;
    }


    private function validateName($name): array {    //валидация имени
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

    private function validateEmail($email, $checkUnique = false, $currentUserId = null): array {   //валидация почты
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

    private function validatePassword($password, $passwordConfirm = null):array {  //валидация пароля
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

    public function showRegistrationForm() { //показать страницу регистрации
        require_once __DIR__ . '/../Views/registration_form.php';
    }

    public function handleRegistration() { //регистрация
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $errors = $this->validateRegistration([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirm' => $passwordConfirm
        ]);

        if (!empty($errors)) {
            $_SESSION['registration_errors'] = $errors;
            $_SESSION['old_registration_data'] = $_POST;
            return ['redirect' => '/registration'];
        }

        try {
            if ($this->userModel->emailExists($email)) {
                $_SESSION['registration_errors'] = ['email' => ['Пользователь с таким email уже существует']];
                $_SESSION['old_registration_data'] = $_POST;
                return ['redirect' => '/registration'];
            }

            $result = $this->userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            if ($result === true) {
                $_SESSION['registration_success'] = 'Регистрация прошла успешно! Теперь вы можете войти.';
                return ['redirect' => '/login'];
            } elseif (is_array($result) && isset($result['duplicate_email']) && $result['duplicate_email']) {
                $_SESSION['registration_errors'] = ['email' => ['Пользователь с таким email уже существует']];
                $_SESSION['old_registration_data'] = $_POST;
                return ['redirect' => '/registration'];
            } else {
                if ($this->userModel->emailExists($email)) {
                    $_SESSION['registration_errors'] = ['email' => ['Пользователь с таким email уже существует']];
                } else {
                    error_log("Registration failed: create() returned false for email: " . $email . " | Result: " . var_export($result, true));
                    $_SESSION['registration_errors'] = ['general' => 'Ошибка при регистрации. Попробуйте позже или обратитесь к администратору.'];
                }
                $_SESSION['old_registration_data'] = $_POST;
                return ['redirect' => '/registration'];
            }
        } catch (\Exception $e) {
            error_log("Registration error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            $_SESSION['registration_errors'] = ['general' => 'Ошибка сервера. Попробуйте позже.'];
            $_SESSION['old_registration_data'] = $_POST;
            return ['redirect' => '/registration'];
        }
    }

    public function showLoginForm() {
        $errors = $_SESSION['login_errors'] ?? [];
        $old_login_data = $_SESSION['old_login_data'] ?? ['email' => ''];
        unset($_SESSION['login_errors'], $_SESSION['old_login_data']);
        require_once __DIR__ . '/../Views/login.php';
    }

    public function handleLogin() {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];
        if (empty($email)) {
            $errors['email'] = ['Введите email'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Введите корректный email'];
        }

        if (empty($password)) {
            $errors['password'] = ['Введите пароль'];
        }

        if (!empty($errors)) {
            $_SESSION['login_errors'] = $errors;
            $_SESSION['old_login_data'] = ['email' => $email];
            return ['redirect' => '/login'];
        }

        try {
            // Аутентификация
            $user = $this->userModel->getByEmail($email);

            if ($user && password_verify($password, $user->getPassword())) {
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['user_name'] = $user->getName();
                $_SESSION['user_email'] = $user->getEmail();
                $_SESSION['logged_in'] = true;

                // Перенаправляем на профиль
                return ['redirect' => '/catalog'];
            } else {
                $_SESSION['login_errors'] = ['general' => ['Неверный email или пароль']];
                $_SESSION['old_login_data'] = ['email' => $email];
                return ['redirect' => '/login'];

            }
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['login_errors'] = ['general' => ['Ошибка сервера. Попробуйте позже.']];
            return ['redirect' => '/login'];
        }
    }

    public function showProfile() {
        if (!$this->isAuthenticated()) {
            return ['redirect' => '/login'];
        }
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getById($userId);
        if (!$user) {
            return ['redirect' => '/login'];
        }
        $errors = $_SESSION['profile_errors'] ?? [];
        $success = $_SESSION['profile_success'] ?? [];
        unset($_SESSION['profile_errors'], $_SESSION['profile_success']);
        require_once __DIR__ . '/../Views/profile.php';
    }

    public function handleProfileUpdate() {
        if (!$this->isAuthenticated()) {
            return ['redirect' => '/login'];
        }

        $userId = $_SESSION['user_id'];
        $errors = [];
        $success = [];

        // Обновление имени
        if (isset($_POST['update_name']) && !empty($_POST['name'])) {
            $name = trim($_POST['name']);
            $result = $this->updateName($userId, $name);

            if ($result['success']) {
                $success['name'] = $result['message'];
                $_SESSION['user_name'] = $name;
            } else {
                $errors['name'] = [$result['message']];
            }
        }

        // Обновление email
        if (isset($_POST['update_email']) && !empty($_POST['email'])) {
            $email = trim($_POST['email']);
            $result = $this->updateEmail($userId, $email);

            if ($result['success']) {
                $success['email'] = $result['message'];
                $_SESSION['user_email'] = $email;
            } else {
                $errors['email'] = [$result['message']];
            }
        }

        // Обновление пароля
        if (isset($_POST['update_password'])) {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $result = $this->updatePassword($userId, $currentPassword, $newPassword, $confirmPassword);

            if ($result['success']) {
                $success['password'] = $result['message'];
            } else {
                $errors['password'] = [$result['message']];
            }
        }

        // Сохраняем сообщения в сессию
        if (!empty($errors)) {
            $_SESSION['profile_errors'] = $errors;
        }
        if (!empty($success)) {
            $_SESSION['profile_success'] = $success;
        }

        return ['redirect' => '/profile'];
    }

    public function handleAvatarUpload() {
        if (!$this->isAuthenticated()) {
            return ['redirect' => '/login'];
        }

        $userId = $_SESSION['user_id'];
        $errors = [];
        $success = [];

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors['avatar'] = ['Выберите файл для загрузки'];
        } elseif ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $errors['avatar'] = ['Ошибка при загрузке файла'];
        } else {
            $file = $_FILES['avatar'];

            // Проверка типа файла
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                $errors['avatar'] = ['Разрешены только изображения (JPEG, PNG, GIF, WebP)'];
            } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB
                $errors['avatar'] = ['Размер файла не должен превышать 5MB'];
            } else {
                // Загружаем аватар
                $avatarResult = $this->uploadAvatar($userId, $file);

                if ($avatarResult['success']) {
                    $success['avatar'] = $avatarResult['message'];
                } else {
                    $errors['avatar'] = [$avatarResult['message']];
                }
            }
        }

        // Сохраняем сообщения
        if (!empty($errors)) {
            $_SESSION['profile_errors'] = $errors;
        }
        if (!empty($success)) {
            $_SESSION['profile_success'] = $success;
        }

        return ['redirect' => '/profile'];
    }

    public function logout() {
        session_destroy();
        return ['redirect' => '/login'];
    }


    private function uploadAvatar($userId, $file) {
        try {
            // Создаем директорию если нужно
            $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $user = $this->userModel->getById($userId);
            if ($user && $user->getAvatar() !== null && $user->getAvatar() !== '') {
                $oldPath = $uploadDir . $user->getAvatar();
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Генерируем имя файла
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;

            // Загружаем файл
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return ['success' => false, 'message' => 'Не удалось сохранить файл'];
            }

            // Обновляем в БД
            $success = $this->userModel->updateAvatar($userId, $fileName);

            if ($success) {
                return ['success' => true, 'message' => 'Аватар успешно обновлен'];
            } else {
                // Откатываем загрузку файла
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                return ['success' => false, 'message' => 'Ошибка при обновлении аватара в БД'];
            }
        } catch (\Exception $e) {
            error_log("Avatar upload error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при загрузке аватара'];
        }
    }

    public function register($data) {
        $errors = $this->validateRegistration($data);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $name = trim($data['name']);
            $email = trim($data['email']);
            $password = $data['password'];

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
        } catch (\PDOException $e) {
            error_log("Database error in register: " . $e->getMessage());
            return ['success' => false, 'errors' => ['database' => 'Ошибка сервера. Попробуйте позже.']];
        }
    }

    public function updateName($userId, $name) {
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
        } catch (\PDOException $e) {
            error_log("Database error in updateName: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении имени'];
        }
    }

    public function updateEmail($userId, $email) {
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
        } catch (\PDOException $e) {
            error_log("Database error in updateEmail: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении email'];
        }
    }

    public function updatePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
        if (!$this->userModel->verifyPassword($userId, $currentPassword)) {
            return ['success' => false, 'message' => 'Неверный текущий пароль'];
        }

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
        } catch (\PDOException $e) {
            error_log("Database error in updatePassword: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении пароля'];
        }
    }

    public function getUserById($userId) {
        return $this->userModel->getById($userId);
    }
}