<?php
/**
 * Sanjeevani – backend/doctor/trigger_emergency.php
 * POST { date, timeSlot }
 * 1. Inserts into emergencies collection.
 * 2. Finds the affected appointment and reschedules to next day.
 * 3. Notifies the patient.
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

$date      = sanitise($_POST['date']     ?? '');
$timeSlot  = sanitise($_POST['timeSlot'] ?? '');

if (empty($date) || empty($timeSlot)) {
    json_out(['success' => false, 'message' => 'Date and time slot are required.'], 400);
}

try {
    $db       = getDB();
    $doctorId = $sessionUser['id'];
    $now      = new MongoDB\BSON\UTCDateTime();

    // Insert emergency record
    $db->emergencies->insertOne([
        'doctorId'  => $doctorId,
        'date'      => $date,
        'timeSlot'  => $timeSlot,
        'createdAt' => $now,
    ]);

    // Find existing appointment for that slot
    $appointment = $db->appointments->findOne([
        'doctor_id'        => $doctorId,
        'appointment_date' => $date,
        'appointment_time' => $timeSlot,
        'status'           => ['$nin' => ['cancelled', 'completed']],
    ]);

    $rescheduled = false;
    $newDate     = null;

    if ($appointment) {
        // Calculate next day
        $dateObj = new DateTime($date);
        $dateObj->modify('+1 day');
        $newDate = $dateObj->format('Y-m-d');

        $aptId = $appointment['_id'];

        $db->appointments->updateOne(
            ['_id' => $aptId],
            ['$set' => ['appointment_date' => $newDate, 'status' => 'rescheduled', 'updated_at' => $now]]
        );

        $patientId   = $appointment['patient_id'] ?? '';
        $patientName = $appointment['patient_name'] ?? 'Patient';

        // Notify patient
        if (!empty($patientId)) {
            $db->notifications->insertOne([
                'type'         => 'reschedule',
                'message'      => "Your appointment scheduled for {$date} at {$timeSlot} has been rescheduled to {$newDate} due to a doctor emergency. We apologise for the inconvenience.",
                'receiverType' => 'patient',
                'receiverId'   => $patientId,
                'relatedId'    => (string) $aptId,
                'status'       => 'unread',
                'createdAt'    => $now,
            ]);
        }

        $rescheduled = true;
    }

    json_out([
        'success'     => true,
        'message'     => $rescheduled
            ? "Emergency recorded. Appointment rescheduled to {$newDate}."
            : 'Emergency recorded. No appointment was found for that slot.',
        'rescheduled' => $rescheduled,
        'newDate'     => $newDate,
    ]);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
