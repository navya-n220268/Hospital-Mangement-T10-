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

// Collect POST data
$name        = sanitise($_POST['full_name']   ?? '');
$phone       = sanitise($_POST['phone']       ?? '');
$age         = (int) ($_POST['age']           ?? 0);
$gender      = sanitise($_POST['gender']      ?? '');
$blood_group = sanitise($_POST['blood_group'] ?? '');
$address     = sanitise($_POST['address']     ?? '');

$errors = [];

if (mb_strlen($name) < 2) {
    $errors[] = 'Full name must be at least 2 characters.';
}

$phoneDigits = preg_replace('/\D/', '', $phone);
if (strlen($phoneDigits) < 10) {
    $errors[] = 'Phone number must contain at least 10 digits.';
}

if ($age < 1 || $age > 120) {
    $errors[] = 'Please enter a valid age (1–120).';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $db = getDB();
    $patient_id = $_SESSION['patient_id'];

    $updateData = [
        'full_name'   => $name,
        'phone'       => $phone,
        'age'         => $age,
        'gender'      => $gender,
        'blood_group' => $blood_group,
        'address'     => $address,
        'updated_at'  => new MongoDB\BSON\UTCDateTime()
    ];

    $result = $db->patients->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($patient_id)],
        ['$set' => $updateData]
    );

    // Update session name if it changed
    $_SESSION['patient_name'] = $name;
    $_SESSION['user_name'] = $name;

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully!'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating profile: ' . $e->getMessage()
    ]);
}
exit;
