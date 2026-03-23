<?php
session_start();
require_once __DIR__ . '/../config.php';

// Helper for JSON output if not defined
if (!function_exists('json_out')) {
    function json_out($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

if (empty($_SESSION['admin_id']) && ($_SESSION['user_role'] ?? '') !== 'admin') {
    json_out(['success' => false, 'message' => 'Unauthorized'], 401);
}

try {
    $db = getDB();
    
    $appointments = $db->appointments->find(
        [],
        [
            'sort' => ['created_at' => -1, 'appointment_date' => -1],
            'limit' => 5
        ]
    );
    
    $results = [];
    foreach ($appointments as $appt) {
        $dateStr = $appt['appointment_date'] ?? null;
        if ($dateStr instanceof \MongoDB\BSON\UTCDateTime) {
            $date = $dateStr->toDateTime()->format('Y-m-d');
        } else {
            $date = $dateStr ? date('Y-m-d', strtotime((string)$dateStr)) : '';
        }

        $results[] = [
            'patientName' => $appt['patient_name'] ?? 'Unknown',
            'patientId'   => (string)($appt['patient_id'] ?? ''),
            'doctorName'  => (strpos($appt['doctor_name'] ?? '', 'Dr.') === 0 ? '' : 'Dr. ') . ($appt['doctor_name'] ?? 'Unknown'),
            'department'  => $appt['department'] ?? 'General',
            'date'        => $date,
            'time'        => $appt['appointment_time'] ?? '',
            'status'      => ucfirst(strtolower($appt['status'] ?? 'pending'))
        ];
    }
    
    json_out($results);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Database error'], 500);
}
