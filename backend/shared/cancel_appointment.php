<?php
/**
 * MediVita Hospital Management System
 * ─────────────────────────────────────
 * backend/cancel_appointment.php
 *
 * Cancels an appointment by setting status = 'cancelled'.
 * Only the patient who owns the appointment can cancel it.
 *
 * Expected POST fields: appointment_id
 *
 * Returns JSON: { success: true/false, message }
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'Method not allowed.'], 405);
}

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if (!$sessionUser || $sessionUser['role'] !== 'patient') {
    json_out(['success' => false, 'message' => 'Only patients can cancel their appointments.'], 403);
}

$appointmentId = sanitise($_POST['appointment_id'] ?? '');
if (empty($appointmentId)) {
    json_out(['success' => false, 'message' => 'Appointment ID is required.'], 400);
}

try {
    $db = getDB();
    $objId = new MongoDB\BSON\ObjectId($appointmentId);

    // Find the appointment and verify ownership
    $appt = $db->appointments->findOne(['_id' => $objId]);
    if (!$appt) {
        json_out(['success' => false, 'message' => 'Appointment not found.'], 404);
    }

    if ($appt['patient_id'] !== $sessionUser['id']) {
        json_out(['success' => false, 'message' => 'You are not authorised to cancel this appointment.'], 403);
    }

    if (($appt['status'] ?? '') === 'cancelled') {
        json_out(['success' => false, 'message' => 'This appointment is already cancelled.'], 400);
    }

    if (($appt['status'] ?? '') === 'completed') {
        json_out(['success' => false, 'message' => 'Completed appointments cannot be cancelled.'], 400);
    }

    // Update status
    $db->appointments->updateOne(
        ['_id' => $objId],
        ['$set' => ['status' => 'cancelled', 'updated_at' => new MongoDB\BSON\UTCDateTime()]]
    );

    json_out(['success' => true, 'message' => 'Appointment cancelled successfully.']);

} catch (MongoDB\Driver\Exception\InvalidArgumentException $e) {
    json_out(['success' => false, 'message' => 'Invalid appointment ID.'], 400);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
}
