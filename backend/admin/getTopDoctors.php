<?php
session_start();
require_once __DIR__ . '/../config.php';

// Only logged-in admins
if (empty($_SESSION['admin_id']) && ($_SESSION['user_role'] ?? '') !== 'admin') {
    json_out(['success' => false, 'message' => 'Unauthorized'], 401);
}

try {
    $db = getDB();
    
    // Aggregate appointments by doctor_name
    $pipeline = [
        ['$group' => [
            '_id' => '$doctor_id',
            'doctorName' => ['$first' => '$doctor_name'],
            'department' => ['$first' => '$department'],
            'appointments' => ['$sum' => 1]
        ]],
        ['$sort' => ['appointments' => -1]],
        ['$limit' => 5]
    ];
    
    $results = $db->appointments->aggregate($pipeline);
    
    $topDoctors = [];
    foreach ($results as $doc) {
        $name = $doc['doctorName'] ?? 'Unknown';
        if (strpos($name, 'Dr. ') !== 0) $name = 'Dr. ' . $name;
        
        $topDoctors[] = [
            'doctorName' => $name,
            'department' => $doc['department'] ?? 'General',
            'appointments' => $doc['appointments']
        ];
    }
    
    json_out($topDoctors);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
}
