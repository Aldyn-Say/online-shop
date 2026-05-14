<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна

namespace Service\Auth;

use Models\User;

class AuthCookieService implements AuthInterface
{
    protected User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // TODO (Безопасность): check() проверяет только наличие cookie 'user_id', но не его содержимое.
    // Любой пользователь может вручную установить cookie user_id=1 в браузере и получить доступ
    // как другой пользователь. Нужно хранить токен сессии, а не user_id напрямую.
    public function check(): bool
    {
        return isset($_COOKIE['user_id']);  // проверка авторизации
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
        // else после return лишний — нужно убрать else и оставить только тело
        } else {
            $passwordDB = $user->getPassword();
        }
        if (!password_verify($password, $passwordDB)) {
            return false;
        }
        $this->setLoginCookies((int) $user->getId(), (string) $user->getName(), (string) $user->getEmail());
        return true;
    }

    // TODO (Безопасность): куки устанавливаются без флагов безопасности:
    //   - 'httponly' => true  — защита от кражи куки через JavaScript (XSS)
    //   - 'secure'   => true  — передача только по HTTPS
    //   - 'expires'  — отсутствует: куки сессионные и удалятся при закрытии браузера.
    //     Если нужна постоянная авторизация — добавить: 'expires' => time() + 30 * 24 * 3600
    public function setLoginCookies(int $userId, string $name, string $email): void
    {
        $opts = ['path' => '/'];
        setcookie('user_id', (string) $userId, $opts);
        setcookie('user_name', $name, $opts);
        setcookie('user_email', $email, $opts);
        $_COOKIE['user_id'] = (string) $userId;
        $_COOKIE['user_name'] = $name;
        $_COOKIE['user_email'] = $email;
    }

    public function startSession(): void
    {
        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function logout(): void
    {
        $opts = ['path' => '/', 'expires' => time() - 3600];
        setcookie('user_id', '', $opts);
        setcookie('user_name', '', $opts);
        setcookie('user_email', '', $opts);
        unset($_COOKIE['user_id'], $_COOKIE['user_name'], $_COOKIE['user_email']);
    }
// PSR-12 §4.1: пустая строка перед закрывающей } класса недопустима — нужно убрать
}