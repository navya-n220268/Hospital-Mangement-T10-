<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
$db = getDB();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$patientId = $data['patientId'] ?? null;
$doctorId = $data['doctorId'] ?? null;
$message = $data['message'] ?? null;

if (!$patientId || !$doctorId || !$message) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    try { $patientObjId = new MongoDB\BSON\ObjectId($patientId); } catch(Exception $e) { $patientObjId = $patientId; }
    try { $doctorObjId = new MongoDB\BSON\ObjectId($doctorId); } catch(Exception $e) { $doctorObjId = $doctorId; }

    // Check appointments (can be under patientId/doctorId or patient_id/doctor_id)
    $appointment = $db->appointments->findOne([
        '$or' => [
            ['patient_id' => $patientId, 'doctor_id' => $doctorId],
            ['patient_id' => $patientObjId, 'doctor_id' => $doctorObjId],
            ['patientId' => $patientId, 'doctorId' => $doctorId],
            ['patientId' => $patientObjId, 'doctorId' => $doctorObjId]
        ]
    ]);

    if (!$appointment) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'You can message this doctor only after booking an appointment.']);
        exit;
    }

    $patientInfo = $db->patients->findOne([
        '$or' => [
            ['_id' => $patientObjId],
            ['_id' => $patientId]
        ]
    ]);
    $patientName = $patientInfo ? ($patientInfo['full_name'] ?? $patientInfo['name'] ?? 'Unknown Patient') : 'Unknown Patient';

    $db->messages->insertOne([
        'patientId' => $patientObjId,
        'patientName' => $patientName,
        'doctorId' => $doctorObjId,
        'appointmentId' => $appointment['_id'],
        'sender' => 'patient',
        'message' => $message,
        'createdAt' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
