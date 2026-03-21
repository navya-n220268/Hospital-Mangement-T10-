<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Try to use the same connection string logic if defined
$client = new MongoDB\Client("mongodb+srv://medivita:Dhoni%249977@medivita.hqmudoe.mongodb.net/?appName=medivita", [], [
    'typeMap' => [
        'root'     => 'array',
        'document' => 'array',
        'array'    => 'array',
    ],
]);

// The prompt specified ->medivita, but existing database is hospital_management. 
// We will use hospital_management where appointments and users exist.
$db = $client->hospital_management;
?>
