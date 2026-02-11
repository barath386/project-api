<?php

<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

spl_autoload_register(function ($class) {
    $basePath = dirname(__DIR__) . '/api/';
    foreach (['middlewares/','controllers/','models/','helpers/','core/'] as $f) {
        $file = $basePath . $f . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

JsonMiddleware::handle();

// continue router...



// ==================================================
// 3️⃣ DEBUG CHECK
// ==================================================
// if (!class_exists('JsonMiddleware')) {
//     die('❌ JsonMiddleware NOT LOADED');
// }

// die('✅ JsonMiddleware LOADED SUCCESSFULLY');


// ==================================================
// 4️⃣ RUN GLOBAL MIDDLEWARE
// ==================================================
JsonMiddleware::handle();

// ==================================================
// 5️⃣ INIT ROUTER
// ==================================================
$router = new Router();

// ==================================================
// 6️⃣ ROUTES
// ==================================================
$router->post('/api/register', AuthController::class, 'register');
$router->post('/api/login', AuthController::class, 'login');
$router->post('/api/refresh', AuthController::class, 'refresh');

$router->get('/api/patients', PatientController::class, 'index', true);
$router->post('/api/patients', PatientController::class, 'create', true);
$router->put('/api/patients', PatientController::class, 'update', true);
$router->patch('/api/patients', PatientController::class, 'patch', true);
$router->delete('/api/patients', PatientController::class, 'delete', true);

// ==================================================
// 7️⃣ RESOLVE REQUEST
// ==================================================
$router->resolve();
