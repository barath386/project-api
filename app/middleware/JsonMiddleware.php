<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * JSON Request Middleware
 * --------------------------------------------------
 * - Validates JSON payload for write requests
 * - Decodes request body into an array
 * - Makes data globally accessible for controllers
 */

class JsonMiddleware
{
    /**
     * Handle incoming JSON request
     */
    public static function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Apply only to methods that usually carry a body
        if (!in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            return;
        }

        $rawInput = file_get_contents('php://input');

        // Allow empty body (e.g., PUT without payload)
        if ($rawInput === '' || $rawInput === false) {
            $GLOBALS['request_data'] = [];
            return;
        }

        $data = json_decode($rawInput, true);

        // Validate JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::json(
                ['status' => false, 'message' => 'Invalid JSON payload'],
                400
            );
        }

        // Store decoded data for controllers
        $GLOBALS['request_data'] = $data;
    }
}
