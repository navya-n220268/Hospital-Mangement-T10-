<?php
/**
 * Sanjeevani — backend/doctor/get_unavailabilities.php
 * Returns all unavailability records for the logged-in doctor.
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

    $cursor = $db->unavailabilities->find(
        ['doctorId' => $doctorId],
        ['sort' => ['createdAt' => -1]]
    );

    $list = [];
    foreach ($cursor as $u) {
        $list[] = [
            'id'           => (string) $u['_id'],
            'reason'       => $u['reason']       ?? '',
            'customReason' => $u['customReason'] ?? '',
            'fromDate'     => $u['fromDate']     ?? '',
            'toDate'       => $u['toDate']       ?? '',
            'fromTime'     => $u['fromTime']     ?? '',
            'toTime'       => $u['toTime']       ?? '',
            'status'       => $u['status']       ?? 'active',
            'createdAt'    => isset($u['createdAt']) ? $u['createdAt']->toDateTime()->format('Y-m-d') : '',
        ];
    }

    json_out(['success' => true, 'unavailabilities' => $list]);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
