<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
$db = getDB();

session_start();

$patientId = $_GET['patientId'] ?? $_SESSION['patient_id'] ?? $_SESSION['user_id'] ?? null;

if (!$patientId) {
    echo json_encode([]);
    exit;
}

try {
    try {
        $patientObjId = new MongoDB\BSON\ObjectId($patientId);
    } catch (Exception $e) {
        $patientObjId = $patientId;
    }

    // Fetch appointments for this patient that are confirmed/booked/approved
    $appointments = [];
    $cursor = $db->appointments->find([
        '$and' => [
            [
                '$or' => [
                    ['patient_id' => $patientId],
                    ['patient_id' => $patientObjId],
                    ['patientId' => $patientId],
                    ['patientId' => $patientObjId]
                ]
            ],
            [
                'status' => ['$in' => ['booked', 'confirmed', 'approved', 'pending']]
            ]
        ]
    ]);

    $docs = [];
    $addedDocs = [];
    foreach ($cursor as $appt) {
        // Extract doctor_id string
        $docIdRaw = $appt['doctor_id'] ?? $appt['doctorId'] ?? null;
        if (!$docIdRaw) continue;
        
        $docIdStr = (string)$docIdRaw;
        
        if (!in_array($docIdStr, $addedDocs)) {
            try {
                $docObjectId = new MongoDB\BSON\ObjectId($docIdStr);
            } catch (Exception $e) {
                // If it's not a valid 24-char hex string, maybe it was literally saved as a generic string/int
                $docObjectId = $docIdStr;
            }
            
            // Search by ObjectId or the literal string depending on database type inconsistency
            $doctor = $db->doctors->findOne([
                '$or' => [
                    ['_id' => $docObjectId],
                    ['_id' => $docIdStr]
                ]
            ]);

            if ($doctor) {
                // Determine initials
                $docName = $doctor['full_name'] ?? $doctor['name'] ?? 'Dr. Unknown';
                $nameParts = explode(' ', str_replace('Dr. ', '', $docName));
                $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                if (strlen($initials) < 1) $initials = "DR";
                
                // Color array
                $colors = ['#2979cc', '#0ea5a0', '#7c3aed', '#eab308', '#ef4444'];
                $color = $colors[count($addedDocs) % count($colors)];

                $dateStr = 'TBD';
                if (isset($appt['appointment_date']) && is_string($appt['appointment_date'])) {
                    $dateStr = date('M d, Y', strtotime($appt['appointment_date']));
                } elseif (isset($appt['appointmentDate'])) {
                    $dateStr = $appt['appointmentDate'];
                }
                
                $timeStr = 'TBD';
                if (isset($appt['appointment_time']) && is_string($appt['appointment_time'])) {
                    $timeStr = date('h:i A', strtotime($appt['appointment_time']));
                } elseif (isset($appt['appointmentTime'])) {
                    $timeStr = $appt['appointmentTime'];
                }

                $docs[] = [
                    'id' => $docIdStr, // string
                    'doctorName' => $docName,
                    'initials' => $initials,
                    'specialization' => $doctor['department'] ?? $doctor['specialization'] ?? 'Specialist',
                    'appointmentDate' => $dateStr,
                    'appointmentTime' => $timeStr,
                    'room' => 'OPD Dept',
                    'color' => $color,
                    'appointmentId' => (string)$appt['_id']
                ];
                $addedDocs[] = $docIdStr;
            }
        }
    }
    
    echo json_encode($docs);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
