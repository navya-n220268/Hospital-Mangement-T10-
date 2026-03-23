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

// Check if user is logged in as admin
if (empty($_SESSION['admin_id']) && ($_SESSION['user_role'] ?? '') !== 'admin') {
    json_out(['success' => false, 'message' => 'Unauthorized'], 401);
}

try {
    $db = getDB();
    
    $doctors = $db->doctors->countDocuments([]);
    $patients = $db->patients->countDocuments([]);
    $appointments = $db->appointments->countDocuments([]);
    $prescriptions = $db->prescriptions->countDocuments([]);
    
    json_out([
        'doctors' => $doctors,
        'patients' => $patients,
        'appointments' => $appointments,
        'prescriptions' => $prescriptions
    ]);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Database error'], 500);
}
