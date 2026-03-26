<?php
/**
 * Sanjeevani Hospital Management System
 * ─────────────────────────────────────
 * backend/get_doctors_by_department.php
 *
 * Returns doctors belonging to a specific department.
 * Accepts GET parameter: ?department=Cardiology
 * Checks both 'department' and 'specialization' fields for compatibility.
 *
 * Response: { success: true, doctors: [ { _id, name, department, experience, ... }, ... ] }
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$department = sanitise($_GET['department'] ?? '');

if (empty($department)) {
    json_out(['success' => false, 'message' => 'Department parameter is required.'], 400);
}

try {
    $db = getDB();

    // Try matching on 'department' field first, then 'specialization'
    $filter = [
        '$or' => [
            ['department'    => $department],
            ['specialization'=> $department],
        ]
    ];

    $cursor = $db->doctors->find(
        $filter,
        [
            'projection' => [
                '_id'            => 1,
                'name'           => 1,
                'full_name'      => 1,
                'department'     => 1,
                'specialization' => 1,
                'experience'     => 1,
                'rating'         => 1,
                'email'          => 1,
                'phone'          => 1,
                'is_available'   => 1,
                'unavailability_reason' => 1,
            ]
        ]
    );

    $doctors = [];
    foreach ($cursor as $doc) {
        $name  = $doc['full_name'] ?? $doc['name'] ?? 'Unknown Doctor';
        $dept  = $doc['department'] ?? $doc['specialization'] ?? $department;
        $exp   = $doc['experience'] ?? 'N/A';
        $rating = isset($doc['rating']) ? (int)$doc['rating'] : 4;

        $doctors[] = [
            '_id'        => (string)$doc['_id'],
            'name'       => $name,
            'department' => $dept,
            'experience' => $exp,
            'rating'     => $rating,
            'is_available' => $doc['is_available'] ?? true,
            'unavailability_reason' => $doc['unavailability_reason'] ?? ''
        ];
    }

    json_out([
        'success' => true,
        'doctors' => $doctors,
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Failed to fetch doctors: ' . $e->getMessage()], 500);
}
