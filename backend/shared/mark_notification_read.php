<?php
/**
 * Sanjeevani – backend/shared/mark_notification_read.php
 * POST { id } → marks a notification as read.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'POST required.'], 405);
}
if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$body = json_decode(file_get_contents('php://input'), true);
$id   = trim($body['id'] ?? ($_POST['id'] ?? ''));

if (empty($id)) {
    json_out(['success' => false, 'message' => 'Notification ID required.'], 400);
}

try {
    $db  = getDB();
    $oid = new MongoDB\BSON\ObjectId($id);
    $db->notifications->updateOne(['_id' => $oid], ['$set' => ['status' => 'read']]);
    json_out(['success' => true]);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
