<?php
/**
 * Sanjeevani — backend/patient/get_prescriptions.php
 * Returns all prescriptions for the logged-in patient, newest first.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}
$sessionUser = getSessionUser();
if ($sessionUser['role'] !== 'patient') {
    json_out(['success' => false, 'message' => 'Patients only.'], 403);
}

try {
    $db        = getDB();
    $patientId = $sessionUser['id'];

    $cursor = $db->prescriptions->find(
        ['patient_id' => $patientId],
        ['sort' => ['created_at' => -1]]
    );

    $list = [];
    foreach ($cursor as $rx) {
        $list[] = [
            'id'               => (string) $rx['_id'],
            'doctor_name'      => $rx['doctor_name']      ?? 'Unknown Doctor',
            'department'       => $rx['department']       ?? '',
            'diagnosis'        => $rx['diagnosis']        ?? '',
            'symptoms'         => $rx['symptoms']         ?? '',
            'medicines'        => $rx['medicines']        ?? [],
            'notes'            => $rx['notes']            ?? '',
            'next_visit'       => $rx['next_visit']       ?? '',
            'tests_ordered'    => $rx['tests_ordered']    ?? '',
            'prescription_date'=> $rx['prescription_date'] ?? '',
            'createdAt'        => isset($rx['created_at']) ? $rx['created_at']->toDateTime()->format('Y-m-d') : '',
        ];
    }

    json_out(['success' => true, 'prescriptions' => $list]);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
