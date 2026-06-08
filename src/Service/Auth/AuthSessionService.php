<?php

declare(strict_types=1);

namespace Service\Auth;

use Models\User;

class AuthSessionService implements AuthInterface
{
    protected User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function check(): bool
    {
        $this->startSession();

        return isset($_SESSION['user_id']);
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
        $this->startSession();
        return (string) ($_SESSION['user_name'] ?? '');
    }

    public function getCurrentUserEmail(): string
    {
        $this->startSession();
        return (string) ($_SESSION['user_email'] ?? '');
    }

    public function auth(string $email, string $password): bool
    {
        $user = $this->userModel->getByEmail($email);
        if (!$user) {
            return false;
        }
        if (!password_verify($password, (string) $user->getPassword())) {
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
