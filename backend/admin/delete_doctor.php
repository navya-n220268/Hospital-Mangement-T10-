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
    echo json_encode(['success' => false, 'message' => 'Doctor ID is required.']);
    exit;
}

try {
    $db = getDB();
    $result = $db->doctors->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    
    if ($result->getDeletedCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Doctor deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Doctor not found.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
