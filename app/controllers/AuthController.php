<?php

/**
 * Authentication Controller
 * Handles user registration, login, and profile
 */

class AuthController
{
    /**
     * POST /api/register
     * Register a new user
     */
    public function register(): void
    {
        $data = $_REQUEST['body'] ?? [];

        /* ===============================
           VALIDATION
        =============================== */
        if (empty($data['name'])) {
            Response::validationError(['name' => 'Name is required']);
        }

        if (empty($data['email'])) {
            Response::validationError(['email' => 'Email is required']);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::validationError(['email' => 'Invalid email format']);
        }

        if (empty($data['password'])) {
            Response::validationError(['password' => 'Password is required']);
        }

        if (strlen($data['password']) < 6) {
            Response::validationError(['password' => 'Password must be at least 6 characters']);
        }

        /* ===============================
           CHECK DUPLICATE EMAIL
        =============================== */
        if (User::findByEmail($data['email'])) {
            Response::error('Email already registered', 409);
        }

        /* ===============================
           CREATE USER
        =============================== */
        $created = User::create([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT)
        ]);

        if (!$created) {
            Response::error('Failed to register user', 500);
        }

        Response::success('User registered successfully');
    }

    /**
     * POST /api/login
     * Authenticate user & return JWT
     */
    public function login(): void
    {
        $data = $_REQUEST['body'] ?? [];

        /* ===============================
           VALIDATION
        =============================== */
        if (empty($data['email']) || empty($data['password'])) {
            Response::validationError([
                'email' => 'Email is required',
                'password' => 'Password is required'
            ]);
        }

        /* ===============================
           FIND USER
        =============================== */
        $user = User::findByEmail($data['email']);

        if (!$user || !User::verifyPassword($data['password'], $user['password'])) {
            Response::unauthorized('Invalid credentials');
        }

        /* ===============================
           GENERATE JWT
        =============================== */
        $token = JWT::generate([
            'user_id' => $user['id'],
            'email'   => $user['email'],
            'name'    => $user['name']
        ]);

        Response::success('Login successful', [
            'token'       => $token,
            'token_type'  => 'Bearer',
            'expires_in'  => JWT_EXPIRY,
            'user' => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email']
            ]
        ]);
    }

    /**
     * GET /api/me
     * Get authenticated user profile (JWT Protected)
     */
    public function me(): void
    {
        $authUser = $_REQUEST['user'] ?? null;

        if (!$authUser) {
            Response::unauthorized();
        }

        $user = User::findById($authUser['user_id']);

        if (!$user) {
            Response::notFound('User not found');
        }

        Response::success('User profile', [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email']
        ]);
    }
}
