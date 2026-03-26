<?php
/**
 * Sanjeevani — backend/add_prescription.php
 *
 * Saves a doctor's prescription to the 'prescriptions' collection.
 *
 * Security:
 *  - Doctor must be authenticated.
 *  - patient_id must correspond to a patient who booked with THIS doctor.
 *
 * POST fields (JSON body):
 *   patient_id, patient_name, appointment_id,
 *   diagnosis, medicines (JSON array), dosage,
 *   instructions, symptoms, next_visit, tests
 *
 * Response:
 *   { success: true, prescription_id, message }
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

$sessionUser = getSessionUser();
if (!$sessionUser || $sessionUser['role'] !== 'doctor') {
    json_out(['success' => false, 'message' => 'Doctor access only.'], 403);
}

// ── Parse JSON body ───────────────────────────────────────────────────────────
$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input) {
    // Fallback to POST fields
    $input = $_POST;
}

// ── Validate required fields ──────────────────────────────────────────────────
$patientId     = sanitise($input['patient_id']     ?? '');
$patientName   = sanitise($input['patient_name']   ?? '');
$appointmentId = sanitise($input['appointment_id'] ?? '');
$diagnosis     = sanitise($input['diagnosis']      ?? '');
$medicines     = $input['medicines'] ?? [];
$notes         = sanitise($input['notes']          ?? '');
$symptoms      = sanitise($input['symptoms']       ?? '');
$nextVisit     = sanitise($input['next_visit']     ?? '');
$tests         = sanitise($input['tests']          ?? '');

// Medicines: JSON string or array
$medicines = $input['medicines'] ?? [];
if (is_string($medicines)) {
    $medicines = json_decode($medicines, true) ?? [];
}

if (empty($patientId) || empty($diagnosis) || empty($medicines)) {
    json_out(['success' => false, 'message' => 'Patient, diagnosis, and at least one medicine are required.'], 400);
}

$doctorId   = $_SESSION['doctor_id']   ?? $sessionUser['id'];
$doctorName = $_SESSION['doctor_name'] ?? $sessionUser['name'];

try {
    $db = getDB();

    // ── Security: verify this patient actually booked with this doctor ─────────
    $appointmentCheck = $db->appointments->findOne([
        'doctor_id'  => $doctorId,
        'patient_id' => $patientId,
        'status'     => ['$ne' => 'cancelled'],
    ]);

    if (!$appointmentCheck) {
        json_out([
            'success' => false,
            'message' => 'You can only prescribe for patients who booked appointments with you.',
        ], 403);
    }

    // Use the appointment_id from the check if not provided
    if (empty($appointmentId) && $appointmentCheck) {
        $appointmentId = (string)$appointmentCheck['_id'];
    }

    $department = $appointmentCheck['department'] ?? 'General';

    // ── Build medical record document ─────────────────────────────────────────
    $now = new MongoDB\BSON\UTCDateTime();

    $record = [
        'patient_id'        => $patientId,
        'patient_name'      => $patientName,
        'doctor_id'         => $doctorId,
        'doctor_name'       => $doctorName,
        'department'        => $department,
        'diagnosis'         => $diagnosis,
        'medicines'         => $medicines,
        'notes'             => $notes,
        'prescription_date' => date('Y-m-d'),
        'created_at'        => $now,
        
        // Extra fields we can safely store for the UI:
        'appointment_id'    => $appointmentId,
        'symptoms'          => $symptoms,
        'next_visit'        => $nextVisit,
        'tests_ordered'     => $tests,
    ];

    $result = $db->prescriptions->insertOne($record);

    if (!$result->getInsertedId()) {
        json_out(['success' => false, 'message' => 'Failed to save prescription.'], 500);
    }

    $prescriptionId = (string)$result->getInsertedId();

    // ── Notify patient about new prescription ─────────────────────────────────
    try {
        $db->notifications->insertOne([
            'type'         => 'prescription',
            'message'      => "Dr. {$doctorName} has added a new prescription for you. Diagnosis: {$diagnosis}. Visit your prescriptions page to view and download it.",
            'receiverType' => 'patient',
            'receiverId'   => $patientId,
            'relatedId'    => $prescriptionId,
            'status'       => 'unread',
            'createdAt'    => $now,
        ]);
    } catch (Exception $notifEx) { /* non-fatal */ }

    // ── Optionally update appointment status to 'completed' ───────────────────
    if (!empty($appointmentId)) {
        try {
            $db->appointments->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($appointmentId)],
                ['$set' => [
                    'status'     => 'completed',
                    'updated_at' => $now,
                ]]
            );
        } catch (Exception $ex) { /* non-fatal */ }
    }

    json_out([
        'success'         => true,
        'message'         => 'Prescription saved successfully.',
        'prescription_id' => $prescriptionId,
        'ref'             => 'MV-RX-' . strtoupper(substr($prescriptionId, -6)),
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
