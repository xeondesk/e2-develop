<?php
declare(strict_types=1);

namespace Nexo\Http;

final class Router
{
    /** @var array<int, array{method: string, pattern: string, regex: string, handler: callable}> */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $regex = '#^' . preg_replace('#:([a-zA-Z_][a-zA-Z0-9_]*)#', '(?P<$1>[^/]+)', $pattern) . '$#';
        $this->routes[] = ['method' => strtoupper($method), 'pattern' => $pattern, 'regex' => $regex, 'handler' => $handler];
    }

    public function get(string $pattern, callable $handler): void { $this->add('GET', $pattern, $handler); }
    public function post(string $pattern, callable $handler): void { $this->add('POST', $pattern, $handler); }
    public function put(string $pattern, callable $handler): void { $this->add('PUT', $pattern, $handler); }
    public function delete(string $pattern, callable $handler): void { $this->add('DELETE', $pattern, $handler); }

    public function dispatch(HttpRequest $request): HttpResponse
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }
            if (preg_match($route['regex'], $request->path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request = new HttpRequest($request->method, $request->path, $request->query, $request->body, $request->headers, $params);
                try {
                    $result = ($route['handler'])($request);
                    return $result instanceof HttpResponse ? $result : HttpResponse::json($result);
                } catch (\Throwable $e) {
                    return HttpResponse::error('internal_error', $e->getMessage(), 500);
                }
            }
        }
        return HttpResponse::error('not_found', "No route for {$request->method} {$request->path}", 404);
    }
}
