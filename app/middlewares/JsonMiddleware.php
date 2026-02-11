<?php

declare(strict_types=1);

class JsonMiddleware
{
    public static function handle(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            return;
        }

        $rawInput = file_get_contents('php://input');

        if ($rawInput === false || trim($rawInput) === '') {
            $GLOBALS['request_data'] = [];
            return;
        }

        $data = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode([
                'status'  => false,
                'message' => 'Invalid JSON payload'
            ]);
            exit;
        }

        $GLOBALS['request_data'] = $data;
    }
}
