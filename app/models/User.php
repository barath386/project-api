<?php

/**
 * User Model
 * Handles database operations related to users
 */

class User
{
    /**
     * Get database connection
     */
    private static function db(): PDO
    {
        return Database::connect();
    }

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): array|false
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find user by ID
     */
    public static function findById(int $id): array|false
    {
        $sql = "SELECT id, name, email, created_at FROM users WHERE id = :id LIMIT 1";
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new user
     */
    public static function create(array $data): bool
    {
        $sql = "INSERT INTO users (name, email, password, created_at)
                VALUES (:name, :email, :password, NOW())";

        $stmt = self::db()->prepare($sql);

        return $stmt->execute([
            'name'     => $data[0],
            'email'    => $data[1],
            'password' => $data[2]
        ]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}
