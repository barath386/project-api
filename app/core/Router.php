<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * Professional Router Class
 * --------------------------------------------------
 * Handles subfolder detection, Regex route matching, 
 * and Middleware execution.
 */
class Router
{
    /**
     * Stores all registered routes
     */
    private array $routes = [];

    /* --------------------------------------------------
     | Route Registration (Fluent Methods)
     | -------------------------------------------------- */

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

    private function addRoute(string $httpMethod, string $path, string $controller, string $method, bool $protected): void
    {
        $this->routes[] = [
            'httpMethod' => $httpMethod,
            'path'       => $path,
            'controller' => $controller,
            'method'     => $method,
            'protected'  => $protected,
        ];
    }

    /* --------------------------------------------------
     | Request Resolver Engine
     | -------------------------------------------------- */

    public function resolve(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // 1. AUTO-STRIP PROJECT FOLDER
        // This detects '/project-api' or '/JWT-Authentication' automatically
        $projectName = '/project-api'; 
        
        // If your folder name is different, you can add it to this array
        $possibleFolders = [$projectName, '/JWT-Authentication', '/public'];
        
        $cleanUri = str_replace($possibleFolders, '', $requestUri);
        $uri = '/' . trim($cleanUri, '/');

        // 2. SEARCH FOR MATCHING ROUTE
        foreach ($this->routes as $route) {
            // Convert {id} to a regex capture group ([a-zA-Z0-9_]+)
            $pattern = "@^" . preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $route['path']) . "$@";

            if ($route['httpMethod'] === $requestMethod && preg_match($pattern, $uri, $matches)) {
                // Remove the first element (the full string match)
                array_shift($matches);

                // 3. EXECUTE MIDDLEWARE (AUTH)
                if ($route['protected']) {
                    // This sets AuthMiddleware::$currentUserId etc.
                    $auth = new AuthMiddleware();
                    $auth->handle(); 
                }

                $controllerName = $route['controller'];
                $methodName     = $route['method'];

                // 4. EXECUTE CONTROLLER
                if (!class_exists($controllerName)) {
                    Response::json(500, ["message" => "Controller {$controllerName} not found"]);
                }

                $controller = new $controllerName();

                if (!method_exists($controller, $methodName)) {
                    Response::json(500, ["message" => "Method {$methodName} not found in {$controllerName}"]);
                }

                // Call the method and pass regex matches (like $id) as arguments
                call_user_func_array([$controller, $methodName], $matches);
                return;
            }
        }

        // 5. 404 - NOTHING MATCHED
        Response::json(404, [
            "status"     => false,
            "message"    => "Route not found",
            "debug_info" => [
                "method"      => $requestMethod,
                "original_uri"=> $requestUri,
                "cleaned_uri" => $uri
            ]
        ]);
    }
}