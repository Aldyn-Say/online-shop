<?php

namespace Service;

use Models\User;

class AuthService
{
    protected User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function check(): bool
    {
        return isset($_COOKIE['user_id']);
    }

    public function getCurrentUserId(): int
    {
        return (int) ($_COOKIE['user_id'] ?? 0);
    }

    public function getCurrentUserName(): string
    {
        return (string) ($_COOKIE['user_name'] ?? '');
    }

    public function getCurrentUserEmail(): string
    {
        return (string) ($_COOKIE['user_email'] ?? '');
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
        return true;
    }

    public function setLoginCookies(int $userId, string $name, string $email): void
    {
        $expire = time() + 86400 * 30; // 30 дней
        setcookie('user_id', (string) $userId, $expire, '/');
        setcookie('user_name', $name, $expire, '/');
        setcookie('user_email', $email, $expire, '/');
    }

    public function logout(): void
    {
        $this->startSession();
        session_destroy();
        $past = time() - 3600;
        setcookie('user_id', '', $past, '/');
        setcookie('user_name', '', $past, '/');
        setcookie('user_email', '', $past, '/');
    }

    public function startSession(): void
    {
        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
        }
    }
}