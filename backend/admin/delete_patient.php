<?php
/**
 * Delete a patient from MongoDB
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
$db = getDB();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

if (empty($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
    exit;
}

try {
    $db = getDatabaseConnection();
    $id = new MongoDB\BSON\ObjectId($input['id']);
    $result = $db->patients->deleteOne(['_id' => $id]);

    if ($result->getDeletedCount() === 1) {
        echo json_encode(['success' => true, 'message' => 'Patient deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Patient not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
