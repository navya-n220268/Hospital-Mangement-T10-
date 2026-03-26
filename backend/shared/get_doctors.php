<?php
/**
 * Sanjeevani Hospital Management System
 * ─────────────────────────────────────
 * backend/shared/get_doctors.php
 *
 * Returns all doctors, optionally filtered by department.
 * Accessible by admins & patients.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

// Any logged in user with role admin or patient can see this
$sessionUser = getSessionUser();
if (!in_array($sessionUser['role'], ['admin', 'patient'])) {
    json_out(['success' => false, 'message' => 'Access denied.'], 403);
}

try {
    $db = getDB();
    
    $filter = [];
    $dept = sanitise($_GET['department'] ?? '');
    if (!empty($dept)) {
        $filter['$or'] = [
            ['department' => $dept],
            ['specialization' => $dept]
        ];
    }

    $cursor = $db->doctors->find($filter, ['sort' => ['full_name' => 1]]);
    
    $doctors = [];
    foreach ($cursor as $doc) {
        $idStr = (string)$doc['_id'];
        $name = $doc['full_name'] ?? $doc['name'] ?? 'Unknown';
        
        $doctors[] = [
            '_id' => $idStr,
            'doctor_id' => $doc['doctor_id'] ?? 'DOC' . strtoupper(substr($idStr, -4)),
            'name' => strpos(strtolower($name), 'dr.') === false ? 'Dr. ' . $name : $name,
            'department' => $doc['department'] ?? $doc['specialization'] ?? 'General',
            'experience' => $doc['experience'] ?? '',
            'rating' => $doc['rating'] ?? 5,
            'status' => $doc['status'] ?? 'Active'
        ];
    }
    
    json_out([
        'success' => true,
        'doctors' => $doctors
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
}
