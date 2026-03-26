<?php
/**
 * Sanjeevani — backend/doctor/cancel_unavailability.php
 * POST { id } → marks an unavailability as cancelled.
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

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id   = trim($body['id'] ?? ($_POST['id'] ?? ''));

try {
    $db = getDB();
    if (empty($id)) {
        // Cancel all active records for this doctor
        $result = $db->unavailabilities->updateMany(
            ['doctorId' => $sessionUser['id'], 'status' => 'active'],
            ['$set' => ['status' => 'cancelled']]
        );
        if ($result->getMatchedCount() === 0) {
            json_out(['success' => false, 'message' => 'No active unavailability found.'], 404);
        }
    } else {
        $oid = new MongoDB\BSON\ObjectId($id);
        // Ensure doctor owns this record
        $result = $db->unavailabilities->updateOne(
            ['_id' => $oid, 'doctorId' => $sessionUser['id']],
            ['$set' => ['status' => 'cancelled']]
        );

        if ($result->getMatchedCount() === 0) {
            json_out(['success' => false, 'message' => 'Record not found or access denied.'], 404);
        }
    }

    // Restore doctor availability if no other active unavailabilities
    $activeCount = $db->unavailabilities->countDocuments([
        'doctorId' => $sessionUser['id'],
        'status'   => 'active',
    ]);
    if ($activeCount === 0) {
        $db->doctors->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($sessionUser['id'])],
            ['$set' => [
                'availability' => 'Available',
                'is_available' => true,
                'unavailability_reason' => ''
            ]]
        );
    }

    json_out(['success' => true, 'message' => 'Unavailability cancelled.']);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
