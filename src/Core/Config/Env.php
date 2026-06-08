<?php

declare(strict_types=1);

namespace Core\Config;

final class Env
{
    private static bool $loaded = false;

    public static function get(string $key, ?string $default = null): ?string
    {
        self::load();

        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }

    private static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = dirname(__DIR__, 3) . '/.env';
        if (!is_readable($envFile)) {
            self::$loaded = true;
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            self::$loaded = true;
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }

        self::$loaded = true;
    }
}
