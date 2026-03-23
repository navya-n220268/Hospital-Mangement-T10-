<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_id']) && ($_SESSION['user_role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['name']) || empty($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Name and Email are required.']);
    exit;
}

try {
    $db = getDB();
    
    // Check if email already exists
    $existing = $db->doctors->findOne(['email' => $data['email']]);
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'Email is already registered.']);
        exit;
    }
    
    // Generate doctor_id
    $count = $db->doctors->countDocuments([]);
    $docId = 'DOC' . str_pad($count + 101, 3, '0', STR_PAD_LEFT);
    
    $name = $data['name'];
    $fullName = strpos(strtolower($name), 'dr.') !== false ? $name : 'Dr. ' . $name;
    
    $newDoc = [
        'doctor_id' => $docId,
        'name' => $name,
        'full_name' => $fullName,
        'email' => $data['email'],
        'phone' => $data['phone'] ?? '',
        'department' => $data['department'] ?? 'General',
        'experience' => (int)($data['experience'] ?? 0),
        'qualification' => $data['qualification'] ?? '',
        'password' => $data['password'] ?? '123456',
        'status' => $data['status'] ?? 'Active',
        'role' => 'doctor',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];
    
    $result = $db->doctors->insertOne($newDoc);
    
    if ($result->getInsertedCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Doctor added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add doctor.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
