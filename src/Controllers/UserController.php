<?php

namespace Controllers;

use Models\User;
use Service\AuthService;

class UserController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    private function validateRegistration(array $data): array
    {
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

    private function validateName($name): array
    {
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

    private function validateEmail($email, $checkUnique = false, $currentUserId = null): array
    {
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

    private function validatePassword($password, $passwordConfirm = null): array
    {
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

    public function showRegistrationForm()
    {
        $errors = [];
        $old_registration_data = [];
        $registration_success = null;
        require_once __DIR__ . '/../Views/registration_form.php';
    }

    public function handleRegistration()
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $errors = $this->validateRegistration([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirm' => $passwordConfirm,
        ]);

        if (!empty($errors)) {
            $old_registration_data = $_POST;
            $registration_success = null;
            require_once __DIR__ . '/../Views/registration_form.php';
            return null;
        }

        try {
            if ($this->userModel->emailExists($email)) {
                $errors = ['email' => ['Пользователь с таким email уже существует']];
                $old_registration_data = $_POST;
                $registration_success = null;
                require_once __DIR__ . '/../Views/registration_form.php';
                return null;
            }

            $result = $this->userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            if ($result === true) {
                return ['redirect' => '/login'];
            }
            if (is_array($result) && isset($result['duplicate_email']) && $result['duplicate_email']) {
                $errors = ['email' => ['Пользователь с таким email уже существует']];
                $old_registration_data = $_POST;
                $registration_success = null;
                require_once __DIR__ . '/../Views/registration_form.php';
                return null;
            }
            if ($this->userModel->emailExists($email)) {
                $errors = ['email' => ['Пользователь с таким email уже существует']];
            } else {
                error_log("Registration failed: create() returned false for email: " . $email);
                $errors = ['general' => 'Ошибка при регистрации. Попробуйте позже или обратитесь к администратору.'];
            }
            $old_registration_data = $_POST;
            $registration_success = null;
            require_once __DIR__ . '/../Views/registration_form.php';
            return null;
        } catch (\Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors = ['general' => 'Ошибка сервера. Попробуйте позже.'];
            $old_registration_data = $_POST;
            $registration_success = null;
            require_once __DIR__ . '/../Views/registration_form.php';
            return null;
        }
    }

    public function showLoginForm()
    {
        $errors = [];
        $old_login_data = ['email' => ''];
        require_once __DIR__ . '/../Views/login.php';
    }

    public function handleLogin()
    {
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
            $old_login_data = ['email' => $email];
            require_once __DIR__ . '/../Views/login.php';
            return null;
        }

        try {
            if ($this->authService->auth($email, $password)) {
                $user = $this->userModel->getByEmail($email);
                if ($user) {
                    $this->authService->setLoginCookies(
                        $user->getId(),
                        $user->getName(),
                        $user->getEmail()
                    );
                }
                return ['redirect' => '/catalog'];
            }
            $errors['general'] = ['Неверный email или пароль'];
            $old_login_data = ['email' => $email];
            require_once __DIR__ . '/../Views/login.php';
            return null;
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $errors = ['general' => ['Ошибка сервера. Попробуйте позже.']];
            $old_login_data = ['email' => $email];
            require_once __DIR__ . '/../Views/login.php';
            return null;
        }
    }

    public function showProfile()
    {
        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }
        $userId = $this->authService->getCurrentUserId();
        $user = $this->userModel->getById($userId);
        if (!$user) {
            return ['redirect' => '/login'];
        }
        $errors = [];
        $success = [];
        require_once __DIR__ . '/../Views/profile.php';
    }

    public function handleProfileUpdate()
    {
        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }
        $userId = $this->authService->getCurrentUserId();
        $errors = [];
        $success = [];
        $user = $this->userModel->getById($userId);
        $newName = $this->authService->getCurrentUserName();
        $newEmail = $this->authService->getCurrentUserEmail();

        if (isset($_POST['update_name']) && !empty($_POST['name'])) {
            $name = trim($_POST['name']);
            $result = $this->updateName($userId, $name);
            if ($result['success']) {
                $success['name'] = $result['message'];
                $newName = $name;
            } else {
                $errors['name'] = [$result['message']];
            }
        }

        if (isset($_POST['update_email']) && !empty($_POST['email'])) {
            $email = trim($_POST['email']);
            $result = $this->updateEmail($userId, $email);
            if ($result['success']) {
                $success['email'] = $result['message'];
                $newEmail = $email;
            } else {
                $errors['email'] = [$result['message']];
            }
        }

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

        if ($newName !== $this->getCurrentUserName() || $newEmail !== $this->getCurrentUserEmail()) {
            $expire = time() + 60 * 60 * 24 * 30;
            setcookie('user_id', (string) $userId, $expire, '/');
            setcookie('user_name', $newName, $expire, '/');
            setcookie('user_email', $newEmail, $expire, '/');
        }

        $user = $this->userModel->getById($userId);
        require_once __DIR__ . '/../Views/profile.php';
        return null;
    }

    public function handleAvatarUpload()
    {
        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }
        $userId = $this->authService->getCurrentUserId();
        $errors = [];
        $success = [];

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors['avatar'] = ['Выберите файл для загрузки'];
        } elseif ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $errors['avatar'] = ['Ошибка при загрузке файла'];
        } else {
            $file = $_FILES['avatar'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                $errors['avatar'] = ['Разрешены только изображения (JPEG, PNG, GIF, WebP)'];
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors['avatar'] = ['Размер файла не должен превышать 5MB'];
            } else {
                $avatarResult = $this->uploadAvatar($userId, $file);
                if ($avatarResult['success']) {
                    $success['avatar'] = $avatarResult['message'];
                } else {
                    $errors['avatar'] = [$avatarResult['message']];
                }
            }
        }

        $user = $this->userModel->getById($userId);
        require_once __DIR__ . '/../Views/profile.php';
        return null;
    }

    public function logout()
    {
        $this->authService->logout();
        return ['redirect' => '/login'];
        exit();
    }

    private function uploadAvatar($userId, $file)
    {
        try {
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
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return ['success' => false, 'message' => 'Не удалось сохранить файл'];
            }
            $success = $this->userModel->updateAvatar($userId, $fileName);
            if ($success) {
                return ['success' => true, 'message' => 'Аватар успешно обновлен'];
            }
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            return ['success' => false, 'message' => 'Ошибка при обновлении аватара в БД'];
        } catch (\Exception $e) {
            error_log("Avatar upload error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при загрузке аватара'];
        }
    }

    public function register($data)
    {
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
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            if ($success) {
                return ['success' => true, 'message' => 'Пользователь успешно зарегистрирован'];
            }
            return ['success' => false, 'errors' => ['database' => 'Ошибка при создании пользователя']];
        } catch (\PDOException $e) {
            error_log("Database error in register: " . $e->getMessage());
            return ['success' => false, 'errors' => ['database' => 'Ошибка сервера. Попробуйте позже.']];
        }
    }

    public function updateName($userId, $name)
    {
        $errors = $this->validateName($name);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }
        try {
            $success = $this->userModel->updateName($userId, $name);
            return $success
                ? ['success' => true, 'message' => 'Имя успешно обновлено']
                : ['success' => false, 'message' => 'Ошибка при обновлении имени'];
        } catch (\PDOException $e) {
            error_log("Database error in updateName: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении имени'];
        }
    }

    public function updateEmail($userId, $email)
    {
        $errors = $this->validateEmail($email, true, $userId);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }
        try {
            $success = $this->userModel->updateEmail($userId, $email);
            return $success
                ? ['success' => true, 'message' => 'Email успешно обновлен']
                : ['success' => false, 'message' => 'Ошибка при обновлении email'];
        } catch (\PDOException $e) {
            error_log("Database error in updateEmail: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении email'];
        }
    }

    public function updatePassword($userId, $currentPassword, $newPassword, $confirmPassword)
    {
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
            return $success
                ? ['success' => true, 'message' => 'Пароль успешно обновлен']
                : ['success' => false, 'message' => 'Ошибка при обновлении пароля'];
        } catch (\PDOException $e) {
            error_log("Database error in updatePassword: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при обновлении пароля'];
        }
    }

    public function getUserById($userId)
    {
        return $this->userModel->getById($userId);
    }
}
