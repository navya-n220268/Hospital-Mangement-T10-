<?php
/**
 * Sanjeevani – backend/admin/get_leaves.php
 * Returns all leave requests (all statuses), newest first.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated() || getSessionUser()['role'] !== 'admin') {
    json_out(['success' => false, 'message' => 'Unauthorised.'], 403);
}

try {
    $db     = getDB();
    $cursor = $db->leaves->find([], ['sort' => ['createdAt' => -1]]);

    $list = [];
    foreach ($cursor as $l) {
        $list[] = [
            'id'         => (string) $l['_id'],
            'doctorId'   => $l['doctorId']   ?? '',
            'doctorName' => $l['doctorName'] ?? 'Unknown',
            'fromDate'   => $l['fromDate']   ?? '',
            'toDate'     => $l['toDate']     ?? '',
            'reason'     => $l['reason']     ?? '',
            'status'     => $l['status']     ?? 'pending',
            'createdAt'  => isset($l['createdAt']) ? $l['createdAt']->toDateTime()->format('Y-m-d') : '',
        ];
    }

    json_out(['success' => true, 'leaves' => $list]);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
