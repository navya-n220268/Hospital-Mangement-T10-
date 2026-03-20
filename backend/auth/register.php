<?php
/**
 * MediVita Hospital Management System
 * ─────────────────────────────────────
 * backend/register.php
 *
 * AJAX JSON handler for new user registration.
 *
 * Supports roles: patient, doctor. (Admins cannot self-register from frontend).
 *
 * Expected POST fields:
 *   name, email, phone, password, confirm, role
 *   + patient: age, gender
 *   + doctor:  specialization, department, experience
 *
 * JSON response on success:
 *   { success: true, message: "...", redirect: "dashboard.html" }
 */

session_start();
require_once __DIR__ . '/../config.php';

// Always return JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ── Collect & sanitise inputs ─────────────────────────────────────────────────
$name     = sanitise($_POST['name']     ?? '');
$email    = strtolower(sanitise($_POST['email'] ?? ''));
$phone    = sanitise($_POST['phone']    ?? '');
$password = $_POST['password']          ?? '';
$confirm  = $_POST['confirm']           ?? '';
$role     = sanitise($_POST['role']     ?? '');

$specialization = sanitise($_POST['specialization'] ?? '');
$experience     = sanitise($_POST['experience']     ?? '');
$department     = sanitise($_POST['department']     ?? '');
$age            = (int) ($_POST['age']              ?? 0);
$gender         = sanitise($_POST['gender']         ?? '');

// ── Validation ────────────────────────────────────────────────────────────────
$errors = [];

if (mb_strlen($name) < 2) $errors[] = 'Full name must be at least 2 characters.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

$phoneDigits = preg_replace('/\D/', '', $phone);
if (strlen($phoneDigits) < 10) $errors[] = 'Phone number must contain at least 10 digits.';

if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
if ($password !== $confirm) $errors[] = 'Passwords do not match.';

if (!in_array($role, ['patient', 'doctor'], true)) {
    $errors[] = 'Please select a valid role (Patient or Doctor).';
}

if ($role === 'patient') {
    if ($age < 1 || $age > 120) $errors[] = 'Please enter a valid age (1–120).';
    if (!in_array($gender, ['Male', 'Female', 'Other'], true)) $errors[] = 'Please select a valid gender.';
}

if ($role === 'doctor') {
    if (empty($specialization)) $errors[] = 'Specialization is required for doctor accounts.';
    if (empty($department)) $errors[] = 'Department is required for doctor accounts.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── Database operations ───────────────────────────────────────────────────────
try {
    $db         = getDB();
    $collection = ($role === 'patient') ? $db->patients : $db->doctors;

    if ($collection->findOne(['email' => $email])) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists.']);
        exit;
    }

    $now = new MongoDB\BSON\UTCDateTime();

    // Generate unique IDs
    if ($role === 'patient') {
        $patient_id = 'PAT-' . strtoupper(substr(uniqid(), -6));
        $doc = [
            'patient_id' => $patient_id,
            'full_name'  => $name,
            'email'      => $email,
            'phone'      => $phone,
            'gender'     => $gender,
            'age'        => $age,
            'address'    => '',
            'password'   => $password, // Plain text for dev
            'created_at' => $now,
            'role'       => 'patient'
        ];
    } else {
        $doctor_id = 'DOC-' . strtoupper(substr(uniqid(), -6));
        $doc = [
            'doctor_id'      => $doctor_id,
            'full_name'      => $name,
            'email'          => $email,
            'phone'          => $phone,
            'department'     => $department,
            'specialization' => $specialization, // kept for compatibility if needed
            'experience'     => $experience,
            'qualification'  => '',
            'password'       => $password, // Plain text for dev
            'availability'   => 'Available',
            'created_at'     => $now,
            'role'           => 'doctor'
        ];
    }

    $result = $collection->insertOne($doc);

    // ── Start session ─────────────────────────────────────────────────────────
    session_regenerate_id(true);
    $_SESSION['user_id']    = (string) $result->getInsertedId();
    $_SESSION['user_name']  = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role']  = $role;

    if ($role === 'doctor') {
        $_SESSION['doctor_id']    = (string) $result->getInsertedId();
        $_SESSION['doctor_name']  = $name;
        $_SESSION['doctor_email'] = $email;
    } elseif ($role === 'patient') {
        $_SESSION['patient_id']    = (string) $result->getInsertedId();
        $_SESSION['patient_name']  = $name;
        $_SESSION['patient_email'] = $email;
    }

    unset($_SESSION['auth_error'], $_SESSION['auth_form']);

    $redirect = ($role === 'patient') ? '/medivita/frontend/patient/dashboard.html' : '/medivita/frontend/doctor/doctor-dashboard.php';

    echo json_encode([
        'success'  => true,
        'message'  => 'Account created successfully! Redirecting...',
        'redirect' => $redirect,
        'role'     => $role,
        'name'     => $name,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error during registration: ' . $e->getMessage()]);
}
exit;
