<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * Global Configuration File
 * --------------------------------------------------
 * ✔ Safe constant definitions
 * ✔ No duplicate define warnings
 * ✔ JWT Access (40s) / Refresh (90m)
 * ✔ Environment-aware error handling
 */

// ==================================================
// 1️⃣ LOAD ENV FILE (if exists)
// ==================================================
$envFile = __DIR__ . '/env.php';
if (file_exists($envFile)) {
    require_once $envFile;
}

// ==================================================
// 2️⃣ APPLICATION CONFIG
// ==================================================
defined('APP_NAME')   || define('APP_NAME', 'Project API');
defined('APP_ENV')    || define('APP_ENV', 'local');
defined('APP_DEBUG')  || define('APP_DEBUG', true);

// ==================================================
// 3️⃣ DATABASE CONFIG
// ==================================================
defined('DB_HOST') || define('DB_HOST', '127.0.0.1');
defined('DB_NAME') || define('DB_NAME', 'project_api');
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');

// ==================================================
// 4️⃣ JWT CONFIG
// ==================================================
defined('JWT_SECRET')        || define('JWT_SECRET', 'my_super_secret');
defined('ACCESS_TOKEN_EXP') || define('ACCESS_TOKEN_EXP', 40);     // 40 seconds
defined('REFRESH_TOKEN_EXP')|| define('REFRESH_TOKEN_EXP', 5400);  // 90 minutes

// ==================================================
// 5️⃣ TIMEZONE
// ==================================================
date_default_timezone_set('Asia/Kolkata');

// ==================================================
// 6️⃣ ERROR REPORTING (ENV BASED)
// ==================================================
if (APP_ENV === 'local' && APP_DEBUG === true) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
