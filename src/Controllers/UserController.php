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

    // ========== ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ==========

    private function requireAuth(): ?array
    {
        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }
        return null;
    }

    private function getCurrentUser()
    {
        $userId = $this->authService->getCurrentUserId();
        return $userId ? $this->userModel->getById($userId) : null;
    }

    private function redirectWithData(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . "/../Views/{$view}.php";
    }

    // ========== ВАЛИДАЦИЯ ==========

    private function validateName($name): array
    {
        if (empty($name)) return ['Заполните имя'];
        if (strlen($name) < 2) return ['Имя должно быть не меньше 2 символов'];
        if (!preg_match('/^[a-zA-Zа-яА-ЯёЁїЇєЄіІ\s\-]+$/u', $name)) {
            return ['Имя может содержать только буквы, пробелы и дефисы'];
        }
        return [];
    }

    private function validateEmail($email, $checkUnique = false, $currentUserId = null): array
    {
        if (empty($email)) return ['Введите Email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['Неверный формат Email'];
        if ($checkUnique && $this->userModel->emailExists($email, $currentUserId)) {
            return ['Пользователь с таким email уже существует'];
        }
        return [];
    }

    private function validatePassword($password, $passwordConfirm = null): array
    {
        if (empty($password)) return ['Введите пароль'];
        if (strlen($password) < 6) return ['Пароль должен быть минимум 6 символов'];
        if ($passwordConfirm !== null && $password !== $passwordConfirm) {
            return ['Пароли не совпадают'];
        }
        return [];
    }

    private function validateRegistration(array $data): array
    {
        return array_filter([
            'name' => $this->validateName($data['name'] ?? ''),
            'email' => $this->validateEmail($data['email'] ?? '', true),
            'password' => $this->validatePassword($data['password'] ?? '', $data['password_confirm'] ?? null)
        ]);
    }

    // ========== РЕГИСТРАЦИЯ ==========

    public function showRegistrationForm()
    {
        $this->redirectWithData('registration_form', [
            'errors' => [],
            'old_registration_data' => [],
            'registration_success' => null
        ]);
    }

    public function handleRegistration()
    {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? ''
        ];

        $errors = $this->validateRegistration($data);

        if (!empty($errors)) {
            return $this->redirectWithData('registration_form', [
                'errors' => $errors,
                'old_registration_data' => $_POST,
                'registration_success' => null
            ]);
        }

        try {
            $result = $this->userModel->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ]);

            if ($result === true) {
                return ['redirect' => '/login'];
            }

            $errors = ['email' => ['Пользователь с таким email уже существует']];

        } catch (\Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors = ['general' => ['Ошибка сервера. Попробуйте позже.']];
        }

        $this->redirectWithData('registration_form', [
            'errors' => $errors,
            'old_registration_data' => $_POST,
            'registration_success' => null
        ]);
    }


    public function showLoginForm()
    {
        $this->redirectWithData('login', [
            'errors' => [],
            'old_login_data' => ['email' => '']
        ]);
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
            return $this->redirectWithData('login', [
                'errors' => $errors,
                'old_login_data' => ['email' => $email]
            ]);
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
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $errors['general'] = ['Ошибка сервера. Попробуйте позже.'];
        }

        $this->redirectWithData('login', [
            'errors' => $errors,
            'old_login_data' => ['email' => $email]
        ]);
    }


    public function showProfile()
    {
        if ($redirect = $this->requireAuth()) return $redirect;

        $user = $this->getCurrentUser();
        if (!$user) return ['redirect' => '/login'];

        $this->redirectWithData('profile', [
            'user' => $user,
            'errors' => [],
            'success' => []
        ]);
    }

    public function handleProfileUpdate()
    {
        if ($redirect = $this->requireAuth()) return $redirect;

        $userId = $this->authService->getCurrentUserId();
        $errors = [];
        $success = [];

        if (isset($_POST['update_name']) && !empty($_POST['name'])) {
            $name = trim($_POST['name']);
            $nameErrors = $this->validateName($name);
            if (empty($nameErrors)) {
                if ($this->userModel->updateName($userId, $name)) {
                    $success['name'] = 'Имя обновлено';
                } else {
                    $errors['name'] = ['Ошибка обновления имени'];
                }
            } else {
                $errors['name'] = $nameErrors;
            }
        }

        if (isset($_POST['update_email']) && !empty($_POST['email'])) {
            $email = trim($_POST['email']);
            $emailErrors = $this->validateEmail($email, true, $userId);
            if (empty($emailErrors)) {
                if ($this->userModel->updateEmail($userId, $email)) {
                    $success['email'] = 'Email обновлен';
                } else {
                    $errors['email'] = ['Ошибка обновления email'];
                }
            } else {
                $errors['email'] = $emailErrors;
            }
        }

        if (isset($_POST['update_password'])) {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (!$this->userModel->verifyPassword($userId, $current)) {
                $errors['password'] = ['Неверный текущий пароль'];
            } else {
                $passErrors = $this->validatePassword($new, $confirm);
                if (empty($passErrors)) {
                    $hashed = password_hash($new, PASSWORD_DEFAULT);
                    if ($this->userModel->updatePassword($userId, $hashed)) {
                        $success['password'] = 'Пароль обновлен';
                    } else {
                        $errors['password'] = ['Ошибка обновления пароля'];
                    }
                } else {
                    $errors['password'] = $passErrors;
                }
            }
        }

        $user = $this->userModel->getById($userId); // Обновляем куки
        $expire = time() + 60 * 60 * 24 * 30;
        setcookie('user_id', (string) $userId, $expire, '/');
        setcookie('user_name', $user->getName(), $expire, '/');
        setcookie('user_email', $user->getEmail(), $expire, '/');

        $this->redirectWithData('profile', [
            'user' => $user,
            'errors' => $errors,
            'success' => $success
        ]);
    }

    public function handleAvatarUpload()
    {
        if ($redirect = $this->requireAuth()) return $redirect;

        $userId = $this->authService->getCurrentUserId();
        $errors = [];
        $success = [];

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors['avatar'] = ['Выберите файл'];
        } elseif ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $errors['avatar'] = ['Ошибка загрузки'];
        } else {
            $file = $_FILES['avatar'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                $errors['avatar'] = ['Разрешены только JPEG, PNG, GIF, WebP'];
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors['avatar'] = ['Максимум 5MB'];
            } else {
                $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $user = $this->userModel->getById($userId);
                if ($user && $user->getAvatar()) {
                    $oldPath = $uploadDir . $user->getAvatar();
                    if (file_exists($oldPath)) unlink($oldPath);
                }

                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = "avatar_{$userId}_" . time() . ".$extension";

                if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                    if ($this->userModel->updateAvatar($userId, $fileName)) {
                        $success['avatar'] = 'Аватар обновлен';
                    } else {
                        unlink($uploadDir . $fileName);
                        $errors['avatar'] = ['Ошибка сохранения'];
                    }
                } else {
                    $errors['avatar'] = ['Ошибка загрузки'];
                }
            }
        }

        $user = $this->userModel->getById($userId);
        $this->redirectWithData('profile', [
            'user' => $user,
            'errors' => $errors,
            'success' => $success
        ]);
    }


    public function logout()
    {
        $this->authService->logout();
        return ['redirect' => '/login'];
        exit();
    }
}