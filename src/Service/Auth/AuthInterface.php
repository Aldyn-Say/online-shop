<?php

declare(strict_types=1);

namespace Service\Auth;

use Models\User;

interface AuthInterface
{
    public function check(): bool;

    public function logout(): void;

    public function getCurrentUser(): ?User;

    public function auth(string $email, string $password): bool;

    public function getCurrentUserId(): int;

    public function getCurrentUserName(): string;

    public function getCurrentUserEmail(): string;

    public function setLoginCookies(int $userId, string $name, string $email): void;

    public function startSession(): void;
}
