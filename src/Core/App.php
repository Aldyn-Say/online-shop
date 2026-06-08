<?php

declare(strict_types=1);

namespace Core;

use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use Request\AddProductRequest;
use Request\RegistrateRequest;

class App
{
    private array $routes = [];

    public function run(): void
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($requestUri, PHP_URL_PATH);
        if ($path === false) {
            $path = $requestUri;
        }

        if (array_key_exists($path, $this->routes)) {
            $routeMethods = $this->routes[$path];
            if (array_key_exists($requestMethod, $routeMethods)) {
                $handler = $routeMethods[$requestMethod];
                $class = $handler['class'];
                $method = $handler['method'];
                $controller = new $class();

                $args = $this->resolveControllerArguments($class, $method);
                $result = $controller->$method(...$args);

                if (is_array($result) && isset($result['redirect'])) {
                    header('Location: ' . $result['redirect']);
                    exit;
                }
            } else {
                http_response_code(405);
                header('Allow: GET, POST');
                echo "$requestMethod для адреса $requestUri не поддерживается";
            }
        } else {
            http_response_code(404);
            require_once __DIR__ . '/../../Views/404.php';
        }
    }


    private function resolveControllerArguments(string $controllerClass, string $actionMethod): array
    {
        try {
            $refMethod = new ReflectionMethod($controllerClass, $actionMethod);
        } catch (ReflectionException) {
            return [];
        }

        $args = [];
        foreach ($refMethod->getParameters() as $param) {
            $type = $param->getType();
            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            $className = $type->getName();
            if ($className === AddProductRequest::class) {
                $args[] = new AddProductRequest($_POST);
            } elseif ($className === RegistrateRequest::class) {
                $args[] = new RegistrateRequest($_POST);
            }
        }

        return $args;
    }

    public function post(string $route, string $className, string $method): void
    {
        $this->routes[$route]['POST'] = [
            'class' => $className,
            'method' => $method,
        ];
    }

    public function get(string $route, string $className, string $method): void
    {
        $this->routes[$route]['GET'] = [
            'class' => $className,
            'method' => $method,
        ];
    }
}
