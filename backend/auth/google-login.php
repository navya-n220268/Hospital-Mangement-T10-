<?php
/**
 * MediVita Hospital Management System
 * google-login.php — Entry point for Google OAuth 2.0 login.
 * Generates CSRF state, builds the auth URL, and redirects to Google.
 */

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

// ── Google OAuth credentials ────────────────────────────────────
// Replace these with the values from Google Cloud Console → Credentials
define('GOOGLE_CLIENT_ID',     $_ENV['GOOGLE_CLIENT_ID']);
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET']);
define('GOOGLE_REDIRECT_URI',  'http://localhost/medivita/backend/auth/google-callback.php');

$client = new Google\Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope('email');
$client->addScope('profile');
$client->addScope('openid');
$client->setAccessType('online');
$client->setPrompt('select_account');   // always show account picker

// Generate CSRF state token and persist in session
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$client->setState($state);

header('Location: ' . filter_var($client->createAuthUrl(), FILTER_SANITIZE_URL));
exit;
