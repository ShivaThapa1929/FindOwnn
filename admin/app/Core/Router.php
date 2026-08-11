<?php

namespace App\Core;

/**
 * Router — Custom URL router with middleware support, route groups,
 * named routes, and parameter capture.
 */
class Router
{
    private array $routes     = [];
    private array $middleware = [];
    private array $named      = [];
    private string $prefix    = '';
    private array  $groupMiddleware = [];

    // ----------------------------------------------------------------
    // Registration
    // ----------------------------------------------------------------

    public function get(string $path, array|callable $handler, array $middleware = []): static
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): static
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array|callable $handler, array $middleware = []): static
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array|callable $handler, array $middleware = []): static
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function any(string $path, array|callable $handler, array $middleware = []): static
    {
        foreach (['GET','POST','PUT','DELETE','PATCH'] as $m) {
            $this->addRoute($m, $path, $handler, $middleware);
        }
        return $this;
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middleware = []): static
    {
        $fullPath = rtrim($this->prefix . $path, '/') ?: '/';
        $allMiddleware = array_merge($this->groupMiddleware, $middleware);
        $this->routes[] = compact('method', 'fullPath', 'handler', 'allMiddleware');
        return $this;
    }

    public function name(string $name): static
    {
        $last = array_key_last($this->routes);
        if ($last !== null) {
            $this->named[$name] = $this->routes[$last]['fullPath'];
            $this->routes[$last]['name'] = $name;
        }
        return $this;
    }

    /** Group routes with shared prefix and/or middleware */
    public function group(array $options, callable $callback): void
    {
        $prevPrefix     = $this->prefix;
        $prevMiddleware = $this->groupMiddleware;

        $this->prefix          = $prevPrefix . ($options['prefix'] ?? '');
        $this->groupMiddleware = array_merge($prevMiddleware, $options['middleware'] ?? []);

        $callback($this);

        $this->prefix          = $prevPrefix;
        $this->groupMiddleware = $prevMiddleware;
    }

    // ----------------------------------------------------------------
    // Dispatch
    // ----------------------------------------------------------------

    public function dispatch(Request $request): void
    {
        $method = strtoupper($request->getMethod());
        $uri    = $this->normalizeUri($request->getUri());

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = $this->buildPattern($route['fullPath']);
            if (preg_match($pattern, $uri, $matches)) {
                http_response_code(200); // Explicitly override Apache's initial 404 rewrite status
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request->setParams($params);

                // Run middleware chain
                $this->runMiddleware($route['allMiddleware'], $request, function () use ($route, $request) {
                    $this->callHandler($route['handler'], $request);
                });
                return;
            }
        }

        // 404
        http_response_code(404);
        $this->render404();
    }

    private function runMiddleware(array $middlewareList, Request $request, callable $next): void
    {
        if (empty($middlewareList)) {
            $next();
            return;
        }

        $middlewareName = array_shift($middlewareList);
        $class = $this->resolveMiddleware($middlewareName);

        if (!class_exists($class)) {
            throw new \RuntimeException("Middleware [{$class}] not found.");
        }

        (new $class())->handle($request, function () use ($middlewareList, $request, $next) {
            $this->runMiddleware($middlewareList, $request, $next);
        });
    }

    private function resolveMiddleware(string $name): string
    {
        $map = [
            'auth'        => \App\Middleware\AuthMiddleware::class,
            'guest'       => \App\Middleware\GuestMiddleware::class,
            'role.super'  => \App\Middleware\RoleSuperAdmin::class,
            'role.admin'  => \App\Middleware\RoleAdmin::class,
            'role.owner'  => \App\Middleware\RoleOwner::class,
            'csrf'        => \App\Middleware\CsrfMiddleware::class,
        ];
        return $map[$name] ?? $name;
    }

    private function callHandler(array|callable $handler, Request $request): void
    {
        if (is_callable($handler)) {
            call_user_func($handler, $request);
            return;
        }

        [$controllerClass, $method] = $handler;
        $fullClass = str_contains($controllerClass, '\\')
            ? $controllerClass
            : "App\\Controllers\\{$controllerClass}";

        if (!class_exists($fullClass)) {
            throw new \RuntimeException("Controller [{$fullClass}] not found.");
        }

        $controller = new $fullClass();
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method [{$method}] not found in [{$fullClass}].");
        }

        $controller->$method($request);
    }

    private function buildPattern(string $path): string
    {
        // Convert {param} and {param?} to named capture groups
        $pattern = preg_replace('/\{(\w+)\?\}/', '(?P<$1>[^/]*)?', $path);
        $pattern = preg_replace('/\{(\w+)\}/',   '(?P<$1>[^/]+)',  $pattern);
        $pattern = '#^' . $pattern . '$#';
        return $pattern;
    }

    private function normalizeUri(string $uri): string
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';

        // SCRIPT_NAME examples:
        //  /findownn_website/admin/public/index.php   (direct access)
        //  /findownn_website/admin/index.php           (root htaccess rewrite)
        // We want to find the "app base" = everything before /public or before index.php
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

        // Remove /public/index.php or /index.php from the end
        $base = preg_replace('#(/public)?/index\.php$#', '', $scriptName);
        $base = rtrim($base, '/');

        // Strip the base from the incoming URI
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        // Also strip a leading /public if it survived (shouldn't normally)
        $uri = preg_replace('#^/public#', '', $uri);

        // Also strip leading /admin if present (so `/admin/venues` becomes `/venues`)
        if (str_starts_with($uri, '/admin/')) {
            $uri = substr($uri, 6);
        } elseif ($uri === '/admin') {
            $uri = '/';
        }

        $uri = '/' . ltrim($uri, '/');
        return rtrim($uri, '/') ?: '/';
    }

    public function route(string $name, array $params = []): string
    {
        if (!isset($this->named[$name])) return '#';
        $url = $this->named[$name];
        foreach ($params as $key => $val) {
            $url = preg_replace('/\{' . $key . '\??}/', $val, $url);
        }
        return $url;
    }

    private function render404(): void
    {
        if (file_exists(__DIR__ . '/../../views/errors/404.php')) {
            require __DIR__ . '/../../views/errors/404.php';
        } else {
            echo '<h1>404 — Page Not Found</h1>';
        }
    }
}
