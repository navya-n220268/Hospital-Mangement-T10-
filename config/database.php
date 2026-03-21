<?php
/**
 * MediVita Hospital Management System
 * Database Connection Configuration
 */

require_once __DIR__ . '/../vendor/autoload.php';

// MongoDB Atlas Connection URI
define('DB_URI', 'mongodb+srv://medivita:Dhoni%249977@medivita.hqmudoe.mongodb.net/?appName=medivita');
define('DB_NAME', 'hospital_management');

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
