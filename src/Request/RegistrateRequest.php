<?php

namespace Request;

use Models\User;

class RegistrateRequest
{
    public function __construct(private array $data)
    {
    }

    public function getName(): string
    {
        return trim((string) ($this->data['name'] ?? ''));
    }

    public function getEmail(): string
    {
        return trim((string) ($this->data['email'] ?? ''));
    }

    public function getPassword(): string
    {
        return (string) ($this->data['password'] ?? '');
    }

    public function getPasswordConfirm(): string
    {
        return (string) ($this->data['password_confirm'] ?? '');
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'password' => $this->getPassword(),
            'password_confirm' => $this->getPasswordConfirm(),
        ];
    }

    /**
     * @return array<string, list<string>> Поля с непустыми списками ошибок (как раньше в UserController::validateRegistration)
     */
    public function validate(?User $userModel = null): array
    {
        $emailErrors = $this->validateEmail();
        if (empty($emailErrors) && $userModel !== null && $userModel->emailExists($this->getEmail(), null)) {
            $emailErrors = ['Пользователь с таким email уже существует'];
        }

        return array_filter([
            'name' => $this->validateName(),
            'email' => $emailErrors,
            'password' => $this->validatePassword(),
        ]);
    }

    /**
     * @return list<string>
     */
    private function validateName(): array
    {
        $name = $this->getName();
        if ($name === '') {
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

    /**
     * @return list<string>
     */
    private function validateEmail(): array
    {
        $email = $this->getEmail();
        if ($email === '') {
            return ['Введите Email'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['Неверный формат Email'];
        }

        return [];
    }

    /**
     * @return list<string>
     */
    private function validatePassword(): array
    {
        $password = $this->getPassword();
        $confirm = $this->getPasswordConfirm();
        if ($password === '') {
            return ['Введите пароль'];
        }
        if (strlen($password) < 6) {
            return ['Пароль должен быть минимум 6 символов'];
        }
        if ($password !== $confirm) {
            return ['Пароли не совпадают'];
        }

        return [];
    }
}
