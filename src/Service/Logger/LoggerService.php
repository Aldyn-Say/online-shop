<?php

namespace Service\Logger;

use Models\ApplicationLog;
use Throwable;

class LoggerService
{
    public function error(string $message): void
    {
        $path = dirname(__DIR__, 2) . '/Storage/Log/errors.txt';
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $line = date('[Y-m-d H:i:s] ') . $message . PHP_EOL;
        file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }

    public function errorToDb(string $message, string $level = 'error'): void
    {
        try {
            (new ApplicationLog())->write($level, $message);
        } catch (Throwable) {
            // Не ломаем приложение из-за недоступной БД
        }
    }
}
