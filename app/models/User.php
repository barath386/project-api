<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * User Model
 * --------------------------------------------------
 * Handles all user-related database operations
 */

class User
{
    private PDO $db;

    /**
     * Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Store or update refresh token for a user
     */
    public function updateRefreshToken(int $userId, string $refreshToken): bool
    {
        $sql = "UPDATE users 
                SET refresh_token = :refresh_token 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':refresh_token' => $refreshToken,
            ':id'            => $userId,
        ]);
    }

    /**
     * Find user by refresh token
     */
    public function findByRefreshToken(string $refreshToken): ?array
    {
        $sql = "SELECT * 
                FROM users 
                WHERE refresh_token = :refresh_token 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':refresh_token' => $refreshToken,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create a new user
     */
    public function create(string $name, string $email, string $password): bool
    {
        $sql = "INSERT INTO users (name, email, password) 
                VALUES (:name, :email, :password)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => $password,
        ]);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * 
                FROM users 
                WHERE email = :email 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => $email,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
