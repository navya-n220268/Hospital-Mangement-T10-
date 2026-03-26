<?php
/**
 * Sanjeevani — backend/auth/verify_otp.php
 * POST { email, role, otp }
 * Validates OTP hash + expiry. On success sets session reset_token.
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'POST required.'], 405);
}

$email = strtolower(trim($_POST['email'] ?? ''));
$role  = sanitise($_POST['role'] ?? '');
$otp   = trim($_POST['otp'] ?? '');

if (empty($email) || empty($otp) || !in_array($role, ['patient', 'doctor', 'admin'], true)) {
    json_out(['success' => false, 'message' => 'Email, role, and OTP are required.'], 400);
}

try {
    $db     = getDB();
    $record = $db->password_resets->findOne(['email' => $email, 'role' => $role]);

    if (!$record) {
        json_out(['success' => false, 'message' => 'No OTP request found. Please request a new OTP.'], 404);
    }

    // Check expiry
    $expiresAt = $record['expires_at'] ?? null;
    if (!$expiresAt || $expiresAt->toDateTime()->getTimestamp() < time()) {
        json_out(['success' => false, 'message' => 'OTP has expired. Please request a new one.'], 410);
    }

    // Verify hash
    $otpHash = hash('sha256', $otp);
    if (!hash_equals($record['otp_hash'] ?? '', $otpHash)) {
        json_out(['success' => false, 'message' => 'Invalid OTP. Please try again.'], 401);
    }

    // Generate short-lived reset token and store in session
    if (session_status() === PHP_SESSION_NONE) session_start();
    $resetToken = bin2hex(random_bytes(24));
    $_SESSION['pwd_reset_token'] = $resetToken;
    $_SESSION['pwd_reset_email'] = $email;
    $_SESSION['pwd_reset_role']  = $role;
    $_SESSION['pwd_reset_exp']   = time() + 600; // 10-min reset window

    // Mark OTP as verified so it can't be reused
    $db->password_resets->updateOne(
        ['email' => $email, 'role' => $role],
        ['$set' => ['verified' => true]]
    );

    json_out(['success' => true, 'token' => $resetToken, 'message' => 'OTP verified. You may now reset your password.']);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
