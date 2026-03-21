<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if (!$sessionUser || $sessionUser['role'] !== 'doctor') {
    json_out(['success' => false, 'message' => 'Doctor session required.'], 403);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    json_out(['success' => false, 'message' => 'No data provided.'], 400);
}

try {
    $db = getDB();
    $doctorId = $_SESSION['doctor_id'] ?? $sessionUser['id'];

    $updateData = [];

    // Map frontend fields to DB fields
    if (isset($data['name'])) $updateData['name'] = $data['name'];
    if (isset($data['email'])) $updateData['email'] = $data['email'];
    if (isset($data['phone'])) $updateData['phone'] = $data['phone'];
    if (isset($data['specialization'])) $updateData['specialization'] = $data['specialization'];
    if (isset($data['experience'])) $updateData['experience'] = $data['experience'];
    if (isset($data['department'])) $updateData['department'] = $data['department'];
    if (isset($data['consultation_fee'])) $updateData['consultation_fee'] = $data['consultation_fee'];
    if (isset($data['gender'])) $updateData['gender'] = $data['gender'];
    
    // Add more fields as needed

    if (empty($updateData)) {
        json_out(['success' => false, 'message' => 'No valid fields to update.']);
    }

    $result = $db->doctors->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($doctorId)],
        ['$set' => $updateData]
    );

    json_out([
        'success' => true,
        'message' => 'Profile updated successfully.',
        'updated' => $result->getModifiedCount()
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()], 500);
}
