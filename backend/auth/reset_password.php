<?php
/**
 * Sanjeevani — backend/auth/reset_password.php
 * POST { email, role, token, newPassword }
 * Validates the security-question reset token from MongoDB password_resets,
 * then updates the user's password using password_hash().
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'POST required.'], 405);
}

$email       = strtolower(trim($_POST['email']       ?? ''));
$role        = sanitise($_POST['role']               ?? '');
$token       = $_POST['token']                       ?? '';
$newPassword = $_POST['newPassword']                 ?? '';

if (empty($email) || empty($role) || empty($token) || empty($newPassword)) {
    json_out(['success' => false, 'message' => 'All fields are required.'], 400);
}

if (strlen($newPassword) < 8) {
    json_out(['success' => false, 'message' => 'Password must be at least 8 characters.'], 400);
}

if (!in_array($role, ['patient', 'doctor', 'admin'], true)) {
    json_out(['success' => false, 'message' => 'Invalid role.'], 400);
}

try {
    $db = getDB();

    // ── Validate reset token from MongoDB password_resets ──────────────────────
    $resetDoc = $db->password_resets->findOne([
        'email' => $email,
        'role'  => $role,
        'type'  => 'security_verified',
    ]);

    if (!$resetDoc) {
        json_out(['success' => false, 'message' => 'Invalid or expired reset session. Please start over.'], 401);
    }

    // Check expiry
    $expiresAt = $resetDoc['expires_at']->toDateTime()->getTimestamp();
    if (time() > $expiresAt) {
        $db->password_resets->deleteOne(['email' => $email, 'role' => $role]);
        json_out(['success' => false, 'message' => 'Reset session has expired. Please start over.'], 401);
    }

    // Verify token value (stored as sha256 hash)
    if (!hash_equals($resetDoc['token'], hash('sha256', $token))) {
        json_out(['success' => false, 'message' => 'Invalid reset token. Please start over.'], 401);
    }

    // ── Update password (hashed) ───────────────────────────────────────────────
    $collectionMap = ['patient' => 'patients', 'doctor' => 'doctors', 'admin' => 'admins'];
    $collection    = $collectionMap[$role];

    $result = $db->{$collection}->updateOne(
        ['email' => $email],
        ['$set'  => [
            'password'   => password_hash($newPassword, PASSWORD_DEFAULT),
            'updated_at' => new MongoDB\BSON\UTCDateTime(),
        ]]
    );

    if ($result->getMatchedCount() === 0) {
        json_out(['success' => false, 'message' => 'User not found.'], 404);
    }

    // ── Clean up the used reset token ─────────────────────────────────────────
    $db->password_resets->deleteOne(['email' => $email, 'role' => $role]);

    json_out(['success' => true, 'message' => 'Password reset successfully. You may now log in.']);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
