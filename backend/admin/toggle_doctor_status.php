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
$newStatus = $data['status'] ?? '';

if (empty($id) || empty($newStatus)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    exit;
}

try {
    $db = getDB();
    $result = $db->doctors->updateOne(
        ['_id' => new \MongoDB\BSON\ObjectId($id)],
        ['$set' => ['status' => $newStatus]]
    );
    
    echo json_encode(['success' => true, 'message' => "Doctor status changed to $newStatus."]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
