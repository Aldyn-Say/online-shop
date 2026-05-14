<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна

namespace Controllers;

use Models\User;
use Request\RegistrateRequest;

class UserController extends BaseController
{
    // PSR-1 §4.3: у свойства $userModel отсутствует тип — нужно User
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }


    private function requireAuth(): ?array
    {
        if (!$this->authService->check()) {
            return ['redirect' => '/login'];
        }
        return null;
    }

    // TODO (PSR-1 §4.3): метод getCurrentUser() не имеет return type — нужно добавить: ?User
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

    // PSR-1 §4.3: параметр $name без типа — нужно string
    private function validateName($name): array
    {
        // PSR-12 §5.1: управляющие конструкции без фигурных скобок — нужно обернуть тело в {}
        if (empty($name)) return ['Заполните имя'];
        if (strlen($name) < 2) return ['Имя должно быть не меньше 2 символов'];
        if (!preg_match('/^[a-zA-Zа-яА-ЯёЁїЇєЄіІ\s\-]+$/u', $name)) {
            return ['Имя может содержать только буквы, пробелы и дефисы'];
        }
        return [];
    }

    // PSR-1 §4.3: параметры без типов — нужно string $email, bool $checkUnique, ?int $currentUserId
    private function validateEmail($email, $checkUnique = false, $currentUserId = null): array
    {
        // PSR-12 §5.1: управляющие конструкции без фигурных скобок — нужно обернуть тело в {}
        if (empty($email)) return ['Введите Email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['Неверный формат Email'];
        if ($checkUnique && $this->userModel->emailExists($email, $currentUserId)) {
            return ['Пользователь с таким email уже существует'];
        }
        return [];
    }

    // PSR-1 §4.3: параметры без типов — нужно string $password, ?string $passwordConfirm
    private function validatePassword($password, $passwordConfirm = null): array
    {
        // PSR-12 §5.1: управляющие конструкции без фигурных скобок — нужно обернуть тело в {}
        if (empty($password)) return ['Введите пароль'];
        if (strlen($password) < 6) return ['Пароль должен быть минимум 6 символов'];
        if ($passwordConfirm !== null && $password !== $passwordConfirm) {
            return ['Пароли не совпадают'];
        }
        return [];
    }

    // PSR-1 §4.3: у метода нет return type — нужно void
    public function showRegistrationForm()
    {
        $this->redirectWithData('registration_form', [
            'errors' => [],
            'old_registration_data' => [],
            'registration_success' => null
        ]);
    }

    // PSR-1 §4.3: у метода нет return type — нужно array|void (или mixed)
    public function handleRegistration(RegistrateRequest $request)
    {
        $errors = $request->validate($this->userModel);

        if (!empty($errors)) {
            $this->redirectWithData('registration_form', [
                'errors' => $errors,
                'old_registration_data' => $_POST,
                'registration_success' => null,
            ]);
            return;
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

            $errors = ['email' => ['Пользователь с таким email уже существует']];

        } catch (\Exception $e) {
            $this->logError('Registration error: ' . $e->getMessage());
            $errors = ['general' => ['Ошибка сервера. Попробуйте позже.']];
        }

        $this->redirectWithData('registration_form', [
            'errors' => $errors,
            'old_registration_data' => $_POST,
            'registration_success' => null
        ]);
    }


    // PSR-1 §4.3: у метода нет return type — нужно void
    public function showLoginForm()
    {
        $this->redirectWithData('login', [
            'errors' => [],
            'old_login_data' => ['email' => '']
        ]);
    }

    // PSR-1 §4.3: у метода нет return type — нужно array|void
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
    }


    // PSR-1 §4.3: у метода нет return type — нужно array|void
    public function showProfile()
    {
        // PSR-12 §5.1: управляющая конструкция без фигурных скобок — нужно обернуть тело в {}
        if ($redirect = $this->requireAuth()) return $redirect;

        $user = $this->getCurrentUser();
        // PSR-12 §5.1: управляющая конструкция без фигурных скобок — нужно обернуть тело в {}
        if (!$user) return ['redirect' => '/login'];

        $this->redirectWithData('profile', [
            'user' => $user,
            'errors' => [],
            'success' => []
        ]);
    }

    // PSR-1 §4.3: у метода нет return type — нужно array|void
    public function handleProfileUpdate()
    {
        // PSR-12 §5.1: управляющая конструкция без фигурных скобок — нужно обернуть тело в {}
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

        // TODO (Бизнес-логика): setcookie() вызывается напрямую в контроллере — это нарушение
        // разделения ответственности. Обновление куков авторизации должно выполняться через
        // AuthCookieService::setLoginCookies(), а не в UserController.
        // TODO (Бизнес-логика): проект использует AuthSessionService (сессии), а не AuthCookieService (куки).
        // Установка куков тут создаёт несогласованность: данные сохраняются и в сессии, и в куках.
        // Если нужны куки — перейти на AuthCookieService; иначе — удалить setcookie() отсюда.
        // TODO (Бизнес-логика): $user может быть null если пользователь не найден —
        // вызов $user->getName() без null-проверки приведёт к фатальной ошибке
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

    // PSR-1 §4.3: у метода нет return type — нужно array|void
    public function handleAvatarUpload()
    {
        // PSR-12 §5.1: управляющая конструкция без фигурных скобок — нужно обернуть тело в {}
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
                // Документ nginx: src/public — сюда же пишем файлы (не корневой /public проекта).
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
    }


    // PSR-1 §4.3: у метода нет return type — нужно array
    public function logout()
    {
        $this->authService->logout();
        return ['redirect' => '/login'];
        // Недостижимый код: exit() никогда не будет выполнен — нужно удалить
        exit();
    }
}