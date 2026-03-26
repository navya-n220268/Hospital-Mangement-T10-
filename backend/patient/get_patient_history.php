<?php
/**
 * Sanjeevani — backend/get_patient_history.php
 *
 * Fetches patient records for the doctor's department.
 * Joins prescriptions data with patient demographic data.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if (!$sessionUser || $sessionUser['role'] !== 'doctor') {
    json_out(['success' => false, 'message' => 'Doctor access only.'], 403);
}

try {
    $db = getDB();
    
    // Resolve department
    $department = $_SESSION['department'] ?? null;
    $doctorId   = $_SESSION['doctor_id'] ?? $sessionUser['id'];
    
    // Fallback if department is missing from session
    if (!$department || $department === 'General') {
        $doc = $db->doctors->findOne(['_id' => new MongoDB\BSON\ObjectId($doctorId)]);
        if ($doc && !empty($doc['department'])) {
            $department = $doc['department'];
            $_SESSION['department'] = $department;
        }
    }

    if (!$department) {
        $department = 'General';
    }

    // Find all prescriptions for this department
    $cursor = $db->prescriptions->find(
        ['department' => $department],
        ['sort' => ['created_at' => -1]]
    );

    $history = [];
    $patientMap = []; // Cache to avoid duplicate queries for same patient

    foreach ($cursor as $rx) {
        $patientId = (string) $rx['patient_id'];

        // If we haven't fetched this patient's details yet, query the patients collection
        if (!isset($patientMap[$patientId])) {
            try {
                $patientInfo = $db->patients->findOne(['_id' => new MongoDB\BSON\ObjectId($patientId)]);
            } catch (Exception $e) {
                // If it fails (e.g. bad ID format), fallback
                $patientInfo = $db->patients->findOne(['_id' => $patientId]);
            }
            
            $patientMap[$patientId] = [
                'age'    => $patientInfo['age'] ?? '—',
                'gender' => $patientInfo['gender'] ?? '—',
                'phone'  => $patientInfo['phone'] ?? '—'
            ];
        }

        $age    = $patientMap[$patientId]['age'];
        $gender = $patientMap[$patientId]['gender'];

        $history[] = [
            'prescription_id' => (string)$rx['_id'],
            'patient_id'      => $patientId,
            'patient_name'    => $rx['patient_name'] ?? 'Unknown',
            'age'             => $age,
            'gender'          => $gender,
            'appointment_date'=> $rx['prescription_date'] ?? '', // Fallback to Rx date
            'diagnosis'       => $rx['diagnosis'] ?? '—',
            'medicines'       => $rx['medicines'] ?? [],
            'doctor_name'     => $rx['doctor_name'] ?? 'Doctor',
            'department'      => $rx['department'] ?? $department
        ];
    }

    json_out([
        'success'    => true,
        'department' => $department,
        'count'      => count($history),
        'records'    => $history
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
