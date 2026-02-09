<?php
/**
 * Configuration File
 * Loads environment variables from .env
 * Defines DB & JWT constants
 */

class Config
{
    private static array $env = [];
    private static bool $loaded = false;

    /**
     * Load environment variables from .env file
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $envPath = __DIR__ . '/../.env';

        if (!file_exists($envPath)) {
            die('Error: .env file not found');
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {

            $line = trim($line);

            // Skip comments or empty lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key = trim($key);
            $value = trim($value);

            self::$env[$key] = $value;

            // Optional: expose to getenv()
            putenv("$key=$value");
        }

        self::defineConstants();
        self::$loaded = true;
    }

    /**
     * Get environment variable safely
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$env[$key] ?? $default;
    }

    /**
     * Define global constants
     */
    private static function defineConstants(): void
    {
        // Database
        define('DB_HOST', self::get('DB_HOST', '127.0.0.1'));
        define('DB_PORT', self::get('DB_PORT', '3306'));
        define('DB_NAME', self::get('DB_NAME', ''));
        define('DB_USER', self::get('DB_USER', ''));
        define('DB_PASS', self::get('DB_PASS', ''));

        // JWT
        define('JWT_SECRET', self::get('JWT_SECRET', 'secret'));
        define('JWT_EXPIRY', (int) self::get('JWT_EXPIRY', 3600));
    }
}

/* =====================================
   AUTO LOAD CONFIG ON INCLUDE
===================================== */
Config::load();
