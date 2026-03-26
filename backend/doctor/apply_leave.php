<?php
/**
 * Sanjeevani – backend/doctor/apply_leave.php
 * POST { fromDate, toDate, reason }
 * Creates leave request and notifies admin.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'POST required.'], 405);
}
if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if ($sessionUser['role'] !== 'doctor') {
    json_out(['success' => false, 'message' => 'Doctors only.'], 403);
}

$fromDate = sanitise($_POST['fromDate'] ?? '');
$toDate   = sanitise($_POST['toDate']   ?? '');
$reason   = sanitise($_POST['reason']   ?? '');

if (empty($fromDate) || empty($toDate)) {
    json_out(['success' => false, 'message' => 'From date and to date are required.'], 400);
}
if ($toDate < $fromDate) {
    json_out(['success' => false, 'message' => 'To date must be on or after from date.'], 400);
}

try {
    $db       = getDB();
    $doctorId = $sessionUser['id'];
    $now      = new MongoDB\BSON\UTCDateTime();

    // Fetch doctor name for the notification message
    $doctor     = $db->doctors->findOne(['_id' => new MongoDB\BSON\ObjectId($doctorId)]);
    $doctorName = $doctor['full_name'] ?? $doctor['name'] ?? 'Unknown Doctor';

    $leave = [
        'doctorId'  => $doctorId,
        'doctorName'=> $doctorName,
        'fromDate'  => $fromDate,
        'toDate'    => $toDate,
        'reason'    => $reason,
        'status'    => 'pending',
        'createdAt' => $now,
    ];

    $result  = $db->leaves->insertOne($leave);
    $leaveId = (string) $result->getInsertedId();

    // Notify admin
    $db->notifications->insertOne([
        'type'         => 'leave_request',
        'message'      => "Dr. {$doctorName} applied for leave from {$fromDate} to {$toDate}.",
        'receiverType' => 'admin',
        'relatedId'    => $leaveId,
        'status'       => 'unread',
        'createdAt'    => $now,
    ]);

    json_out(['success' => true, 'message' => 'Leave request submitted. Awaiting admin approval.', 'leaveId' => $leaveId]);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
