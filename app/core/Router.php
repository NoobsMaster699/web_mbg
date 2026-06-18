<?php

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = $this->normalize(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $params = $this->match($route, $path);
            if ($params !== null) {
                [$controller, $action] = $handler;
                require_once app_path('controllers/' . $controller . '.php');
                (new $controller())->$action(...$params);
                return;
            }
        }

        http_response_code(404);
        echo 'Halaman tidak ditemukan.';
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function match(string $route, string $path): ?array
    {
        $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $route);
        if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
            array_shift($matches);
            return $matches;
        }

        return null;
    }
}

