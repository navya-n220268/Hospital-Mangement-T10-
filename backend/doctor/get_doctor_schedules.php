<?php
/**
 * MediVita Hospital Management System
 * ─────────────────────────────────────
 * backend/get_doctor_schedules.php
 *
 * Returns all appointments assigned to the currently logged-in doctor.
 * Enriches each appointment with patient name and age from the patients collection.
 *
 * Optional GET filters:
 *   ?status=pending|approved|completed|cancelled
 *   ?date=YYYY-MM-DD     (filter by a specific date)
 *   ?range=today|week|all  (convenience date ranges)
 *
 * Response:
 *   { success: true, schedules: [...], stats: { total, pending, approved, completed, cancelled } }
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

// ── Auth check ────────────────────────────────────────────────────────────────
if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if (!$sessionUser || $sessionUser['role'] !== 'doctor') {
    json_out(['success' => false, 'message' => 'Only doctors can access schedules.'], 403);
}

// ── Collect GET params ────────────────────────────────────────────────────────
$doctorId     = $sessionUser['id'];
$statusFilter = sanitise($_GET['status'] ?? '');
$dateFilter   = sanitise($_GET['date']   ?? '');
$range        = sanitise($_GET['range']  ?? 'all');

try {
    $db = getDB();

    // ── Build appointment filter ──────────────────────────────────────────────
    $filter = [];
    try {
        $doctorObjId = new MongoDB\BSON\ObjectId($doctorId);
        $filter['$or'] = [
            ['doctor_id' => $doctorId],
            ['doctor_id' => $doctorObjId]
        ];
    } catch (Exception $e) {
        $filter['doctor_id'] = $doctorId;
    }

    // Status filter
    $validStatuses = ['pending', 'approved', 'completed', 'cancelled'];
    if (!empty($statusFilter) && in_array($statusFilter, $validStatuses)) {
        $filter['status'] = $statusFilter;
    }

    // Date / range filter
    $today = date('Y-m-d');
    if (!empty($dateFilter)) {
        $filter['appointment_date'] = $dateFilter;
    } elseif ($range === 'today') {
        $filter['appointment_date'] = $today;
    } elseif ($range === 'week') {
        $weekEnd = date('Y-m-d', strtotime('+7 days'));
        $filter['appointment_date'] = [
            '$gte' => $today,
            '$lte' => $weekEnd,
        ];
    }
    // 'all' → no date filter

    // ── Fetch appointments sorted by date + time ──────────────────────────────
    $options = [
        'sort' => [
            'appointment_date' => 1,
            'appointment_time' => 1,
        ],
    ];

    $cursor = $db->appointments->find($filter, $options);

    $schedules = [];
    $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'completed' => 0, 'cancelled' => 0];

    foreach ($cursor as $appt) {
        $patientName = $appt['patient_name'] ?? '';
        $patientAge  = null;

        // ── Enrich with patient details from patients collection ──────────────
        $patientId = $appt['patient_id'] ?? '';
        if (!empty($patientId)) {
            try {
                $patientObjId = new MongoDB\BSON\ObjectId($patientId);
                $patient = $db->patients->findOne(
                    ['_id' => $patientObjId],
                    ['projection' => ['_id' => 0, 'name' => 1, 'full_name' => 1, 'age' => 1, 'gender' => 1, 'blood_group' => 1, 'phone' => 1]]
                );
                if ($patient) {
                    // Use patients collection name if appointment name is missing
                    if (empty($patientName)) {
                        $patientName = $patient['full_name'] ?? $patient['name'] ?? 'Unknown Patient';
                    }
                    $patientAge    = isset($patient['age']) ? (int)$patient['age'] : null;
                    $patientGender = $patient['gender']      ?? '';
                    $patientBlood  = $patient['blood_group'] ?? '';
                    $patientPhone  = $patient['phone']       ?? '';
                }
            } catch (Exception $ex) {
                // Invalid ObjectId — skip enrichment
            }
        }

        $status = $appt['status'] ?? 'pending';

        $scheduleRow = [
            '_id'              => (string)$appt['_id'],
            'patient_id'       => $patientId,
            'patient_name'     => $patientName ?: 'Unknown Patient',
            'patient_age'      => $patientAge,
            'patient_gender'   => $patientGender ?? '',
            'patient_blood'    => $patientBlood  ?? '',
            'patient_phone'    => $patientPhone  ?? '',
            'department'       => $appt['department']       ?? '',
            'appointment_date' => $appt['appointment_date'] ?? '',
            'appointment_time' => $appt['appointment_time'] ?? '',
            'appointment_type' => $appt['appointment_type'] ?? 'consultation',
            'reason'           => $appt['reason']           ?? '',
            'status'           => $status,
            'created_at'       => isset($appt['created_at'])
                ? date('Y-m-d H:i:s', $appt['created_at']->toDateTime()->getTimestamp())
                : '',
        ];

        $schedules[] = $scheduleRow;
        $stats['total']++;
        if (isset($stats[$status])) $stats[$status]++;
    }

    json_out([
        'success'   => true,
        'doctor_id' => $doctorId,
        'schedules' => $schedules,
        'stats'     => $stats,
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Failed to fetch schedules: ' . $e->getMessage()], 500);
}
