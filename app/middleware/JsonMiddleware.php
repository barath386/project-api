<?php

/**
 * JSON Middleware
 * - Forces application/json for POST/PUT/PATCH
 * - Parses JSON body
 * - Attaches decoded data to $_REQUEST['body']
 */

class JsonMiddleware
{
    public static function handle(): void
    {
        // Always respond with JSON
        header('Content-Type: application/json');

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Only enforce JSON for methods with body
        if (!in_array($method, ['POST', 'PUT', 'PATCH'])) {
            return;
        }

        // Validate Content-Type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') === false) {
            Response::json(false, 'Content-Type must be application/json', [], 415);
        }

        // Read raw input
        $rawInput = file_get_contents('php://input');

        if ($rawInput === false || trim($rawInput) === '') {
            Response::json(false, 'Request body cannot be empty', [], 400);
        }

        // Decode JSON
        $data = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::json(false, 'Invalid JSON payload', [], 400);
        }

        // Attach parsed body to request
        $_REQUEST['body'] = $data;
    }
}
