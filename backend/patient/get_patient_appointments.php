<?php
/**
 * Sanjeevani Hospital Management System
 * ─────────────────────────────────────
 * backend/get_patient_appointments.php
 *
 * Returns all appointments for the currently logged-in patient.
 * Enriches each appointment with doctor name and department.
 * Optionally filter by: ?status=pending|approved|completed|cancelled
 *
 * Response: { success: true, appointments: [...], total: N }
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if (!$sessionUser || $sessionUser['role'] !== 'patient') {
    json_out(['success' => false, 'message' => 'Access denied.'], 403);
}

$patientId     = $sessionUser['id'];
$statusFilter  = sanitise($_GET['status'] ?? '');

try {
    $db = getDB();

    // Build query filter
    $filter = ['patient_id' => $patientId];
    if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'approved', 'completed', 'cancelled'])) {
        $filter['status'] = $statusFilter;
    }

    // Sort by appointment_date descending (newest first)
    $options = [
        'sort' => ['appointment_date' => -1, 'appointment_time' => 1],
    ];

    $cursor = $db->appointments->find($filter, $options);

    $appointments = [];
    foreach ($cursor as $appt) {
        // Format date nicely
        $dateStr = $appt['appointment_date'] ?? '';

        $appointments[] = [
            '_id'              => (string)$appt['_id'],
            'patient_id'       => $appt['patient_id'] ?? '',
            'patient_name'     => $appt['patient_name'] ?? '',
            'doctor_id'        => $appt['doctor_id'] ?? '',
            'doctor_name'      => $appt['doctor_name'] ?? 'Unknown Doctor',
            'department'       => $appt['department'] ?? 'General',
            'appointment_date' => $dateStr,
            'appointment_time' => $appt['appointment_time'] ?? '',
            'appointment_type' => $appt['appointment_type'] ?? 'consultation',
            'reason'           => $appt['reason'] ?? '',
            'status'           => $appt['status'] ?? 'pending',
            'created_at'       => isset($appt['created_at'])
                                    ? date('Y-m-d H:i:s', $appt['created_at']->toDateTime()->getTimestamp())
                                    : '',
        ];
    }

    json_out([
        'success'      => true,
        'appointments' => $appointments,
        'total'        => count($appointments),
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Failed to fetch appointments: ' . $e->getMessage()], 500);
}
