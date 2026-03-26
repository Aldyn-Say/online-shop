<?php
namespace Core;

class App
{
    private array $routes = [];

    public function run()
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
                $result = $controller->$method();

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

    public function post(string $route, string $className, string $method)
    {
        $this->routes[$route]['POST'] = [
            'class' => $className,
            'method' => $method
        ];
    }
    public function get(string $route, string $className, string $method)
    {
        $this->routes[$route]['GET'] = [
            'class' => $className,
            'method' => $method
        ];
    }
}