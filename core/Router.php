<?php

namespace ECommerce\Core;

/**
 * Router Class
 * 
 * Handles routing to controllers
 */
class Router
{
    private $routes = [
        'GET' => [],
        'POST' => [],
    ];
    private $notFoundCallback;
    private $middlewares = [];

    /**
     * Register GET route
     * 
     * @param string $path
     * @param string $controller@method
     * @param array $middleware
     * @return void
     */
    public function get($path, $controller, $middleware = [])
    {
        $this->routes['GET'][$path] = [
            'controller' => $controller,
            'middleware' => $middleware,
        ];
    }

    /**
     * Register POST route
     * 
     * @param string $path
     * @param string $controller@method
     * @param array $middleware
     * @return void
     */
    public function post($path, $controller, $middleware = [])
    {
        $this->routes['POST'][$path] = [
            'controller' => $controller,
            'middleware' => $middleware,
        ];
    }

    /**
     * Register both GET and POST routes
     * 
     * @param string $path
     * @param string $controller
     * @param array $middleware
     * @return void
     */
    public function match($path, $controller, $middleware = [])
    {
        $this->get($path, $controller, $middleware);
        $this->post($path, $controller, $middleware);
    }

    /**
     * Set 404 callback
     * 
     * @param callable $callback
     * @return void
     */
    public function notFound($callback)
    {
        $this->notFoundCallback = $callback;
    }

    /**
     * Register middleware
     * 
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public function middleware($name, $callback)
    {
        $this->middlewares[$name] = $callback;
    }

    /**
     * Dispatch request
     * 
     * @param string $method
     * @param string $path
     * @return mixed
     */
    public function dispatch($method, $path)
    {
        // Normalize path
        $path = parse_url($path, PHP_URL_PATH);
        $basePath = preg_replace('/\/public$/', '', dirname($_SERVER['SCRIPT_NAME']));
        if ($basePath !== '/') {
            $path = str_replace($basePath, '', $path);
        }
        $path = $path ?: '/';

        // Look for exact match first
        if (isset($this->routes[$method][$path])) {
            return $this->executeRoute($this->routes[$method][$path]);
        }

        // Look for parametrized routes
        foreach ($this->routes[$method] as $route => $handler) {
            if ($this->matches($route, $path, $params)) {
                return $this->executeRoute($handler, $params);
            }
        }

        // Not found
        if ($this->notFoundCallback) {
            call_user_func($this->notFoundCallback);
        }

        http_response_code(404);
        echo '404 - Page Not Found';
        exit;
    }

    /**
     * Check if route pattern matches path
     * 
     * @param string $route
     * @param string $path
     * @param array &$params
     * @return bool
     */
    private function matches($route, $path, &$params = [])
    {
        $pattern = preg_replace_callback('/\{(\w+)\}/', function ($m) {
            return '(?P<' . $m[1] . '>[^/]+)';
        }, $route);

        $pattern = str_replace('/', '\/', $pattern);
        return preg_match("/^{$pattern}$/", $path, $params) && $params;
    }

    /**
     * Execute route
     * 
     * @param array $route
     * @param array $params
     * @return mixed
     */
    private function executeRoute($route, $params = [])
    {
        // Execute middlewares
        foreach ($route['middleware'] as $middleware) {
            if (isset($this->middlewares[$middleware])) {
                call_user_func($this->middlewares[$middleware]);
            }
        }

        // Parse controller and method
        [$controller, $method] = explode('@', $route['controller']);

        // Build full class names (support both legacy and new namespaces)
        $controllerCandidates = [
            '\\ECommerce\\App\\Controllers\\' . $controller,
            '\\App\\Controllers\\' . $controller,
        ];
        $controllerClass = null;

        foreach ($controllerCandidates as $candidate) {
            if (class_exists($candidate)) {
                $controllerClass = $candidate;
                break;
            }
        }

        if ($controllerClass === null) {
            throw new \Exception("Controller not found for route: {$route['controller']}");
        }

        $instance = new $controllerClass();

        if (!method_exists($instance, $method)) {
            throw new \Exception("Method not found: {$controllerClass}@{$method}");
        }

        // Call method with parameters
        return call_user_func_array([$instance, $method], $params);
    }
}
