<?php
namespace Core;

class App
{
    private array $routes = [];

    public function run()
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        if (array_key_exists($requestUri, $this->routes)) {
            $routeMethods = $this->routes[$requestUri];
            if (array_key_exists($requestMethod, $routeMethods)) {
                $handler = $routeMethods[$requestMethod];
                $class = $handler['class'];
                $method = $handler['method'];
                $controller = new $class();
                $result = $controller->$method();

                // Если контроллер вернул массив с ключом 'redirect' — делаем редирект
                if (is_array($result) && isset($result['redirect'])) {
                    header('Location: ' . $result['redirect']);
                    exit;
                }
            } else {
                echo "$requestMethod для адреса $requestUri не поддерживается";
            }
        } else {
            http_response_code(404);
            require_once  './../Views/404.php';
        }
    }

    public function addRoute(string $route, string $routeMethod, string $className, string $method)
    {
        $this->routes[$route][$routeMethod] = [
            'class' => $className,
            'method' => $method
        ];
    }
}