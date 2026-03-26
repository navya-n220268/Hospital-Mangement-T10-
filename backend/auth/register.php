<?php
/**
 * Sanjeevani Hospital Management System
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

// Security question answers — same 5 questions for all users
$securityAnswers = [];
for ($i = 1; $i <= 5; $i++) {
    $ans = strtolower(trim($_POST["sq_{$i}"] ?? ''));
    $securityAnswers[] = $ans;
}
$securityAnswersValid = count(array_filter($securityAnswers, fn($a) => $a !== '')) === 5;

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

if (!$securityAnswersValid) {
    $errors[] = 'Please answer all 5 security questions.';
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
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => $now,
            'role'       => 'patient',
            'auth_type'  => 'manual',
            'is_verified'=> true,
            'security_answers' => $securityAnswers
        ];
    } else {
        $doctor_id = 'DOC-' . strtoupper(substr(uniqid(), -6));
        $doc = [
            'doctor_id'      => $doctor_id,
            'full_name'      => $name,
            'email'          => $email,
            'phone'          => $phone,
            'department'     => $department,
            'specialization' => $specialization,
            'experience'     => $experience,
            'qualification'  => '',
            'password'       => password_hash($password, PASSWORD_DEFAULT),
            'availability'   => 'Unavailable',   // set Available only on approval
            'approval_status'=> 'pending',        // ← NEW: requires admin approval
            'created_at'     => $now,
            'role'           => 'doctor',
            'auth_type'      => 'manual',
            'is_verified'    => true,
            'security_answers' => $securityAnswers
        ];
    }

    $result  = $collection->insertOne($doc);
    $insertedId = (string) $result->getInsertedId();

    // IMMEDIATELY notify admin about new pending doctor
    if ($role === 'doctor') {
        $db->notifications->insertOne([
            'type'         => 'doctor_registration',
            'message'      => "New doctor registration: {$name} ({$email}) — {$department}. Awaiting approval.",
            'receiverType' => 'admin',
            'relatedId'    => (string)$insertedId,
            'status'       => 'unread',
            'createdAt'    => $now,
        ]);
    }

    echo json_encode([
        'success'     => true,
        'require_otp' => false,
        'role'        => $role,
        'email'       => $email,
        'id'          => $insertedId,
        'message'     => 'Registration successful.',
        'pending'     => ($role === 'doctor'),
        'redirect'    => ($role === 'patient') ? '/Hospital-Mangement-T10-/frontend/patient/dashboard.html' : ''
    ]);


} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error during registration: ' . $e->getMessage()]);
}
exit;
