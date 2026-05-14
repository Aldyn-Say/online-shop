<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна

namespace Core\Autoload;

class Autoloader
{
    // TODO (PSR-12 §4.4): открывающая { должна быть на следующей строке, а не на той же строке после )
    // TODO (PSR-1 §4.3): метод register() не имеет return type — нужно добавить: void
    public static function register(string $dir){
        // TODO (PSR-12 §6): замыкание ($autoload) — открывающая { должна быть на той же строке
        // что и function, а не на следующей (для замыканий правило отличается от методов/функций)
        $autoload = function (string $className) use ($dir)
        {
            $path = str_replace('\\', '/', $className);
            // TODO (PSR-12 §2.4): двойной пробел перед '=' — лишний пробел нужно убрать
            $path =  "$dir/$path.php";
            if (file_exists($path)) {
                require_once $path;
                return true;
            }
            return false;
        };
        spl_autoload_register($autoload);
    }
}