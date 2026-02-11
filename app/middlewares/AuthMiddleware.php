<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * Authentication Middleware
 * --------------------------------------------------
 * ✔ Validates JWT Access Token
 * ✔ Extracts authenticated user data
 * ✔ Blocks unauthorized requests
 * ✔ Production-ready error handling
 */

class AuthMiddleware
{
    /**
     * Handle JWT authentication
     */
    public static function handle(): array
    {
        // --------------------------------------------------
        // Read Authorization Header (Apache / Nginx / FastCGI)
        // --------------------------------------------------
        $headers = function_exists('getallheaders')
            ? getallheaders()
            : [];

        $authorizationHeader =
            $headers['Authorization']
            ?? $headers['authorization']
            ?? $_SERVER['HTTP_AUTHORIZATION']
            ?? null;

        // --------------------------------------------------
        // Authorization header must exist
        // --------------------------------------------------
        if (!$authorizationHeader) {
            Response::json(
                [
                    'status'  => false,
                    'message' => 'Authorization header missing'
                ],
                401
            );
        }

        // --------------------------------------------------
        // Extract Bearer token
        // --------------------------------------------------
        if (!preg_match('/^Bearer\s+(\S+)$/', $authorizationHeader, $matches)) {
            Response::json(
                [
                    'status'  => false,
                    'message' => 'Invalid Authorization format'
                ],
                401
            );
        }

        $accessToken = $matches[1];

        // --------------------------------------------------
        // Validate Access Token (40s expiry)
        // --------------------------------------------------
        $payload = JWT::validate($accessToken, 'access');

        if ($payload === false) {
            Response::json(
                [
                    'status'  => false,
                    'message' => 'Access token expired or invalid'
                ],
                401
            );
        }

        // --------------------------------------------------
        // Return authenticated user payload
        // --------------------------------------------------
        return $payload;
    }
}
