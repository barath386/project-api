<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * Database Connection (PDO)
 * --------------------------------------------------
 */

require_once __DIR__ . '/env.php';

class Database
{
    private static ?PDO $connection = null;

    public static function connect(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        try {

            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                DB_DRIVER,
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            self::$connection = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT         => false,
                ]
            );

            return self::$connection;

        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode([
                'status'  => false,
                'message' => 'Database connection failed',
            ]));
        }
    }
}
