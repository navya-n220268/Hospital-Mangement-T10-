<?php
/**
 * MediVita Hospital Management System
 * google-callback.php — OAuth 2.0 callback handler.
 *
 * Flow:
 *  1. Validate CSRF state.
 *  2. Exchange authorisation code for access token.
 *  3. Fetch user profile from Google.
 *  4. Check MongoDB:
 *     - email found  → set session → redirect to correct dashboard.
 *     - email absent → store name/email/google_id in session
 *                      → redirect to complete-profile.html.
 */

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

// ── Google OAuth credentials (must match google-login.php) ──────
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID']);
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET']);
define('GOOGLE_REDIRECT_URI', 'http://localhost/medivita/backend/auth/google-callback.php');

// ── CSRF state validation ────────────────────────────────────────
$returnedState = $_GET['state'] ?? '';
$savedState = $_SESSION['oauth_state'] ?? '';

if (empty($returnedState) || !hash_equals($savedState, $returnedState)) {
    $_SESSION['auth_error'] = 'Security check failed. Please try signing in again.';
    header('Location: /medivita/frontend/auth/login.html');
    exit;
}
unset($_SESSION['oauth_state']);

// ── Handle user cancellation or errors from Google ───────────────
if (isset($_GET['error'])) {
    $errMsg = htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
    $_SESSION['auth_error'] = 'Google sign-in was cancelled or failed: ' . $errMsg;
    header('Location: /medivita/frontend/auth/login.html');
    exit;
}

if (empty($_GET['code'])) {
    $_SESSION['auth_error'] = 'No authorisation code received from Google.';
    header('Location: /medivita/frontend/auth/login.html');
    exit;
}

// ── Build Google client & exchange code for token ────────────────
$client = new Google\Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);

try {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
}
catch (Exception $e) {
    $_SESSION['auth_error'] = 'Failed to retrieve access token from Google.';
    header('Location: /medivita/frontend/auth/login.html');
    exit;
}

if (isset($token['error'])) {
    $_SESSION['auth_error'] = 'Google token error: ' . htmlspecialchars($token['error_description'] ?? $token['error'], ENT_QUOTES, 'UTF-8');
    header('Location: /medivita/frontend/auth/login.html');
    exit;
}

$client->setAccessToken($token);

// ── Fetch user profile ───────────────────────────────────────────
try {
    $oauth2 = new Google\Service\Oauth2($client);
    $googleUser = $oauth2->userinfo->get();
}
catch (Exception $e) {
    $_SESSION['auth_error'] = 'Could not retrieve your profile from Google.';
    header('Location: /medivita/frontend/auth/login.html');
    exit;
}

$googleId = $googleUser->id ?? '';
$googleEmail = strtolower(trim($googleUser->email ?? ''));
$googleName = trim($googleUser->name ?? '');

if (empty($googleEmail)) {
    $_SESSION['auth_error'] = 'Google did not return an email address. Please check your Google account settings.';
    header('Location: /medivita/frontend/auth/login.html');
    exit;
}

// ── Check MongoDB for existing user ─────────────────────────────
$db = getDB();

// Search across both patients and doctors collections
$existingPatient = $db->patients->findOne(['email' => $googleEmail]);
$existingDoctor = $db->doctors->findOne(['email' => $googleEmail]);

if ($existingPatient) {
    // ── Existing patient — log in directly ───────────────────────
    session_regenerate_id(true);
    $u = $existingPatient;
    $userName = $u['full_name'] ?? $u['name'] ?? $googleName;

    $_SESSION['user_id'] = (string)$u['_id'];
    $_SESSION['user_name'] = $userName;
    $_SESSION['user_email'] = $googleEmail;
    $_SESSION['user_role'] = 'patient';
    $_SESSION['patient_id'] = (string)$u['_id'];
    $_SESSION['patient_name'] = $userName;
    $_SESSION['patient_email'] = $googleEmail;
    $_SESSION['google_login'] = true;

    // Update google_id if not stored yet
    if (empty($u['google_id'])) {
        $db->patients->updateOne(
        ['_id' => $u['_id']],
        ['$set' => ['google_id' => $googleId]]
        );
    }

    header('Location: /medivita/frontend/patient/dashboard.html');
    exit;

}
elseif ($existingDoctor) {
    // ── Existing doctor — log in directly ────────────────────────
    session_regenerate_id(true);
    $u = $existingDoctor;
    $userName = $u['full_name'] ?? $u['name'] ?? $googleName;

    $_SESSION['user_id'] = (string)$u['_id'];
    $_SESSION['user_name'] = $userName;
    $_SESSION['user_email'] = $googleEmail;
    $_SESSION['user_role'] = 'doctor';
    $_SESSION['doctor_id'] = (string)$u['_id'];
    $_SESSION['doctor_name'] = $userName;
    $_SESSION['doctor_email'] = $googleEmail;
    $_SESSION['google_login'] = true;

    if (empty($u['google_id'])) {
        $db->doctors->updateOne(
        ['_id' => $u['_id']],
        ['$set' => ['google_id' => $googleId]]
        );
    }

    header('Location: /medivita/frontend/doctor/doctor-dashboard.php');
    exit;

}
else {
    // ── New user — collect extra profile info ────────────────────
    $_SESSION['google_id'] = $googleId;
    $_SESSION['google_name'] = $googleName;
    $_SESSION['google_email'] = $googleEmail;
    $_SESSION['oauth_new_user'] = true;

    header('Location: /medivita/frontend/auth/complete-profile.html');
    exit;
}