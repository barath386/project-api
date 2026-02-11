<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * Patient Model
 * --------------------------------------------------
 * Handles all database operations related to patients
 */

class Patient
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
     * Get patient by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT *
                FROM patients
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create a new patient
     */
    public function create(
        string $name,
        int $age,
        string $gender,
        string $contact,
        string $address
    ): bool {
        $sql = "INSERT INTO patients 
                (name, age, gender, contact, address)
                VALUES
                (:name, :age, :gender, :contact, :address)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':name'    => $name,
            ':age'     => $age,
            ':gender'  => $gender,
            ':contact' => $contact,
            ':address' => $address,
        ]);
    }

    /**
     * Update patient (PUT)
     */
    public function update(
        int $id,
        string $name,
        int $age,
        string $gender,
        string $contact,
        string $address
    ): bool {
        $sql = "UPDATE patients
                SET name    = :name,
                    age     = :age,
                    gender  = :gender,
                    contact = :contact,
                    address = :address
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id'      => $id,
            ':name'    => $name,
            ':age'     => $age,
            ':gender'  => $gender,
            ':contact' => $contact,
            ':address' => $address,
        ]);
    }

    /**
     * Partial update (PATCH)
     */
    public function patchUpdate(int $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $column => $value) {
            $fields[] = "{$column} = :{$column}";
            $params[":{$column}"] = $value;
        }

        $sql = "UPDATE patients
                SET " . implode(', ', $fields) . "
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Delete patient
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM patients
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get all patients
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM patients");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
