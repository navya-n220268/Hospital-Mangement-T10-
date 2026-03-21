<?php
/**
 * MediVita Hospital Management System
 * ─────────────────────────────────────
 * backend/get_error.php
 *
 * Tiny JSON endpoint called by script.js immediately after a failed
 * login or register redirect to retrieve the server-side error text.
 *
 * Response: { "error": "message string" }  or  { "error": null }
 *
 * The session error is cleared after its first read (flash message pattern).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

$error = $_SESSION['auth_error'] ?? null;

// Consume the flash message so it isn't shown twice
unset($_SESSION['auth_error']);

echo json_encode(['error' => $error]);
exit;
