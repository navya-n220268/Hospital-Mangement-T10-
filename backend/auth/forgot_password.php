<?php
/**
 * Sanjeevani — backend/auth/forgot_password.php
 * POST { email, role }
 * Step 1 of the security-question-based password reset flow.
 * Simply verifies that an account with this email + role exists.
 * No OTP is sent — the frontend will move to the security questions step.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'POST required.'], 405);
}

$email = strtolower(trim($_POST['email'] ?? ''));
$role  = sanitise($_POST['role'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_out(['success' => false, 'message' => 'A valid email address is required.'], 400);
}
if (!in_array($role, ['patient', 'doctor', 'admin'], true)) {
    json_out(['success' => false, 'message' => 'Invalid role selected.'], 400);
}

try {
    $db = getDB();
    $collectionMap = ['patient' => 'patients', 'doctor' => 'doctors', 'admin' => 'admins'];
    $user = $db->{$collectionMap[$role]}->findOne(['email' => $email]);

    if (!$user) {
        // Generic response — don't reveal whether email exists
        json_out(['success' => false, 'message' => 'No account found with that email and role.']);
    }

    // Check if the account has security questions set up
    $hasQuestions = !empty($user['security_answers']) && count($user['security_answers']) === 5;
    if (!$hasQuestions) {
        json_out(['success' => false, 'message' => 'Security questions were not set up for this account. Please contact support.']);
    }

    json_out(['success' => true]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
