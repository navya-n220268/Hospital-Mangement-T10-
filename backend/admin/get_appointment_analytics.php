<?php
/**
 * Appointment Analytics API
 * Returns all analytics data in one response for the appointment-analytics page.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

// Auth check: support both admin_id and user_role session vars
if (empty($_SESSION['admin_id']) && ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db           = getDB();
    $appointments = $db->appointments->find([])->toArray();

    $total     = count($appointments);
    $completed = $pending = $cancelled = $approved = 0;
    $todayCount = $monthCount = 0;

    $today        = date('Y-m-d');
    $currentMonth = date('Y-m');

    $departmentCounts = [];
    $doctorCounts     = [];
    $doctorDepts      = [];
    $monthlyCounts    = [];
    $statusCounts     = [];

    foreach ($appointments as $appt) {
        $status = strtolower($appt['status'] ?? '');

        if ($status === 'completed')  $completed++;
        elseif ($status === 'pending')   $pending++;
        elseif ($status === 'cancelled') $cancelled++;
        elseif ($status === 'approved')  $approved++;

        $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

        $apptDate = $appt['appointment_date'] ?? '';
        if ($apptDate === $today) $todayCount++;
        if (substr($apptDate, 0, 7) === $currentMonth) $monthCount++;

        $dept = $appt['department'] ?? 'Other';
        $departmentCounts[$dept] = ($departmentCounts[$dept] ?? 0) + 1;

        $docName = $appt['doctor_name'] ?? 'Unknown';
        $doctorCounts[$docName] = ($doctorCounts[$docName] ?? 0) + 1;
        if (!isset($doctorDepts[$docName])) {
            $doctorDepts[$docName] = $dept;
        }

        $month = substr($apptDate, 0, 7);
        if ($month) {
            $monthlyCounts[$month] = ($monthlyCounts[$month] ?? 0) + 1;
        }
    }

    arsort($departmentCounts);
    arsort($doctorCounts);

    $doctorRanking = [];
    foreach ($doctorCounts as $name => $count) {
        $doctorRanking[] = [
            'name'       => $name,
            'department' => $doctorDepts[$name] ?? '',
            'count'      => $count,
        ];
    }

    $monthlyTrend = [];
    for ($i = 11; $i >= 0; $i--) {
        $m     = date('Y-m', strtotime("-$i months"));
        $label = date('M Y', strtotime("-$i months"));
        $monthlyTrend[] = [
            'month' => $label,
            'count' => $monthlyCounts[$m] ?? 0,
        ];
    }

    $departmentBreakdown = [];
    foreach ($departmentCounts as $dept => $count) {
        $departmentBreakdown[] = ['department' => $dept, 'count' => $count];
    }

    echo json_encode([
        'success'              => true,
        'totalAppointments'    => $total,
        'completed'            => $completed,
        'pending'              => $pending,
        'cancelled'            => $cancelled,
        'approved'             => $approved,
        'todayAppointments'    => $todayCount,
        'monthlyAppointments'  => $monthCount,
        'departmentBreakdown'  => $departmentBreakdown,
        'doctorRanking'        => $doctorRanking,
        'monthlyTrend'         => $monthlyTrend,
        'statusBreakdown'      => $statusCounts,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
