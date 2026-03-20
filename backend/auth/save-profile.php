<?php
/**
 * MediVita Hospital Management System
 * save-profile.php — Saves Google OAuth new-user profile to MongoDB.
 *
 * Expects POST: phone, role
 * Session must contain: google_id, google_name, google_email, oauth_new_user=true
 * Returns JSON: { success, message, redirect }
 */

session_start();
require_once __DIR__ . '/../config.php';

// We'll set the Content-Type header later to avoid flushing headers early.

// ── Helper ───────────────────────────────────────────────────────
function fail(string $msg): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// ── Guard: must come from Google OAuth flow ──────────────────────
if (empty($_SESSION['oauth_new_user']) || empty($_SESSION['google_email'])) {
    fail('Session expired or invalid. Please sign in with Google again.');
}

// ── Only accept POST ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Invalid request method.');
}

// ── Collect & sanitise inputs ────────────────────────────────────
$phone = sanitise($_POST['phone'] ?? '');
$role  = sanitise($_POST['role']  ?? '');

// ── Server-side validation ───────────────────────────────────────
if (empty($phone)) {
    fail('Phone number is required.');
}
if (!preg_match('/^\+?[\d\s\-().]{7,15}$/', $phone)) {
    fail('Please enter a valid phone number (7–15 digits, optional + prefix).');
}
if (!in_array($role, ['patient', 'doctor'], true)) {
    fail('Please select a valid role (Patient or Doctor).');
}

// ── Pull Google data from session ────────────────────────────────
$googleId    = $_SESSION['google_id']    ?? '';
$googleName  = $_SESSION['google_name']  ?? '';
$googleEmail = $_SESSION['google_email'] ?? '';

// ── Connect to MongoDB ───────────────────────────────────────────
$db = getDB();

// Check for duplicate email across both collections
$existingPatient = $db->patients->findOne(['email' => $googleEmail]);
$existingDoctor  = $db->doctors->findOne(['email'  => $googleEmail]);

if ($existingPatient || $existingDoctor) {
    fail('An account with this email already exists. Please sign in normally.');
}

// ── Build document ───────────────────────────────────────────────
$now = new MongoDB\BSON\UTCDateTime();

$document = [
    'name'       => $googleName,
    'full_name'  => $googleName,
    'email'      => $googleEmail,
    'phone'      => $phone,
    'role'       => $role,
    'google_id'  => $googleId,
    'auth_type'  => 'google',
    'created_at' => $now,
    'updated_at' => $now,
];

// ── Insert into correct collection ───────────────────────────────
$collection = ($role === 'doctor') ? $db->doctors : $db->patients;

try {
    $result = $collection->insertOne($document);
} catch (Exception $e) {
    fail('Database error: ' . $e->getMessage());
}

$insertedId = (string)$result->getInsertedId();

// ── Set session ──────────────────────────────────────────────────
session_regenerate_id(true);

$_SESSION['user_id']    = $insertedId;
$_SESSION['user_name']  = $googleName;
$_SESSION['user_email'] = $googleEmail;
$_SESSION['user_role']  = $role;
$_SESSION['google_login'] = true;

// Clear OAuth temporary session data
unset($_SESSION['oauth_new_user'], $_SESSION['google_id'],
      $_SESSION['google_name'],   $_SESSION['google_email']);

if ($role === 'patient') {
    $_SESSION['patient_id']    = $insertedId;
    $_SESSION['patient_name']  = $googleName;
    $_SESSION['patient_email'] = $googleEmail;
    $redirect = '/medivita/frontend/patient/dashboard.html';
} else {
    $_SESSION['doctor_id']    = $insertedId;
    $_SESSION['doctor_name']  = $googleName;
    $_SESSION['doctor_email'] = $googleEmail;
    $redirect = '/medivita/frontend/doctor/doctor-dashboard.php';
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success'  => true,
    'message'  => 'Profile saved successfully.',
    'redirect' => $redirect,
]);
exit;
