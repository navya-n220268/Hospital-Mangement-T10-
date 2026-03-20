<?php
/**
 * MediVita Hospital Management System
 * ─────────────────────────────────────
 * backend/logout.php
 *
 * Destroys the current PHP session and redirects the user to the login page.
 *
 * Can be called via:
 *   • A direct link: <a href="/medivita/backend/logout.php">Sign Out</a>
 *   • Or via a GET / POST request from JS fetch / axios.
 *
 * Accepts an optional query parameter ?redirect=<url> to send the user
 * to a custom page instead of login.html.
 */

// ── Start (or resume) session before trying to destroy it ──────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Delete all session variables ────────────────────────────────────────────
$_SESSION = [];

// ── Invalidate the session cookie in the browser ────────────────────────────
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',                      // empty value
        time() - 42000,          // expire in the past
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// ── Destroy the server-side session ─────────────────────────────────────────
session_destroy();

// ── If caller wants a JSON response (AJAX logout) ───────────────────────────
$wantsJson = (
    isset($_SERVER['HTTP_ACCEPT']) &&
    strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
);

if ($wantsJson) {
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
    exit;
}

// ── Redirect to login (or a custom page if ?redirect= is set) ───────────────
$customRedirect = filter_input(INPUT_GET, 'redirect', FILTER_SANITIZE_URL);

// Only allow relative URLs (no http:// etc.) to prevent open-redirect attacks
if ($customRedirect && preg_match('/^[a-zA-Z0-9_\-\/\.]+$/', $customRedirect)) {
    $target = '../../' . ltrim($customRedirect, '/');
} else {
    $target = '../../frontend/auth/login.html';
}

header('Location: ' . $target);
exit;
