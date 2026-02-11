<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * Authentication Middleware
 * --------------------------------------------------
 * - Validates JWT access token
 * - Extracts authenticated user data
 * - Blocks unauthorized requests
 */

class AuthMiddleware
{
    /**
     * Handle JWT authentication
     */
    public static function handle(): array
    {
        $headers = getallheaders();
        $authorizationHeader =
            $headers['Authorization']
            ?? $headers['authorization']
            ?? null;

        // Validate Authorization header
        if (!$authorizationHeader) {
            Response::json(
                ['status' => false, 'message' => 'Authorization token missing'],
                401
            );
        }

        // Extract Bearer token
        if (!preg_match('/Bearer\s+(\S+)/', $authorizationHeader, $matches)) {
            Response::json(
                ['status' => false, 'message' => 'Invalid authorization format'],
                401
            );
        }

        $accessToken = $matches[1];

        // Validate JWT access token
        $userPayload = JWT::validate($accessToken, 'access');

        if (!$userPayload) {
            Response::json(
                ['status' => false, 'message' => 'Access token expired or invalid'],
                401
            );
        }

        return $userPayload;
    }
}
