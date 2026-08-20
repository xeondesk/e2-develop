<?php
declare(strict_types=1);

namespace Nexo\Api;

use Nexo\Container\ContainerInterface;

final class Routes
{
    /** @var array<string, array{method: string, handler: callable}> */
    private array $routes = [];

    public function __construct(private ContainerInterface $container)
    {
    }

    public function get(string $path, callable $handler): void
    {
        $this->routes[$path] = ['method' => 'GET', 'handler' => $handler];
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes[$path] = ['method' => 'POST', 'handler' => $handler];
    }

    public function put(string $path, callable $handler): void
    {
        $this->routes[$path] = ['method' => 'PUT', 'handler' => $handler];
    }

    public function delete(string $path, callable $handler): void
    {
        $this->routes[$path] = ['method' => 'DELETE', 'handler' => $handler];
    }

    public function dispatch(string $method, string $path): mixed
    {
        $route = $this->routes[$path] ?? null;

        if (!$route || $route['method'] !== $method) {
            http_response_code(404);
            return ['error' => 'Not Found', 'path' => $path, 'method' => $method];
        }

        try {
            $handler = $route['handler'];
            $result = $handler($this->container);
            return $result;
        } catch (\Throwable $e) {
            http_response_code(500);
            return [
                'error' => 'Internal Server Error',
                'message' => $e->getMessage(),
                'trace' => $this->container->get('config')->getBool('app.debug') ? $e->getTraceAsString() : null,
            ];
        }
    }
}