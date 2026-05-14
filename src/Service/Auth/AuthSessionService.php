<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна

namespace Service\Auth;

use Models\User;

class AuthSessionService implements AuthInterface
{
    protected User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // TODO (Бизнес-логика): check() обращается к $_SESSION['user_id'] без предварительного вызова
    // startSession() — если сессия не была запущена ранее, $_SESSION недоступен и метод вернёт false
    // даже для авторизованного пользователя. Нужно добавить $this->startSession() в начало метода.
    public function check(): bool
    {
        return isset($_SESSION['user_id']);  // проверка авторизации
    }

    public function getCurrentUserId(): int
    {
        $this->startSession();
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    public function getCurrentUser(): ?User
    {
        $id = $this->getCurrentUserId();
        if ($id === 0) {
            return null;
        }
        return $this->userModel->getById($id);
    }

    public function getCurrentUserName(): string
    {
        return (string) ($_SESSION['user_name'] ?? '');
    }

    public function getCurrentUserEmail(): string
    {
        return (string) ($_SESSION['user_email'] ?? '');
    }

    public function auth(string $email, string $password): bool
    {
        $user = $this->userModel->getByEmail($email);
        if (!$user) {
            return false;
        }
        if (!password_verify($password, $user->getPassword())) {
            return false;
        }
        $this->setLoginCookies((int) $user->getId(), (string) $user->getName(), (string) $user->getEmail());
        return true;
    }

    public function setLoginCookies(int $userId, string $name, string $email): void
    {
        $this->startSession();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
    }

    public function logout(): void
    {
        $this->startSession();
        session_destroy();
    }

    public function startSession(): void
    {
        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
        }
    }
}