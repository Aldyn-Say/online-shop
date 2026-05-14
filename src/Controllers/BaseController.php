<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна

namespace Controllers;

use Service\Auth\AuthInterface;
use Service\Auth\AuthSessionService;

abstract class BaseController
{
    // TODO (Бизнес-логика / принцип dependency inversion): тип свойства должен быть интерфейсом AuthInterface,
    // а не конкретной реализацией AuthSessionService — это нарушает принцип DIP и делает невозможным
    // подмену реализации (например, AuthCookieService) без изменения базового класса
    protected AuthSessionService $authService;


    public function __construct()
    {
        $this->authService = new AuthSessionService();
    }

    // TODO (Бизнес-логика): метод logError() создаёт новый экземпляр LoggerService при каждом вызове —
    // нужно вынести логгер в protected-свойство и инициализировать один раз в конструкторе
    // TODO (PSR-12 §4.4): в вызове new \Service\Logger\LoggerService() используется полное имя с обратным
    // слешем — нужно добавить use-импорт в начало файла: use Service\Logger\LoggerService;
    protected function logError(string $message): void
    {
        $logger = new \Service\Logger\LoggerService();
        $logger->error($message);
        $logger->errorToDb($message, 'error');
    }

}
