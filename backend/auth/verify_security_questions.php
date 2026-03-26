<?php
/**
 * Sanjeevani — backend/auth/verify_security_questions.php
 * POST { email, role, sq_1, sq_2, sq_3, sq_4, sq_5 }
 * Verifies all 5 security answers. On success, stores a short-lived
 * reset token in password_resets (type: security_verified, TTL 10 min).
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'POST required.'], 405);
}

$email = strtolower(trim($_POST['email'] ?? ''));
$role  = sanitise($_POST['role']  ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_out(['success' => false, 'message' => 'Valid email is required.'], 400);
}
if (!in_array($role, ['patient', 'doctor', 'admin'], true)) {
    json_out(['success' => false, 'message' => 'Invalid role.'], 400);
}

// Collect answers (trimmed, lowercase for case-insensitive match)
$submitted = [];
for ($i = 1; $i <= 5; $i++) {
    $ans = strtolower(trim($_POST["sq_{$i}"] ?? ''));
    if ($ans === '') {
        json_out(['success' => false, 'message' => 'All 5 security answers are required.'], 400);
    }
    $submitted[] = $ans;
}

try {
    $db = getDB();
    $collectionMap = ['patient' => 'patients', 'doctor' => 'doctors', 'admin' => 'admins'];
    $user = $db->{$collectionMap[$role]}->findOne(['email' => $email]);

    if (!$user) {
        // Generic message — don't reveal whether email exists
        json_out(['success' => false, 'message' => 'Security answers do not match our records.']);
    }

    $stored = $user['security_answers'] ?? [];
    if (count($stored) < 5) {
        json_out(['success' => false, 'message' => 'Security questions were not set up for this account. Please contact support.']);
    }

    // Compare each answer (case-insensitive)
    for ($i = 0; $i < 5; $i++) {
        if (strtolower(trim($stored[$i])) !== $submitted[$i]) {
            json_out(['success' => false, 'message' => 'One or more answers are incorrect. Please try again.']);
        }
    }

    // All correct — generate a short-lived reset token
    $token   = bin2hex(random_bytes(32));
    $expiry  = new MongoDB\BSON\UTCDateTime((time() + 600) * 1000); // 10 minutes
    $now     = new MongoDB\BSON\UTCDateTime();

    $db->password_resets->updateOne(
        ['email' => $email, 'role' => $role],
        ['$set' => [
            'email'      => $email,
            'role'       => $role,
            'token'      => hash('sha256', $token),
            'type'       => 'security_verified',
            'expires_at' => $expiry,
            'verified'   => true,
            'created_at' => $now,
        ]],
        ['upsert' => true]
    );

    json_out(['success' => true, 'token' => $token]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}
