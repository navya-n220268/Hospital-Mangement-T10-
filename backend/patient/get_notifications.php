<?php
/**
 * Sanjeevani – backend/patient/get_notifications.php
 * Returns notifications for the logged-in patient.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if ($sessionUser['role'] !== 'patient') {
    json_out(['success' => false, 'message' => 'Patients only.'], 403);
}

try {
    $db        = getDB();
    $patientId = $sessionUser['id'];

    $cursor = $db->notifications->find(
        ['receiverType' => 'patient', 'receiverId' => $patientId],
        ['sort' => ['createdAt' => -1], 'limit' => 100]
    );

    $list = [];
    foreach ($cursor as $n) {
        $list[] = [
            'id'        => (string) $n['_id'],
            'type'      => $n['type']      ?? '',
            'message'   => $n['message']   ?? '',
            'status'    => $n['status']    ?? 'unread',
            'relatedId' => (string) ($n['relatedId'] ?? ''),
            'createdAt' => isset($n['createdAt']) ? $n['createdAt']->toDateTime()->format('Y-m-d H:i:s') : '',
        ];
    }

    json_out(['success' => true, 'notifications' => $list]);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
