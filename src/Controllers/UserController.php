<?php
declare(strict_types=1);

namespace Controllers;

use Models\User;
use Request\RegistrateRequest;

class UserController extends BaseController
{
    protected User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    private function requireAuth(): array|null
    {
        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }
        return null;
    }

    private function getCurrentUser(): User|null
    {
        $userId = $this->authService->getCurrentUserId();
        return $userId ? $this->userModel->getById($userId) : null;
    }

    private function redirectWithData(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . "/../Views/{$view}.php";
    }
    
    private function validateName(string $name): array
    {
        if (empty($name)) {
            return ['Заполните имя'];
        }
        if (strlen($name) < 2) {
            return ['Имя должно быть не меньше 2 символов'];
        }
        if (!preg_match('/^[a-zA-Zа-яА-ЯёЁїЇєЄіІ\s\-]+$/u', $name)) {
            return ['Имя может содержать только буквы, пробелы и дефисы'];
        }

        return [];
    }

    private function validateEmail(string $email, bool $checkUnique = false, ?int $currentUserId = null): array
    {
        if (empty($email)) {
            return ['Введите Email'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['Неверный формат Email'];
        }
        if ($checkUnique && $this->userModel->emailExists($email, $currentUserId)) {
            return ['Пользователь с таким email уже существует'];
        }
        return [];
    }

    private function validatePassword(string $password, ?string $passwordConfirm = null): array
    {
        if (empty($password)) {
            return ['Введите пароль'];
        }
        if (strlen($password) < 6) {
            return ['Пароль должен быть минимум 6 символов'];
        }
        if ($passwordConfirm !== null && $password !== $passwordConfirm) {
            return ['Пароли не совпадают'];
        }
        return [];
    }

    public function showRegistrationForm(): void
    {
        $this->redirectWithData('registration_form', [
            'errors' => [],
            'old_registration_data' => [],
            'registration_success' => null
        ]);
    }

    public function handleRegistration(RegistrateRequest $request): ?array
    {
        $errors = $request->validate($this->userModel);

        if (!empty($errors)) {
            $this->redirectWithData('registration_form', [
                'errors' => $errors,
                'old_registration_data' => $_POST,
                'registration_success' => null,
            ]);

            return null;
        }

        $data = $request->toArray();

        try {
            $result = $this->userModel->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ]);

            if ($result === true) {
                return ['redirect' => '/login'];
            }

            if (is_array($result) && !empty($result['duplicate_email'])) {
                $errors = ['emmail' => ['Пользователь с таким email уже существует']];
            } else {
                $errors = ['general' => ['Ошибка сервера. Попробуйте позже.']];
            }

        } catch (\Exception $e) {
            $this->logError('Registration error: ' . $e->getMessage());
            $errors = ['general' => ['Ошибка сервера. Попробуйте позже.']];
        }

        $this->redirectWithData('registration_form', [
            'errors' => $errors,
            'old_registration_data' => $_POST,
            'registration_success' => null
        ]);

        return null;
    }

    public function showLoginForm(): void
    {
        $this->redirectWithData('login', [
            'errors' => [],
            'old_login_data' => ['email' => '']
        ]);
    }

    public function handleLogin(): ?array
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
            $this->redirectWithData('login', [
                'errors' => $errors,
                'old_login_data' => ['email' => $email]
            ]);

            return null;
        }

        try {
            if ($this->authService->auth($email, $password)) {
                return ['redirect' => '/catalog'];
            }
            $errors['general'] = ['Неверный email или пароль'];
        } catch (\Exception $e) {
            $this->logError('Login error: ' . $e->getMessage());
            $errors['general'] = ['Ошибка сервера. Попробуйте позже.'];
        }

        $this->redirectWithData('login', [
            'errors' => $errors,
            'old_login_data' => ['email' => $email]
        ]);

        return null;
    }

    public function showProfile(): ?array
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $user = $this->getCurrentUser();
        if (!$user) {
            return ['redirect' => '/login'];
        }

        $this->redirectWithData('profile', [
            'user' => $user,
            'errors' => [],
            'success' => []
        ]);

        return null;
    }

    public function handleProfileUpdate(): ?array
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

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

        $user = $this->userModel->getById($userId);
        if ($user === null) {
            return ['redirect' => '/login'];
        }

        if ($success !== []) {
            $this->authService->setLoginCookies(
                $userId,
                (string) $user->getName(),
                (string) $user->getEmail()
            );
        }

        $this->redirectWithData('profile', [
            'user' => $user,
            'errors' => $errors,
            'success' => $success
        ]);

        return null;
    }

    public function handleAvatarUpload(): ?array
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

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
                $uploadDir = dirname(__DIR__) . '/public/uploads/avatars/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $user = $this->userModel->getById($userId);
                if ($user && $user->getAvatar()) {
                    $oldPath = $uploadDir . $user->getAvatar();
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $extension = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($extension === '') {
                    $extension = match ($fileType) {
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/gif' => 'gif',
                        'image/webp' => 'webp',
                        default => 'img',
                    };
                }
                $fileName = "avatar_{$userId}_" . time() . '.' . $extension;

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

        return null;
    }

    public function logout(): array
    {
        $this->authService->logout();

        return ['redirect' => '/login'];
    }
}