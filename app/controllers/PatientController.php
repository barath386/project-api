<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * Patient Controller
 * --------------------------------------------------
 * Handles HTTP requests related to patients
 */

class PatientController
{
    private Patient $patientModel;

    public function __construct()
    {
        $this->patientModel = new Patient();
    }

    /* --------------------------------------------------
     | GET /api/patients
     |-------------------------------------------------- */
    public function index(): void
    {
        $patients = $this->patientModel->getAll();

        Response::json([
            'status' => true,
            'data'   => $patients,
        ]);
    }

    /* --------------------------------------------------
     | GET /api/patients?id=1
     |-------------------------------------------------- */
    public function show(): void
    {
        $id = $this->getIdFromQuery();

        $patient = $this->patientModel->findById($id);

        if (!$patient) {
            Response::json(
                ['status' => false, 'message' => 'Patient not found'],
                404
            );
        }

        Response::json([
            'status' => true,
            'data'   => $patient,
        ]);
    }

    /* --------------------------------------------------
     | POST /api/patients
     |-------------------------------------------------- */
    public function create(): void
    {
        $data = $GLOBALS['request_data'] ?? [];

        $this->validateRequiredFields(
            $data,
            ['name', 'age', 'gender', 'contact', 'address']
        );

        $this->validateAge($data['age']);
        $this->validateContact($data['contact']);

        $success = $this->patientModel->create(
            $data['name'],
            (int) $data['age'],
            $data['gender'],
            $data['contact'],
            $data['address']
        );

        if (!$success) {
            Response::json(
                ['status' => false, 'message' => 'Failed to add patient'],
                500
            );
        }

        Response::json(
            ['status' => true, 'message' => 'Patient added successfully'],
            201
        );
    }

    /* --------------------------------------------------
     | PUT /api/patients?id=1
     |-------------------------------------------------- */
    public function update(): void
    {
        $id   = $this->getIdFromQuery();
        $data = $GLOBALS['request_data'] ?? [];

        $this->validateRequiredFields(
            $data,
            ['name', 'age', 'gender', 'contact', 'address']
        );

        $this->validateAge($data['age']);
        $this->validateContact($data['contact']);

        $success = $this->patientModel->update(
            $id,
            $data['name'],
            (int) $data['age'],
            $data['gender'],
            $data['contact'],
            $data['address']
        );

        if (!$success) {
            Response::json(
                ['status' => false, 'message' => 'Update failed'],
                500
            );
        }

        Response::json([
            'status'  => true,
            'message' => 'Patient updated successfully',
        ]);
    }

    /* --------------------------------------------------
     | PATCH /api/patients?id=1
     |-------------------------------------------------- */
    public function patch(): void
    {
        $id   = $this->getIdFromQuery();
        $data = $GLOBALS['request_data'] ?? [];

        if (empty($data)) {
            Response::json(
                ['status' => false, 'message' => 'No data provided for update'],
                400
            );
        }

        if (isset($data['age'])) {
            $this->validateAge($data['age']);
        }

        if (isset($data['contact'])) {
            $this->validateContact($data['contact']);
        }

        $success = $this->patientModel->patchUpdate($id, $data);

        if (!$success) {
            Response::json(
                ['status' => false, 'message' => 'Patch update failed'],
                500
            );
        }

        Response::json([
            'status'  => true,
            'message' => 'Patient partially updated successfully',
        ]);
    }

    /* --------------------------------------------------
     | DELETE /api/patients?id=1
     |-------------------------------------------------- */
    public function delete(): void
    {
        $id = $this->getIdFromQuery();

        if (!$this->patientModel->delete($id)) {
            Response::json(
                ['status' => false, 'message' => 'Delete failed'],
                500
            );
        }

        Response::json([
            'status'  => true,
            'message' => 'Patient deleted successfully',
        ]);
    }

    /* ==================================================
     | Helper Methods (Private)
     |================================================== */

    private function getIdFromQuery(): int
    {
        $id = $_GET['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            Response::json(
                ['status' => false, 'message' => 'Valid ID required'],
                400
            );
        }

        return (int) $id;
    }

    private function validateRequiredFields(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                Response::json(
                    [
                        'status'  => false,
                        'message' => ucfirst($field) . ' is required',
                    ],
                    400
                );
            }
        }
    }

    private function validateAge(mixed $age): void
    {
        if (!is_numeric($age) || (int) $age <= 0) {
            Response::json(
                ['status' => false, 'message' => 'Age must be greater than 0'],
                400
            );
        }
    }

    private function validateContact(string $contact): void
    {
        if (!preg_match('/^[0-9]{10}$/', $contact)) {
            Response::json(
                ['status' => false, 'message' => 'Contact must be exactly 10 digits'],
                400
            );
        }
    }
}
