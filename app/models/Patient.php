<?php

/**
 * Patient Model
 * Handles database operations related to patients
 */

class Patient
{
    /**
     * Get database connection
     */
    private static function db(): PDO
    {
        return Database::connect();
    }

    /**
     * Get all patients
     */
    public static function all(): array
    {
        $sql = "SELECT id, name, age, gender, phone, address, created_at
                FROM patients
                ORDER BY id DESC";

        $stmt = self::db()->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find patient by ID
     */
    public static function findById(int $id): array|false
    {
        $sql = "SELECT id, name, age, gender, phone, address, created_at
                FROM patients
                WHERE id = :id
                LIMIT 1";

        $stmt = self::db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new patient
     */
    public static function create(array $data): bool
    {
        $sql = "INSERT INTO patients (name, age, gender, phone, address, created_at)
                VALUES (:name, :age, :gender, :phone, :address, NOW())";

        $stmt = self::db()->prepare($sql);

        return $stmt->execute([
            'name'    => $data['name'],
            'age'     => $data['age'],
            'gender'  => $data['gender'],
            'phone'   => $data['phone'],
            'address' => $data['address']
        ]);
    }

    /**
     * Update patient
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "UPDATE patients
                SET name = :name,
                    age = :age,
                    gender = :gender,
                    phone = :phone,
                    address = :address
                WHERE id = :id";

        $stmt = self::db()->prepare($sql);

        return $stmt->execute([
            'id'      => $id,
            'name'    => $data['name'],
            'age'     => $data['age'],
            'gender'  => $data['gender'],
            'phone'   => $data['phone'],
            'address' => $data['address']
        ]);
    }

    /**
     * Delete patient
     */
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM patients WHERE id = :id";
        $stmt = self::db()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
