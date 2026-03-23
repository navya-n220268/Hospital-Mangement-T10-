<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_id']) && ($_SESSION['user_role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['_id'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Doctor ID required.']);
    exit;
}

try {
    $db = getDB();
    
    $name = $data['name'];
    $fullName = strpos(strtolower($name), 'dr.') !== false ? $name : 'Dr. ' . $name;
    
    $updateFields = [
        'name' => $name,
        'full_name' => $fullName,
        'email' => $data['email'],
        'phone' => $data['phone'] ?? '',
        'department' => $data['department'] ?? 'General',
        'experience' => (int)($data['experience'] ?? 0),
        'qualification' => $data['qualification'] ?? '',
        'status' => $data['status'] ?? 'Active'
    ];
    
    if (!empty($data['password'])) {
        $updateFields['password'] = $data['password'];
    }
    
    $result = $db->doctors->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($id)],
        ['$set' => $updateFields]
    );
    
    echo json_encode(['success' => true, 'message' => 'Doctor updated successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
