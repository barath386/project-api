<?php

/**
 * Response Helper
 * Handles all JSON API responses
 */

class Response
{
    /**
     * Base JSON response
     */
    public static function json(
        bool $status,
        string $message,
        array $data = [],
        int $code = 200
    ): void {
        http_response_code($code);
        header('Content-Type: application/json');

        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ]);

        exit;
    }

    /**
     * Success response
     */
    public static function success(
        string $message,
        array $data = [],
        int $code = 200
    ): void {
        self::json(true, $message, $data, $code);
    }

    /**
     * Error response
     */
    public static function error(
        string $message,
        int $code = 400,
        array $data = []
    ): void {
        self::json(false, $message, $data, $code);
    }

    /**
     * Validation error response
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed'
    ): void {
        self::json(false, $message, $errors, 422);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(
        string $message = 'Unauthorized'
    ): void {
        self::json(false, $message, [], 401);
    }

    /**
     * Not found response
     */
    public static function notFound(
        string $message = 'Resource not found'
    ): void {
        self::json(false, $message, [], 404);
    }
}
