<?php

/**
 * Router Class
 * Handles route registration and request dispatching
 */

class Router
{
    /**
     * Registered routes
     */
    private array $routes = [];

    /* =====================================
       ROUTE REGISTRATION METHODS
    ===================================== */

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Register a route
     */
    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        // Convert dynamic params: /api/patients/{id} → ([0-9]+)
        $path = preg_replace('#\{[a-zA-Z_]+\}#', '([0-9]+)', $path);

        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => '#^' . $path . '$#',
            'handler' => $handler
        ];
    }

    /* =====================================
       REQUEST DISPATCHER
    ===================================== */

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        /*
        |--------------------------------------------------------------------------
        | REMOVE PROJECT FOLDER (SUB-FOLDER SUPPORT)
        | Example:
        | /project-api/api/login → /api/login
        |--------------------------------------------------------------------------
        */
        $basePath = '/project-api'; // change if folder name changes

        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        if ($uri === '') {
            $uri = '/';
        }

        /*
        |--------------------------------------------------------------------------
        | MATCH ROUTES
        |--------------------------------------------------------------------------
        */
        foreach ($this->routes as $route) {

            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['path'], $uri, $matches)) {

                // Remove full match
                array_shift($matches);

                // Call controller or closure
                if (is_array($route['handler'])) {
                    [$controller, $action] = $route['handler'];

                    if (!class_exists($controller) || !method_exists($controller, $action)) {
                        Response::json(false, 'Controller or method not found', [], 500);
                    }

                    call_user_func_array([new $controller(), $action], $matches);
                } else {
                    call_user_func_array($route['handler'], $matches);
                }

                return;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NO ROUTE MATCHED
        |--------------------------------------------------------------------------
        */
        Response::json(false, 'Route not found', [], 404);
    }
}
