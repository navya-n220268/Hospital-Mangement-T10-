<?php
/**
 * Fetch all patients from MongoDB Atlas
 * Normalizes field names across different document formats.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

// Auth check: support both admin_id and user_role session vars
if (empty($_SESSION['admin_id']) && ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = getDB();
    $patientsCollection = $db->patients;

    $cursor   = $patientsCollection->find([], ['sort' => ['created_at' => -1]]);
    $patients = $cursor->toArray();

    $result = [];

    foreach ($patients as $patient) {
        $name       = $patient['full_name'] ?? $patient['name'] ?? 'Unknown';
        $age        = isset($patient['age']) ? (int)$patient['age'] : 0;
        $patientId  = $patient['patient_id'] ?? '';
        $gender     = $patient['gender'] ?? 'N/A';
        $status     = $patient['status'] ?? 'Active';
        $bloodGroup = $patient['blood_group'] ?? $patient['bloodGroup'] ?? '';
        $address    = $patient['address'] ?? '';

        $result[] = [
            'id'          => (string)($patient['_id'] ?? ''),
            'patient_id'  => $patientId,
            'name'        => $name,
            'email'       => $patient['email'] ?? '',
            'phone'       => $patient['phone'] ?? '',
            'age'         => $age,
            'gender'      => $gender,
            'blood_group' => $bloodGroup,
            'address'     => $address,
            'status'      => $status,
        ];
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
