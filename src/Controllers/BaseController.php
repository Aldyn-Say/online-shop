<?php

namespace Controllers;

use Service\Auth\AuthInterface;
use Service\Auth\AuthSessionService;

abstract class BaseController
{
    protected AuthSessionService $authService;


    public function __construct()
    {
        $this->authService = new AuthSessionService();
    }

    protected function logError(string $message): void
    {
        $logger = new \Service\Logger\LoggerService();
        $logger->error($message);
        $logger->errorToDb($message, 'error');
    }

}
