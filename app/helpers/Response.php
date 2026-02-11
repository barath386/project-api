<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * HTTP Response Helper
 * --------------------------------------------------
 * Provides standardized JSON responses for the API
 */

class Response
{
    /**
     * Send JSON response and terminate execution
     *
     * @param mixed $data
     * @param int   $statusCode
     */
    public static function json(mixed $data, int $statusCode = 200): void
    {
        // Set HTTP status code
        http_response_code($statusCode);

        // Set response headers
        header('Content-Type: application/json; charset=utf-8');

        // Encode and send response
        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        exit;
    }
}
