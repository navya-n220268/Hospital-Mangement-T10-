<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
$db = getDB();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$doctorId = $data['doctorId'] ?? null;
$patientId = $data['patientId'] ?? null;
$message = $data['message'] ?? null;
$appointmentId = $data['appointmentId'] ?? null;

if (!$doctorId || !$patientId || !$message) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    try { $patientObjId = new MongoDB\BSON\ObjectId($patientId); } catch(Exception $e) { $patientObjId = $patientId; }
    try { $doctorObjId = new MongoDB\BSON\ObjectId($doctorId); } catch(Exception $e) { $doctorObjId = $doctorId; }
    $apptObjId = null;
    if ($appointmentId) {
        try { $apptObjId = new MongoDB\BSON\ObjectId($appointmentId); } catch(Exception $e) { $apptObjId = $appointmentId; }
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
        'appointmentId' => $apptObjId,
        'sender' => 'doctor',
        'message' => $message,
        'createdAt' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode(['success' => true, 'message' => 'Reply sent successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
