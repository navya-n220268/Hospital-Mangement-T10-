<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$mongoUri = $_ENV['MONGO_URI'] ?? null;
$mongoDb  = $_ENV['MONGO_DB']  ?? 'hospital_management';

if (!$mongoUri) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'MONGO_URI is not set. Please configure your .env file.']));
}

$client = new MongoDB\Client($mongoUri, [], [
    'typeMap' => [
        'root'     => 'array',
        'document' => 'array',
        'array'    => 'array',
    ],
]);

$db = $client->$mongoDb;
?>
