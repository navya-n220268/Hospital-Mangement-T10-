<?php
/**
 * MediVita Hospital Management System
 * get-session-info.php — Returns Google OAuth session data as JSON.
 * Used by complete-profile.html to populate the user info strip.
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// Only return data if the user just came through Google OAuth flow
if (!empty($_SESSION['oauth_new_user'])) {
    echo json_encode([
        'google_name'  => $_SESSION['google_name']  ?? '',
        'google_email' => $_SESSION['google_email'] ?? '',
    ]);
} else {
    // No valid OAuth session — signal to JS to redirect away
    echo json_encode([
        'google_name'  => '',
        'google_email' => '',
    ]);
}
exit;
