<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * Authentication Controller
 * --------------------------------------------------
 * Handles user registration, login, and token refresh
 */

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /* --------------------------------------------------
     | POST /api/register
     |-------------------------------------------------- */
    public function register(): void
    {
        $data = $GLOBALS['request_data'] ?? [];

        if (empty($data['email']) || empty($data['password'])) {
            Response::json(
                ['status' => false, 'message' => 'Email and password are required'],
                400
            );
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::json(
                ['status' => false, 'message' => 'Invalid email format'],
                400
            );
        }

        $hashedPassword = password_hash(
            $data['password'],
            PASSWORD_DEFAULT
        );

        try {
            $this->userModel->create(
                $data['name'] ?? '',
                $data['email'],
                $hashedPassword
            );

            Response::json(
                ['status' => true, 'message' => 'User registered successfully'],
                201
            );

        } catch (Exception $e) {
            Response::json(
                ['status' => false, 'message' => 'Email already exists'],
                409
            );
        }
    }

    /* --------------------------------------------------
     | POST /api/login
     |-------------------------------------------------- */
    public function login(): void
    {
        $data = $GLOBALS['request_data'] ?? [];

        if (
            empty($data['email']) ||
            empty($data['password'])
        ) {
            Response::json(
                ['status' => false, 'message' => 'Email and password are required'],
                400
            );
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::json(
                ['status' => false, 'message' => 'Invalid email format'],
                400
            );
        }

        $user = $this->userModel->findByEmail($data['email']);

        if (
            !$user ||
            !password_verify($data['password'], $user['password'])
        ) {
            Response::json(
                ['status' => false, 'message' => 'Invalid email or password'],
                401
            );
        }

        // Generate tokens
        $payload = [
            'user_id' => $user['id'],
            'email'   => $user['email'],
        ];

        $accessToken  = JWT::generateAccessToken($payload);
        $refreshToken = JWT::generateRefreshToken($payload);

        // Store refresh token in DB
        $this->userModel->updateRefreshToken(
            (int) $user['id'],
            $refreshToken
        );

        // Set refresh token as HttpOnly cookie
        setcookie(
            'refreshToken',
            $refreshToken,
            [
                'expires'  => time() + REFRESH_TOKEN_EXP,
                'path'     => '/',
                'secure'   => false, // true in HTTPS
                'httponly' => true,
                'samesite' => 'Strict',
            ]
        );

        Response::json([
            'status'              => true,
            'access_token'        => $accessToken,
            'access_expires_in'   => ACCESS_TOKEN_EXP,
        ]);
    }

    /* --------------------------------------------------
     | POST /api/refresh
     |-------------------------------------------------- */
    public function refresh(): void
    {
        $refreshToken = $_COOKIE['refreshToken'] ?? null;

        if (!$refreshToken) {
            Response::json(
                ['status' => false, 'message' => 'Refresh token missing'],
                401
            );
        }

        $decoded = JWT::validate($refreshToken, 'refresh');

        if (!$decoded) {
            Response::json(
                ['status' => false, 'message' => 'Invalid or expired refresh token'],
                401
            );
        }

        $payload = [
            'user_id' => $decoded['user_id'],
            'email'   => $decoded['email'],
        ];

        $newAccessToken = JWT::generateAccessToken($payload);

        Response::json([
            'status'            => true,
            'access_token'      => $newAccessToken,
            'access_expires_in' => ACCESS_TOKEN_EXP,
        ]);
    }
}
