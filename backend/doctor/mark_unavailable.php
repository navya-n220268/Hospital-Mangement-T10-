<?php
/**
 * Sanjeevani — backend/doctor/mark_unavailable.php
 * POST { reason, customReason, fromDate, toDate, fromTime, toTime }
 * 1. Insert unavailability record.
 * 2. Find all non-cancelled appointments for this doctor in the window.
 * 3. Send in-app notification to each affected patient.
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

$reason       = sanitise($_POST['reason']       ?? '');
$customReason = sanitise($_POST['customReason'] ?? '');
$fromDate     = sanitise($_POST['fromDate']     ?? '');
$toDate       = sanitise($_POST['toDate']       ?? '');
$fromTime     = sanitise($_POST['fromTime']     ?? '00:00');
$toTime       = sanitise($_POST['toTime']       ?? '23:59');

if (empty($reason) || empty($fromDate) || empty($toDate)) {
    json_out(['success' => false, 'message' => 'Reason, from date, and to date are required.'], 400);
}
if ($toDate < $fromDate) {
    json_out(['success' => false, 'message' => 'To date must be on or after from date.'], 400);
}

try {
    $db       = getDB();
    $doctorId = $sessionUser['id'];
    $now      = new MongoDB\BSON\UTCDateTime();

    $doctor     = $db->doctors->findOne(['_id' => new MongoDB\BSON\ObjectId($doctorId)]);
    $doctorName = $doctor['full_name'] ?? $doctor['name'] ?? 'Your doctor';

    $displayReason = $reason === 'Other' && $customReason ? $customReason : $reason;

    // Insert unavailability record
    $result = $db->unavailabilities->insertOne([
        'doctorId'     => $doctorId,
        'doctorName'   => $doctorName,
        'reason'       => $reason,
        'customReason' => $customReason,
        'fromDate'     => $fromDate,
        'toDate'       => $toDate,
        'fromTime'     => $fromTime,
        'toTime'       => $toTime,
        'status'       => 'active',
        'createdAt'    => $now,
    ]);

    // Find all active appointments for this doctor in the date window
    $affected = $db->appointments->find([
        'doctor_id'        => $doctorId,
        'appointment_date' => ['$gte' => $fromDate, '$lte' => $toDate],
        'status'           => ['$nin' => ['cancelled', 'completed']],
    ]);

    $notifiedCount = 0;
    foreach ($affected as $apt) {
        $patientId = $apt['patient_id'] ?? '';
        if (empty($patientId)) continue;

        $db->notifications->insertOne([
            'type'         => 'unavailability',
            'message'      => "Dr. {$doctorName} has marked themselves unavailable from {$fromDate} to {$toDate} due to: {$displayReason}. Your appointment may be affected. The hospital team will reach out to reschedule.",
            'receiverType' => 'patient',
            'receiverId'   => $patientId,
            'relatedId'    => $doctorId,
            'status'       => 'unread',
            'createdAt'    => $now,
        ]);
        $notifiedCount++;
    }

    // Update doctor's availability field
    $db->doctors->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($doctorId)],
        ['$set' => [
            'availability' => 'Unavailable',
            'is_available' => false,
            'unavailability_reason' => $displayReason
        ]]
    );

    json_out([
        'success'        => true,
        'message'        => "Marked unavailable. {$notifiedCount} patient(s) have been notified.",
        'notifiedCount'  => $notifiedCount,
    ]);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
