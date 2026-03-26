<?php
/**
 * Sanjeevani Hospital Management System
 * ─────────────────────────────────────
 * backend/get_user.php
 *
 * Tiny JSON endpoint to fetch the current user's session data.
 * Useful for .html pages (like dashboard.html) that cannot run PHP directly.
 *
 * If authenticated -> returns { success: true, user: { ... } }
 * If NOT authenticated -> returns { success: false, message: ... }
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

// Check if a valid session exists
if (!isAuthenticated()) {
    http_response_code(401);
    json_out([
        'success' => false,
        'message' => 'Not authenticated. Please log in.',
    ]);
}

$userData = getSessionUser();

if (!$userData) {
    http_response_code(401);
    json_out([
        'success' => false,
        'message' => 'Session expired or invalid.',
    ]);
}

// Return safe user details
json_out([
    'success' => true,
    'user'    => $userData,
]);
