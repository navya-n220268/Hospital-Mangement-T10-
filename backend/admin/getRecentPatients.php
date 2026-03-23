<?php
session_start();
require_once __DIR__ . '/../config.php';

// Only logged-in admins
if (empty($_SESSION['admin_id']) && ($_SESSION['user_role'] ?? '') !== 'admin') {
    json_out(['success' => false, 'message' => 'Unauthorized'], 401);
}

try {
    $db = getDB();
    
    // Latest 5 registered patients
    $results = $db->patients->find(
        [],
        [
            'sort' => ['created_at' => -1],
            'limit' => 5
        ]
    );
    
    $recentPatients = [];
    foreach ($results as $pat) {
        $dateStr = $pat['created_at'] ?? null;
        if ($dateStr instanceof \MongoDB\BSON\UTCDateTime) {
            $date = $dateStr->toDateTime()->format('Y-m-d');
        } else {
            $date = $dateStr ? date('Y-m-d', strtotime((string)$dateStr)) : '—';
        }

        $recentPatients[] = [
            'name'  => $pat['full_name'] ?? $pat['name'] ?? 'Unknown',
            'email' => $pat['email'] ?? '—',
            'phone' => $pat['phone'] ?? '—',
            'date'  => $date
        ];
    }
    
    json_out($recentPatients);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
}
