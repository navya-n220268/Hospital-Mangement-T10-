<?php
/**
 * Sanjeevani – backend/doctor/get_my_leaves.php
 * Returns all leave records for the logged-in doctor.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if ($sessionUser['role'] !== 'doctor') {
    json_out(['success' => false, 'message' => 'Doctors only.'], 403);
}

try {
    $db       = getDB();
    $doctorId = $sessionUser['id'];

    $cursor = $db->leaves->find(
        ['doctorId' => $doctorId],
        ['sort' => ['createdAt' => -1]]
    );

    $list = [];
    foreach ($cursor as $l) {
        $list[] = [
            'id'        => (string) $l['_id'],
            'fromDate'  => $l['fromDate']  ?? '',
            'toDate'    => $l['toDate']    ?? '',
            'reason'    => $l['reason']    ?? '',
            'status'    => $l['status']    ?? 'pending',
            'createdAt' => isset($l['createdAt']) ? $l['createdAt']->toDateTime()->format('Y-m-d') : '',
        ];
    }

    json_out(['success' => true, 'leaves' => $list]);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
