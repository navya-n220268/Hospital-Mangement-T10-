<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
$db = getDB();

$patientId = $_GET['patientId'] ?? null;
$doctorId = $_GET['doctorId'] ?? null;

if (!$patientId || !$doctorId) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

try {
    try { $patientObjId = new MongoDB\BSON\ObjectId($patientId); } catch(Exception $e) { $patientObjId = $patientId; }
    try { $doctorObjId = new MongoDB\BSON\ObjectId($doctorId); } catch(Exception $e) { $doctorObjId = $doctorId; }

    $cursor = $db->messages->find(
        ['patientId' => $patientObjId, 'doctorId' => $doctorObjId],
        ['sort' => ['createdAt' => 1]]
    );

    $messages = [];
    foreach ($cursor as $msg) {
        $timeStr = '';
        if (isset($msg['createdAt']) && $msg['createdAt'] instanceof MongoDB\BSON\UTCDateTime) {
            $dt = $msg['createdAt']->toDateTime();
            $dt->setTimezone(new DateTimeZone('Asia/Kolkata'));
            $timeStr = $dt->format('g:i A');
            $ts = $dt->getTimestamp() * 1000;
        } else {
            $timeStr = date('g:i A');
            $ts = time() * 1000;
        }

        $messages[] = [
            'id' => (string)$msg['_id'],
            'sender' => $msg['sender'],
            'text' => $msg['message'],
            'time' => $timeStr,
            'ts' => $ts
        ];
    }
    
    echo json_encode($messages);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
