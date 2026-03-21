<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$db = getDB();
$doctorId = $_GET['doctorId'] ?? null;

if (!$doctorId) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

try {
    try { $doctorObjId = new MongoDB\BSON\ObjectId($doctorId); } catch(Exception $e) { $doctorObjId = $doctorId; }

    $cursor = $db->messages->find(
        ['doctorId' => $doctorObjId],
        ['sort' => ['createdAt' => 1]]
    );

    $messages = [];
    $patientsInfo = []; // cache

    foreach ($cursor as $msg) {
        $pIdStr = (string)$msg['patientId'];
        
        if (!isset($patientsInfo[$pIdStr])) {
            if (!empty($msg['patientName'])) {
                $patientsInfo[$pIdStr] = $msg['patientName'];
            } else {
                try { $pObjId = new MongoDB\BSON\ObjectId($pIdStr); } catch(Exception $e) { $pObjId = $pIdStr; }
                $patient = $db->patients->findOne([
                    '$or' => [
                        ['_id' => $pObjId],
                        ['_id' => $pIdStr]
                    ]
                ]);
                $patientsInfo[$pIdStr] = $patient ? ($patient['full_name'] ?? $patient['name'] ?? 'Unknown Patient') : 'Unknown Patient';
            }
        }

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
            'patientId' => $pIdStr,
            'patientName' => $patientsInfo[$pIdStr],
            'doctorId' => (string)$msg['doctorId'],
            'dept' => 'Patient', // Generic label for dept as requested by original JS
            'sender' => $msg['sender'],
            'text' => $msg['message'],
            'time' => $timeStr,
            'ts' => $ts,
            'readByDoctor' => $msg['readByDoctor'] ?? true // or logic to handle read status
        ];
    }
    
    echo json_encode($messages);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
