<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (empty($_SESSION['patient_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$current_password = $_POST['current_password'] ?? '';
$new_password     = $_POST['new_password']     ?? '';

if (empty($current_password) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (strlen($new_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters.']);
    exit;
}

try {
    $db = getDB();
    $patient_id = $_SESSION['patient_id'];

    $user = $db->patients->findOne(['_id' => new MongoDB\BSON\ObjectId($patient_id)]);
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    $storedPassword = $user['password'] ?? '';
    $isValid = false;

    // Verify current password via hash or plain-text fallback
    if (password_verify($current_password, $storedPassword)) {
        $isValid = true;
    } elseif ($storedPassword === $current_password) {
        $isValid = true;
    }

    if (!$isValid) {
        echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
        exit;
    }

    // Hash the new password securely
    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

    $db->patients->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($patient_id)],
        ['$set' => [
            'password' => $hashedPassword,
            'password_updated_at' => new MongoDB\BSON\UTCDateTime()
        ]]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Password updated securely.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error changing password: ' . $e->getMessage()
    ]);
}
exit;
