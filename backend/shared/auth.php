<?php
/**
 * Sanjeevani Hospital Management System
 * ─────────────────────────────────────
 * backend/auth.php
 *
 * Session validation middleware.
 *
 * Usage – include at the top of any protected PHP page:
 *
 *   require_once __DIR__ . '/backend/auth.php';
 *   requireAuth();                     // any logged-in user
 *   requireAuth('doctor');             // doctors only
 *   requireAuth('admin');              // admins only
 *   requireAuth(['doctor','admin']);   // multiple roles
 *
 * For HTML pages that need session data (e.g. for a JS fetch), include
 * this file and call getSessionUser() to obtain the session array.
 */

// Start (or resume) the session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Enforce authentication and optionally role-based access.
 *
 * If the user is not authenticated they are redirected to the login page.
 * If the user lacks the required role a 403 JSON response is returned.
 *
 * @param string|string[]|null $roles  Allowed role(s). NULL = any logged-in user.
 */
function requireAuth($roles = null): void
{
    // ── Check session exists ────────────────────────────────────────────────
    if (empty($_SESSION['user_id']) || empty($_SESSION['user_role'])) {
        // Store intended URL so we can redirect back after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        redirect('../login.html');
    }

    // ── Role check (optional) ───────────────────────────────────────────────
    if ($roles !== null) {
        $allowed = is_array($roles) ? $roles : [$roles];
        if (!in_array($_SESSION['user_role'], $allowed, true)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Access denied. You do not have permission to view this resource.',
            ]);
            exit;
        }
    }
}

/**
 * Return the current authenticated user's session data as an array,
 * or NULL if no valid session exists.
 *
 * @return array|null
 */
function getSessionUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name']  ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role']  ?? '',
    ];
}

/**
 * Check whether the current user is authenticated (without enforcing it).
 *
 * @return bool
 */
function isAuthenticated(): bool
{
    return !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
}

/**
 * Check whether the current user possesses a specific role.
 *
 * @param  string $role
 * @return bool
 */
function hasRole(string $role): bool
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// ── redirect() helper (also defined in config.php; safe to redefine) ────────
if (!function_exists('redirect')) {
    function redirect(string $url, int $code = 302): void
    {
        http_response_code($code);
        header("Location: $url");
        exit;
    }
}
