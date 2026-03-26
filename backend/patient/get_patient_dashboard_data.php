<?php
/**
 * Sanjeevani — backend/get_patient_dashboard_data.php
 *
 * Fetches summary statistics and recent records for the patient dashboard.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if (!$sessionUser || $sessionUser['role'] !== 'patient') {
    json_out(['success' => false, 'message' => 'Patient access only.'], 403);
}

$patientId = $_SESSION['patient_id'] ?? $sessionUser['id'];

try {
    $db = getDB();
    $today = date('Y-m-d');

    // Counts
    $totalAppointments = $db->appointments->countDocuments([
        'patient_id' => $patientId
    ]);

    $upcomingCount = $db->appointments->countDocuments([
        'patient_id' => $patientId,
        'appointment_date' => ['$gte' => $today],
        'status' => ['$ne' => 'cancelled']
    ]);

    $totalPrescriptions = $db->prescriptions->countDocuments([
        'patient_id' => $patientId
    ]);

    // Upcoming Appointments List (Max 5)
    $cursorUpcoming = $db->appointments->find(
        [
            'patient_id' => $patientId,
            'appointment_date' => ['$gte' => $today],
            'status' => ['$ne' => 'cancelled']
        ],
        [
            'sort' => ['appointment_date' => 1, 'appointment_time' => 1],
            'limit' => 5
        ]
    );

    $upcomingList = [];
    foreach ($cursorUpcoming as $appt) {
        $docId = $appt['doctor_id'] ?? '';
        $docInfo = $db->doctors->findOne(['_id' => new MongoDB\BSON\ObjectId($docId)], ['projection' => ['is_available' => 1, 'unavailability_reason' => 1]]);
        
        $upcomingList[] = [
            '_id'              => (string)$appt['_id'],
            'doctor_name'      => $appt['doctor_name'] ?? 'Doctor',
            'department'       => $appt['department'] ?? 'General',
            'appointment_date' => $appt['appointment_date'] ?? '',
            'appointment_time' => $appt['appointment_time'] ?? '',
            'status'           => $appt['status'] ?? 'pending',
            'is_doctor_available' => $docInfo['is_available'] ?? true,
            'doctor_unavail_reason' => $docInfo['unavailability_reason'] ?? ''
        ];
    }

    // Recent Prescriptions List (Max 5)
    $cursorRx = $db->prescriptions->find(
        ['patient_id' => $patientId],
        [
            'sort' => ['prescription_date' => -1, 'created_at' => -1],
            'limit' => 5
        ]
    );

    $recentRx = [];
    foreach ($cursorRx as $rx) {
        $recentRx[] = [
            '_id'               => (string)$rx['_id'],
            'doctor_name'       => $rx['doctor_name'] ?? 'Doctor',
            'department'        => $rx['department'] ?? 'General',
            'diagnosis'         => $rx['diagnosis'] ?? '',
            'medicines'         => $rx['medicines'] ?? [],
            'prescription_date' => $rx['prescription_date'] ?? ''
        ];
    }

    json_out([
        'success'             => true,
        'totalAppointments'   => $totalAppointments,
        'upcomingAppointments'=> $upcomingCount,
        'totalPrescriptions'  => $totalPrescriptions,
        'upcomingList'        => $upcomingList,
        'recentPrescriptions' => $recentRx
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
