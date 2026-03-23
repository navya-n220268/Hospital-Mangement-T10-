<?php
/**
 * MediVita Hospital Management System
 * ─────────────────────────────────────
 * backend/book_appointment.php
 *
 * Saves a new appointment to the 'appointments' collection.
 * Associates appointment with patient_id and doctor_id from session/POST.
 * Default status: 'pending'
 *
 * Expected POST fields:
 *   doctor_id, department, appointment_date, appointment_time, reason, appointment_type
 *
 * Returns JSON: { success: true/false, message, appointment_id? }
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'Method not allowed.'], 405);
}

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated. Please log in.'], 401);
}

$sessionUser = getSessionUser();
if (!$sessionUser || $sessionUser['role'] !== 'patient') {
    json_out(['success' => false, 'message' => 'Only patients can book appointments.'], 403);
}

// ── Collect & sanitise inputs ─────────────────────────────────────────────────
$doctorId        = sanitise($_POST['doctor_id']         ?? '');
$department      = sanitise($_POST['department']        ?? '');
$appointmentDate = sanitise($_POST['appointment_date']  ?? '');
$appointmentTime = sanitise($_POST['appointment_time']  ?? '');
$reason          = sanitise($_POST['reason']            ?? '');
$appointmentType = sanitise($_POST['appointment_type']  ?? 'consultation');

// ── Validate required fields ──────────────────────────────────────────────────
$errors = [];
if (empty($doctorId))        $errors[] = 'Doctor is required.';
if (empty($department))      $errors[] = 'Department is required.';
if (empty($appointmentDate)) $errors[] = 'Appointment date is required.';
if (empty($appointmentTime)) $errors[] = 'Appointment time is required.';

// Date must not be in the past
if (!empty($appointmentDate)) {
    $today = date('Y-m-d');
    if ($appointmentDate < $today) {
        $errors[] = 'Appointment date cannot be in the past.';
    }
}

if (!empty($errors)) {
    json_out(['success' => false, 'message' => implode(' ', $errors)], 400);
}

try {
    $db = getDB();

    // Verify doctor exists
    $doctorObjId = new MongoDB\BSON\ObjectId($doctorId);
    $doctor = $db->doctors->findOne(['_id' => $doctorObjId]);
    if (!$doctor) {
        json_out(['success' => false, 'message' => 'Selected doctor not found.'], 404);
    }

    $patientId   = $sessionUser['id'];
    $patientName = $sessionUser['name'];
    $doctorName  = $doctor['full_name'] ?? $doctor['name'] ?? 'Unknown Doctor';
    $doctorDept  = $doctor['department'] ?? $doctor['specialization'] ?? $department;

    $now = new MongoDB\BSON\UTCDateTime();

    // Check for duplicate booking (same doctor, date, time for this patient)
    $existing = $db->appointments->findOne([
        'patient_id'       => $patientId,
        'doctor_id'        => $doctorId,
        'appointment_date' => $appointmentDate,
        'appointment_time' => $appointmentTime,
        'status'           => ['$nin' => ['cancelled']],
    ]);

    if ($existing) {
        json_out(['success' => false, 'message' => 'You already have an appointment with this doctor at the selected date and time.'], 409);
    }

    // ── Build appointment document ─────────────────────────────────────────────
    $appointment = [
        'patient_id'       => $patientId,
        'patient_name'     => $patientName,
        'doctor_id'        => $doctorId,
        'doctor_name'      => $doctorName,
        'department'       => $doctorDept,
        'appointment_date' => $appointmentDate,
        'appointment_time' => $appointmentTime,
        'appointment_type' => $appointmentType,
        'reason'           => $reason,
        'status'           => 'pending',
        'created_at'       => $now,
        'updated_at'       => $now,
    ];

    $result = $db->appointments->insertOne($appointment);

    if (!$result->getInsertedId()) {
        json_out(['success' => false, 'message' => 'Failed to save appointment. Please try again.'], 500);
    }

    $appointmentId = (string)$result->getInsertedId();

    json_out([
        'success'        => true,
        'message'        => 'Appointment booked successfully!',
        'appointment_id' => $appointmentId,
        'doctor_name'    => $doctorName,
        'department'     => $doctorDept,
        'date'           => $appointmentDate,
        'time'           => $appointmentTime,
    ]);

} catch (MongoDB\Driver\Exception\InvalidArgumentException $e) {
    json_out(['success' => false, 'message' => 'Invalid doctor ID format.'], 400);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
}
