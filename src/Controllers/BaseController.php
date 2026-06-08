<?php

declare(strict_types=1);

namespace Controllers;

use Service\Auth\AuthInterface;
use Service\Auth\AuthSessionService;
use Service\Logger\LoggerService;

abstract class BaseController
{
    protected AuthInterface $authService;
    protected LoggerService $logger;

    public function __construct(?AuthInterface $authService = null)
    {
        $this->authService = $authService ?? new AuthSessionService();
        $this->logger = new LoggerService();
    }

    protected function logError(string $message): void
    {
        $this->logger->error($message);
        $this->logger->errorToDb($message, 'error');
    }

}
