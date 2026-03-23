<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// Ensure user is an admin
if (!isset($_SESSION['admin_id']) && ($_SESSION['user_role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = getDB();

    // 1. Dashboard summary numbers
    $totalPatients = $db->patients->countDocuments([]);
    $totalDoctors = $db->doctors->countDocuments([]);
    $totalAppointments = $db->appointments->countDocuments([]);
    
    // Departments count (distinct departments in doctors collection)
    $departmentsArray = $db->doctors->distinct('department');
    $totalDepartments = count($departmentsArray);

    // 2. Formatting helper
    function formatDate($dateStr) {
        if (!$dateStr) return '—';
        try {
            if ($dateStr instanceof \MongoDB\BSON\UTCDateTime) {
                return $dateStr->toDateTime()->format('d M Y');
            }
            return date('d M Y', strtotime((string)$dateStr));
        } catch (Exception $e) {
            return (string)$dateStr;
        }
    }

    // 3. Prepare recent appointments
    $recentApptCursor = $db->appointments->find(
        [],
        [
            "sort" => ["appointment_date" => -1, "appointment_time" => -1],
            "limit" => 5
        ]
    );

    $recentAppointmentsList = [];
    foreach ($recentApptCursor as $appt) {
        $recentAppointmentsList[] = [
            'patient_name' => $appt['patient_name'] ?? '—',
            'doctor_name'  => $appt['doctor_name'] ?? '—',
            'department'   => $appt['department'] ?? '—',
            'appointment_date' => formatDate($appt['appointment_date'] ?? null),
            'appointment_time' => $appt['appointment_time'] ?? '—',
            'status'       => $appt['status'] ?? 'pending'
        ];
    }

    // 4. Prepare recent patients
    $recentPatientsCursor = $db->patients->find(
        [],
        [
            "sort" => ["created_at" => -1],
            "limit" => 5
        ]
    );
    $recentPatientsList = [];
    foreach ($recentPatientsCursor as $pat) {
        $recentPatientsList[] = [
            'name'  => $pat['full_name'] ?? $pat['name'] ?? '—',
            'email' => $pat['email'] ?? $pat['phone'] ?? '—',
            'date'  => formatDate($pat['created_at'] ?? null)
        ];
    }

    // 5. Prepare recent doctors
    $recentDoctorsCursor = $db->doctors->find(
        [],
        [
            "sort" => ["created_at" => -1],
            "limit" => 5
        ]
    );
    $recentDoctorsList = [];
    foreach ($recentDoctorsCursor as $doc) {
        $recentDoctorsList[] = [
            'name'       => $doc['full_name'] ?? $doc['name'] ?? '—',
            'department' => $doc['department'] ?? 'General',
            'status'     => $doc['status'] ?? 'Active',
            'date'       => formatDate($doc['created_at'] ?? null)
        ];
    }

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_patients' => $totalPatients,
            'total_doctors' => $totalDoctors,
            'total_appointments' => $totalAppointments,
            'total_departments' => $totalDepartments
        ],
        'recent_appointments' => $recentAppointmentsList,
        'recent_patients' => $recentPatientsList,
        'recent_doctors' => $recentDoctorsList
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
