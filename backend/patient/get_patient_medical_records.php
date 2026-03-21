<?php
/**
 * MediVita — backend/get_patient_medical_records.php
 *
 * Returns all medical records (prescriptions) issued to the currently logged-in patient.
 * Used by the patient portal's Medical Records page.
 *
 * Response:
 *   { success: true, records: [...], total: N }
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if (!$sessionUser || $sessionUser['role'] !== 'patient') {
    json_out(['success' => false, 'message' => 'Patient access only.'], 403);
}

$patientId = $_SESSION['patient_id'] ?? $sessionUser['id'];

try {
    $db = getDB();

    $cursor = $db->prescriptions->find(
        ['patient_id' => $patientId],
        ['sort' => ['created_at' => -1]]
    );

    $records = [];
    foreach ($cursor as $rx) {
        $records[] = [
            '_id'               => (string)$rx['_id'],
            'doctor_id'         => $rx['doctor_id']         ?? '',
            'doctor_name'       => $rx['doctor_name']       ?? '',
            'patient_id'        => $rx['patient_id']        ?? '',
            'patient_name'      => $rx['patient_name']      ?? '',
            'department'        => $rx['department']        ?? '',
            'appointment_id'    => $rx['appointment_id']    ?? '',
            'diagnosis'         => $rx['diagnosis']         ?? '',
            'symptoms'          => $rx['symptoms']          ?? '',
            'medicines'         => $rx['medicines']         ?? [],
            'notes'             => $rx['notes']             ?? '',
            'next_visit'        => $rx['next_visit']        ?? '',
            'tests_ordered'     => $rx['tests_ordered']     ?? '',
            'prescription_date' => $rx['prescription_date'] ?? '',
            'created_at'        => isset($rx['created_at'])
                ? date('Y-m-d', $rx['created_at']->toDateTime()->getTimestamp())
                : '',
        ];
    }

    json_out([
        'success' => true,
        'records' => $records,
        'total'   => count($records),
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
