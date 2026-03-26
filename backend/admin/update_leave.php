<?php
/**
 * Sanjeevani – backend/admin/update_leave.php
 * POST { leaveId, action: "approved"|"rejected" }
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'POST required.'], 405);
}
if (!isAuthenticated() || getSessionUser()['role'] !== 'admin') {
    json_out(['success' => false, 'message' => 'Unauthorised.'], 403);
}

$body    = json_decode(file_get_contents('php://input'), true) ?? [];
$leaveId = trim($body['leaveId'] ?? ($_POST['leaveId'] ?? ''));
$action  = trim($body['action']  ?? ($_POST['action']  ?? ''));

if (empty($leaveId) || !in_array($action, ['approved', 'rejected'], true)) {
    json_out(['success' => false, 'message' => 'leaveId and valid action required.'], 400);
}

try {
    $db  = getDB();
    $oid = new MongoDB\BSON\ObjectId($leaveId);

    $result = $db->leaves->updateOne(
        ['_id' => $oid],
        ['$set' => ['status' => $action]]
    );

    if ($result->getMatchedCount() === 0) {
        json_out(['success' => false, 'message' => 'Leave record not found.'], 404);
    }

    // Mark related admin notification as read
    $db->notifications->updateOne(
        ['type' => 'leave_request', 'relatedId' => $leaveId, 'receiverType' => 'admin'],
        ['$set' => ['status' => 'read']]
    );

    json_out(['success' => true, 'message' => "Leave request {$action}."]);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
