<?php
/**
 * Main Entry Point
 * Core PHP REST API
 * Single entry for all requests
 */

/* =====================================
   ERROR REPORTING (DEV ONLY)
   Disable in production
===================================== */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =====================================
   LOAD CONFIG (.env is loaded here)
===================================== */
require_once __DIR__ . '/../config/config.php';

/* =====================================
   CORE CLASSES
===================================== */
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Router.php';

/* =====================================
   HELPERS
===================================== */
require_once __DIR__ . '/../app/helpers/Response.php';
require_once __DIR__ . '/../app/helpers/JWT.php';

/* =====================================
   MIDDLEWARE
===================================== */
require_once __DIR__ . '/../app/middleware/JsonMiddleware.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';

/* =====================================
   MODELS
===================================== */
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Patient.php';

/* =====================================
   CONTROLLERS
===================================== */
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/PatientController.php';

/* =====================================
   GLOBAL JSON MIDDLEWARE
   - Validates JSON
   - Populates $_REQUEST['body']
===================================== */
JsonMiddleware::handle();

/* =====================================
   ROUTER INITIALIZATION
===================================== */
$router = new Router();

/* =====================================
   HEALTH CHECK (OPTIONAL)
===================================== */
$router->get('/', function () {
    Response::json(true, 'API is running', [
        'name' => 'Core PHP REST API',
        'version' => '1.0.0'
    ]);
});

/* =====================================
   AUTH ROUTES (PUBLIC)
===================================== */
$router->post('/api/register', [AuthController::class, 'register']);
$router->post('/api/login', [AuthController::class, 'login']);

/* =====================================
   PATIENT ROUTES (JWT PROTECTED)
===================================== */
$router->get('/api/patients', function () {
    AuthMiddleware::handle();
    (new PatientController())->index();
});

$router->post('/api/patients', function () {
    AuthMiddleware::handle();
    (new PatientController())->store();
});

$router->put('/api/patients/{id}', function ($id) {
    AuthMiddleware::handle();
    (new PatientController())->update($id);
});

$router->delete('/api/patients/{id}', function ($id) {
    AuthMiddleware::handle();
    (new PatientController())->destroy($id);
});

/* =====================================
   DISPATCH REQUEST (MUST BE LAST)
===================================== */
$router->dispatch();
