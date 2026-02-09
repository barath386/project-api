<?php

/**
 * Patient Controller
 * Handles CRUD operations for patients (JWT Protected)
 */

class PatientController
{
    /**
     * GET /api/patients
     * Fetch all patients
     */
    public function index(): void
    {
        $patients = Patient::all();
        Response::success('Patients fetched successfully', $patients);
    }

    /**
     * POST /api/patients
     * Create new patient
     */
    public function store(): void
    {
        $body = $_REQUEST['body'] ?? [];

        // ===============================
        // VALIDATION
        // ===============================
        if (empty($body['name'])) {
            Response::error('Patient name is required', 422);
        }

        if (!isset($body['age']) || !is_numeric($body['age'])) {
            Response::error('Valid age is required', 422);
        }

        if (empty($body['gender'])) {
            Response::error('Gender is required', 422);
        }

        // ===============================
        // CREATE PATIENT
        // ===============================
        $created = Patient::create([
            'name'    => $body['name'],
            'age'     => (int) $body['age'],
            'gender'  => $body['gender'],
            'phone'   => $body['phone'] ?? '',
            'address' => $body['address'] ?? ''
        ]);

        if (!$created) {
            Response::error('Failed to create patient', 500);
        }

        Response::success('Patient created successfully');
    }

    /**
     * PUT /api/patients/{id}
     * Update patient
     */
    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::error('Invalid patient ID', 400);
        }

        $patient = Patient::findById($id);

        if (!$patient) {
            Response::notFound('Patient not found');
        }

        $body = $_REQUEST['body'] ?? [];

        // ===============================
        // VALIDATION
        // ===============================
        if (isset($body['age']) && !is_numeric($body['age'])) {
            Response::error('Age must be numeric', 422);
        }

        // ===============================
        // UPDATE PATIENT
        // ===============================
        $updated = Patient::update($id, [
            'name'    => $body['name']    ?? $patient['name'],
            'age'     => isset($body['age']) ? (int) $body['age'] : $patient['age'],
            'gender'  => $body['gender']  ?? $patient['gender'],
            'phone'   => $body['phone']   ?? $patient['phone'],
            'address' => $body['address'] ?? $patient['address']
        ]);

        if (!$updated) {
            Response::error('Failed to update patient', 500);
        }

        Response::success('Patient updated successfully');
    }

    /**
     * DELETE /api/patients/{id}
     * Delete patient
     */
    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::error('Invalid patient ID', 400);
        }

        $patient = Patient::findById($id);

        if (!$patient) {
            Response::notFound('Patient not found');
        }

        $deleted = Patient::delete($id);

        if (!$deleted) {
            Response::error('Failed to delete patient', 500);
        }

        Response::success('Patient deleted successfully');
    }
}
