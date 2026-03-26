<?php
/**
 * Sanjeevani – backend/admin/get_notifications.php
 * Returns all admin notifications, sorted newest first.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated() || getSessionUser()['role'] !== 'admin') {
    json_out(['success' => false, 'message' => 'Unauthorised.'], 403);
}

try {
    $db = getDB();
    $cursor = $db->notifications->find(
        ['receiverType' => 'admin'],
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
