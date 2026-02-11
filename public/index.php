<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * Application Entry Point
 * --------------------------------------------------
 * - Loads configuration
 * - Registers autoloader
 * - Executes global middleware
 * - Defines routes
 * - Resolves request
 */

// --------------------------------------------------
// Load global configuration
// --------------------------------------------------
require_once dirname(__DIR__) . '/config/config.php';

// --------------------------------------------------
// PSR-4 Style Autoloader (Custom)
// --------------------------------------------------
spl_autoload_register(function (string $class): void {

    $basePath = dirname(__DIR__) . '/app/';

    $directories = [
        'controllers/',
        'models/',
        'helpers/',
        'core/',
        'middleware/',
    ];

    foreach ($directories as $directory) {
        $filePath = $basePath . $directory . $class . '.php';

        if (file_exists($filePath)) {
            require_once $filePath;
            return;
        }
    }
});

// --------------------------------------------------
// Global Middleware (Runs for every request)
// --------------------------------------------------
JsonMiddleware::handle();

// --------------------------------------------------
// Initialize Router
// --------------------------------------------------
$router = new Router();

// --------------------------------------------------
// Authentication Routes (Public)
// --------------------------------------------------
$router->post('/api/register', AuthController::class, 'register');
$router->post('/api/login', AuthController::class, 'login');
$router->post('/api/refresh', AuthController::class, 'refresh');

// --------------------------------------------------
// Patient Routes (Protected)
// --------------------------------------------------
$router->get('/api/patients', PatientController::class, 'index', true);
$router->post('/api/patients', PatientController::class, 'create', true);
$router->put('/api/patients', PatientController::class, 'update', true);
$router->patch('/api/patients', PatientController::class, 'patch', true);
$router->delete('/api/patients', PatientController::class, 'delete', true);

// --------------------------------------------------
// Resolve Incoming Request
// --------------------------------------------------
$router->resolve();
