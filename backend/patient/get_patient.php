<?php
/**
 * Sanjeevani Hospital Management System
 * ─────────────────────────────────────
 * backend/patient/get_patient.php
 *
 * Fetches full details for the logged-in patient.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if (!$sessionUser || $sessionUser['role'] !== 'patient') {
    json_out(['success' => false, 'message' => 'Access denied. Patient only.'], 403);
}

$patientId = $sessionUser['id'];

try {
    $db = getDB();
    
    // Attempt to find by ObjectId string (stored as patient_id in some scripts)
    // or by custom patient_id if that's what's in the session.
    // In our system, session id is the MongoDB _id.
    
    $user = $db->patients->findOne(['_id' => new MongoDB\BSON\ObjectId($patientId)]);
    
    if (!$user) {
        json_out(['success' => false, 'message' => 'Patient not found.'], 404);
    }

    // Prepare safe data
    $data = [
        'id'         => (string)$user['_id'],
        'patient_id' => $user['patient_id'] ?? 'N/A',
        'full_name'  => $user['full_name'] ?? $user['name'] ?? 'Unknown',
        'email'      => $user['email'] ?? '',
        'phone'      => $user['phone'] ?? '',
        'gender'     => $user['gender'] ?? '',
        'age'        => $user['age'] ?? '',
        'address'    => $user['address'] ?? '',
        'blood_group'=> $user['blood_group'] ?? '',
        'role'       => $user['role'] ?? 'patient'
    ];

    json_out([
        'success' => true,
        'data'    => $data
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
}
