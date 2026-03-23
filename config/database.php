<?php
/**
 * MediVita Hospital Management System
 * Database Connection Configuration
 *
 * All credentials are loaded from the .env file.
 * Copy .env.example to .env and fill in your values.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// MongoDB connection details — set these in your .env file
$mongoUri = $_ENV['MONGO_URI'] ?? null;
$mongoDb  = $_ENV['MONGO_DB']  ?? 'hospital_management';

if (!$mongoUri) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'MONGO_URI is not set. Please configure your .env file.']));
}

define('DB_URI',  $mongoUri);
define('DB_NAME', $mongoDb);

/**
 * Returns a MongoDB\Database instance
 */
function getDatabaseConnection()
{
    static $db = null;
    if ($db === null) {
        try {
            $client = new MongoDB\Client(DB_URI, [], [
                'typeMap' => [
                    'root'     => 'array',
                    'document' => 'array',
                    'array'    => 'array',
                ],
            ]);
            $db = $client->selectDatabase(DB_NAME);
        } catch (Exception $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database connection failed']));
        }
    }
    return $db;
}

// For compatibility with scripts using getDB()
if (!function_exists('getDB')) {
    function getDB() {
        return getDatabaseConnection();
    }
}
