<?php

/**
 * Authentication Middleware
 * - Validates JWT token
 * - Protects secured routes
 * - Attaches authenticated user to request
 */

class AuthMiddleware
{
    public static function handle(): void
    {
        // Get all request headers (case-insensitive)
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);

        // Check Authorization header
        if (!isset($headers['authorization'])) {
            Response::json(false, 'Authorization token required', [], 401);
        }

        $authHeader = trim($headers['authorization']);

        // Validate Bearer format
        if (!str_starts_with($authHeader, 'Bearer ')) {
            Response::json(false, 'Invalid Authorization format', [], 401);
        }

        // Extract token
        $token = trim(substr($authHeader, 7));

        if ($token === '') {
            Response::json(false, 'JWT token missing', [], 401);
        }

        // Validate token
        $user = JWT::validate($token);

        if (!$user) {
            Response::json(false, 'Invalid or expired token', [], 401);
        }

        // Attach authenticated user to request
        $_REQUEST['user'] = $user;
    }
}
