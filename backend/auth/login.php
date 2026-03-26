<?php
/**
 * Sanjeevani Hospital Management System
 * ─────────────────────────────────────
 * backend/login.php
 *
 * AJAX handler for user authentication.
 * Checks the correct MongoDB collection based on role.
 * Compares password as plain text (DEV MODE — no hashing).
 *
 * Expected POST fields: email, password, role
 *
 * Returns JSON:
 *   { success: true,  message, redirect, role, name }
 *   { success: false, message }
 */

session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// ── Only accept POST ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ── Collect & sanitise inputs ─────────────────────────────────────────────────
$email    = strtolower(sanitise($_POST['email']    ?? ''));
$password = $_POST['password']                     ?? '';
$role     = sanitise($_POST['role']                ?? '');

// ── Validate required fields ──────────────────────────────────────────────────
if (empty($email) || empty($password) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// ── Validate role ────────────────────────────────────────────────────────────
$validRoles = ['patient', 'doctor', 'admin'];
if (!in_array($role, $validRoles, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role selected.']);
    exit;
}

// ── Role → redirect target (absolute paths) ─────────────────────────────────
$dashboards = [
    'patient' => '/Hospital-Mangement-T10-/frontend/patient/dashboard.html',
    'doctor'  => '/Hospital-Mangement-T10-/frontend/doctor/doctor-dashboard.php',
    'admin'   => '/Hospital-Mangement-T10-/frontend/admin/admin-dashboard.html',
];

// ── Database lookup ──────────────────────────────────────────────────────────
try {
    $db = getDB();

    // Select the correct collection based on role
    switch ($role) {
        case 'patient': $collection = $db->patients; break;
        case 'doctor':  $collection = $db->doctors;  break;
        case 'admin':   $collection = $db->admins;   break;
    }

    // Find user by email
    $user = $collection->findOne(['email' => $email]);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password. Please check your credentials and selected role.']);
        exit;
    }

    $storedPassword = $user['password'] ?? '';
    // Support securely hashed passwords with backwards-compatibility for plain-text dev accounts
    if (!password_verify($password, $storedPassword) && $storedPassword !== $password) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password. Please check your credentials and selected role.']);
        exit;
    }

    // ── Doctor approval gate ─────────────────────────────────────────────────
    if ($role === 'doctor') {
        $approvalStatus = $user['approval_status'] ?? 'approved'; // existing docs treated as approved
        if ($approvalStatus === 'pending') {
            echo json_encode([
                'success' => false, 
                'pending' => true,
                'redirect'=> '/Hospital-Mangement-T10-/frontend/auth/pending-approval.html',
                'message' => 'Your account is awaiting admin approval.'
            ]);
            exit;
        }
        if ($approvalStatus === 'rejected') {
            echo json_encode(['success' => false, 'message' => 'Your registration was rejected by admin. Please contact the hospital.']);
            exit;
        }
    }

    // ── Success — set session ────────────────────────────────────────────────
    session_regenerate_id(true);

    // Try multiple name fields for compatibility
    $userName = $user['full_name'] ?? $user['name'] ?? 'User';

    $_SESSION['user_id']    = (string) $user['_id'];
    $_SESSION['user_name']  = $userName;
    $_SESSION['user_email'] = $user['email'] ?? $email;
    $_SESSION['user_role']  = $role;

    if ($role === 'doctor') {
        $_SESSION['doctor_id']    = (string) $user['_id'];
        $_SESSION['doctor_name']  = $userName;
        $_SESSION['doctor_email'] = $user['email'] ?? $email;
        $_SESSION['department']   = $user['department'] ?? 'General';
    } elseif ($role === 'patient') {
        $_SESSION['patient_id']    = (string) $user['_id'];
        $_SESSION['patient_name']  = $userName;
        $_SESSION['patient_email'] = $user['email'] ?? $email;
    } elseif ($role === 'admin') {
        $_SESSION['admin_id']      = (string) $user['_id'];
        $_SESSION['admin_name']    = $userName;
        $_SESSION['admin_email']   = $user['email'] ?? $email;
    }

    unset($_SESSION['auth_error']);

    echo json_encode([
        'success'  => true,
        'message'  => 'Login successful! Redirecting to your dashboard...',
        'redirect' => $dashboards[$role],
        'role'     => $role,
        'name'     => $userName,
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
exit;
