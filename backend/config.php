<?php
/**
 * Sanjeevani Hospital Management System
 * ─────────────────────────────────────
 * backend/config.php
 *
 * Centralised MongoDB Atlas configuration.
 * Provides getDB(), json_out(), sanitise(), and redirect() helpers
 * used by every backend script.
 */

// ── Prevent direct browser access ──────────────────────────────────────────
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(403);
    exit('Access denied.');
}

// ── Load Composer autoloader & Environment Variables ────────────────────────
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// ── MongoDB connection details (loaded from .env) ─────────────────────────
// Set MONGO_URI and MONGO_DB in your .env file (see .env.example).
$mongoUri = $_ENV['MONGO_URI'] ?? null;
if (!$mongoUri) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'MONGO_URI is not configured. Please set up your .env file.']);
    exit;
}
define('MONGO_URI', $mongoUri);
define('MONGO_DB',  $_ENV['MONGO_DB']  ?? 'hospital_management');

/**
 * Returns a MongoDB\Database instance (singleton per request).
 *
 * The typeMap converts all BSON documents into PHP arrays so you can
 * use standard array access throughout the codebase.
 */
function getDB(): MongoDB\Database
{
    static $db = null;
    if ($db === null) {
        try {
            $client = new MongoDB\Client(MONGO_URI, [], [
                'typeMap' => [
                    'root'     => 'array',
                    'document' => 'array',
                    'array'    => 'array',
                ],
            ]);
            $db = $client->selectDatabase(MONGO_DB);
        } catch (Exception $e) {
            // Return a JSON error so AJAX callers can handle it gracefully
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ]);
            exit;
        }
    }
    return $db;
}

// ── HTTP helpers ─────────────────────────────────────────────────────────────

/**
 * Send a JSON response and terminate.
 *
 * @param array $data HTTP body as an associative array.
 * @param int   $code HTTP status code (default 200).
 */
function json_out(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Perform an HTTP redirect and terminate.
 *
 * @param string $url  Relative or absolute URL.
 * @param int    $code HTTP redirect status.
 */
function redirect(string $url, int $code = 302): void
{
    http_response_code($code);
    header("Location: $url");
    exit;
}

/**
 * Sanitise a string input: strip tags and trim whitespace.
 *
 * @param  string $value Raw input.
 * @return string        Cleaned value.
 */
function sanitise(string $value): string
{
    return strip_tags(trim($value));
}
