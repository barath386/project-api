<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * Router
 * --------------------------------------------------
 * - Registers routes by HTTP method
 * - Resolves incoming requests
 * - Supports protected routes (JWT middleware)
 */

class Router
{
    /**
     * Registered routes
     *
     * @var array
     */
    private array $routes = [];

    /* --------------------------------------------------
     | Route Registration Methods
     |-------------------------------------------------- */

    public function get(string $path, string $controller, string $method, bool $protected = false): void
    {
        $this->addRoute('GET', $path, $controller, $method, $protected);
    }

    public function post(string $path, string $controller, string $method, bool $protected = false): void
    {
        $this->addRoute('POST', $path, $controller, $method, $protected);
    }

    public function put(string $path, string $controller, string $method, bool $protected = false): void
    {
        $this->addRoute('PUT', $path, $controller, $method, $protected);
    }

    public function patch(string $path, string $controller, string $method, bool $protected = false): void
    {
        $this->addRoute('PATCH', $path, $controller, $method, $protected);
    }

    public function delete(string $path, string $controller, string $method, bool $protected = false): void
    {
        $this->addRoute('DELETE', $path, $controller, $method, $protected);
    }

    /**
     * Internal route registrar
     */
    private function addRoute(
        string $httpMethod,
        string $path,
        string $controller,
        string $method,
        bool $protected
    ): void {
        $this->routes[$httpMethod][$path] = [
            'controller' => $controller,
            'method'     => $method,
            'protected'  => $protected,
        ];
    }

    /* --------------------------------------------------
     | Request Resolver
     |-------------------------------------------------- */

    public function resolve(): void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Normalize base path (project folder)
        $path = str_replace(
            ['/JWT-Authentication/public', '/JWT-Authentication'],
            '',
            $requestUri
        );

        // Check route existence
        if (!isset($this->routes[$httpMethod][$path])) {
            Response::json(
                ['status' => false, 'message' => 'Route not found', 'path' => $path],
                404
            );
        }

        $route = $this->routes[$httpMethod][$path];

        // Run auth middleware for protected routes
        if ($route['protected']) {
            $GLOBALS['user'] = AuthMiddleware::handle();
        }

        $controllerName = $route['controller'];
        $methodName     = $route['method'];

        if (!class_exists($controllerName)) {
            Response::json(
                ['status' => false, 'message' => "Controller {$controllerName} not found"],
                500
            );
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            Response::json(
                ['status' => false, 'message' => "Method {$methodName} not found"],
                500
            );
        }

        // Special case: GET /api/patients?id=1
        if (
            $httpMethod === 'GET'
            && $path === '/api/patients'
            && isset($_GET['id'])
        ) {
            $controller->show();
            return;
        }

        // Default controller execution
        $controller->{$methodName}();
    }
}
