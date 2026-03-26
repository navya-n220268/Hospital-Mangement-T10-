<?php
/**
 * Fetch all doctors from MongoDB
 * Normalizes field names across different doctor document formats.
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
    $doctorsCollection = $db->doctors;

    // Fetch all doctors, sorted by name
    $cursor  = $doctorsCollection->find([], ['sort' => ['name' => 1]]);
    $doctors = $cursor->toArray();

    $result = [];

    foreach ($doctors as $doctor) {
        // Normalize: some docs use "full_name", others use "name"
        $name = $doctor['full_name'] ?? $doctor['name'] ?? 'Unknown';

        // Normalize: some docs use "department" for specialization
        $specialization = $doctor['specialization'] ?? $doctor['department'] ?? 'General';

        // Normalize: experience can be "12 years" (string) or 12 (int)
        $expRaw = $doctor['experience'] ?? 0;
        if (is_string($expRaw)) {
            preg_match('/(\d+)/', $expRaw, $matches);
            $experience = isset($matches[1]) ? (int)$matches[1] : 0;
        } else {
            $experience = (int)$expRaw;
        }

        // Normalize status
        $status = $doctor['status'] ?? null;
        $approvalStatus = $doctor['approval_status'] ?? 'approved';
        
        // Strict mapping for Doctor Approval Status per Requirements
        if ($approvalStatus === 'pending') {
            $status = 'Pending';
        } elseif ($approvalStatus === 'rejected') {
            $status = 'Rejected';
        } else {
            $status = 'Approved';
        }

        // Fetch patient count dynamically from appointments
        $docIdStr = (string)($doctor['_id'] ?? '');
        $patientIds = $db->appointments->distinct('patient_id', ['doctor_id' => $docIdStr]);
        $patientsCount = is_array($patientIds) ? count($patientIds) : 0;

        $result[] = [
            'id'             => $docIdStr,
            'doctor_id'      => $doctor['doctor_id'] ?? '',
            'name'           => $name,
            'specialization' => $specialization,
            'email'          => $doctor['email'] ?? '',
            'phone'          => $doctor['phone'] ?? '',
            'experience'     => $experience,
            'status'         => $status,
            'approval_status'=> $approvalStatus,
            'qualification'  => $doctor['qualification'] ?? '',
            'joined_date'    => $doctor['joined_date'] ?? '',
            'patients'       => $patientsCount,
        ];
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
