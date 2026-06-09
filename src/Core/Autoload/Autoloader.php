<?php

declare(strict_types=1);

namespace Core\Autoload;

class Autoloader
{
    public static function register(string $dir): void
    {
        $dir = rtrim($dir, '/');
        $autoload = function (string $className) use ($dir) {
            $path = str_replace('\\', '/', $className);
            $path = "$dir/$path.php";
            if (file_exists($path)) {
                require_once $path;
                return true;
            }

            return false;
        };
        spl_autoload_register($autoload);
    }
}
