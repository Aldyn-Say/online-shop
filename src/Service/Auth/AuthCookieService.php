<?php

declare(strict_types=1);

namespace Service\Auth;

use Models\User;

class AuthCookieService implements AuthInterface
{
    protected User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function check(): bool
    {
        $userId = $this->getCurrentUserId();
        if ($userId === 0) {
            return false;
        }

        return $this->userModel->getById($userId) !== null;
    }

    public function getCurrentUserId(): int
    {
        return (int) ($_COOKIE['user_id'] ?? 0);
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

        if (!password_verify($password, (string) $user->getPassword())) {
            return false;
        }

        $this->setLoginCookies((int) $user->getId(), (string) $user->getName(), (string) $user->getEmail());
        return true;
    }

    public function setLoginCookies(int $userId, string $name, string $email): void
    {
        $opts = [
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $opts['secure'] = true;
        }

        setcookie('user_id', (string) $userId, $opts);
        setcookie('user_name', $name, $opts);
        setcookie('user_email', $email, $opts);
        $_COOKIE['user_id'] = (string) $userId;
        $_COOKIE['user_name'] = $name;
        $_COOKIE['user_email'] = $email;
    }

    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function logout(): void
    {
        $opts = [
            'path' => '/',
            'expires' => time() - 3600,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $opts['secure'] = true;
        }

        setcookie('user_id', '', $opts);
        setcookie('user_name', '', $opts);
        setcookie('user_email', '', $opts);
        unset($_COOKIE['user_id'], $_COOKIE['user_name'], $_COOKIE['user_email']);
    }
}
