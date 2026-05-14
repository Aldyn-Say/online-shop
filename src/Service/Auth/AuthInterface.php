<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна

namespace Service\Auth;

use Models\User;

interface AuthInterface
{
    public function check(): bool;
    // TODO (PSR-1 §4.3): метод logout() не имеет return type — нужно добавить: void
    public function logout();
    public function getCurrentUser(): ?User;
    public function auth(string $email, string $password): bool;
    public function getCurrentUserId(): int;

    public function getCurrentUserName(): string;

    public function getCurrentUserEmail(): string;

    public function setLoginCookies(int $userId, string $name, string $email): void;

    public function startSession(): void;
}