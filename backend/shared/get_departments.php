<?php
/**
 * MediVita Hospital Management System
 * ─────────────────────────────────────
 * backend/shared/get_departments.php
 *
 * Returns a distinct list of departments from the doctors collection.
 * Following the requested format: { success: true, departments: [ { "name": "..." }, ... ] }
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

try {
    $db = getDB();

    // Aggregate distinct departments from 'department' field
    // Note: PHP arrays cannot have duplicate keys, so we use $and instead
    // of ['$ne' => null, '$ne' => ''] which would silently drop one key.
    $pipeline = [
        [
            '$group' => [
                '_id' => '$department'
            ]
        ],
        [
            '$match' => [
                '$and' => [
                    ['_id' => ['$ne' => null]],
                    ['_id' => ['$ne' => '']],
                ]
            ]
        ],
        [
            '$sort' => ['_id' => 1]
        ]
    ];

    $cursor = $db->doctors->aggregate($pipeline);
    $departments = [];
    foreach ($cursor as $doc) {
        $name = (string)$doc['_id'];
        if (!empty($name)) {
            $departments[] = ['name' => $name];
        }
    }

    // Fallback if no departments found in 'department' field, try 'specialization'
    if (empty($departments)) {
        $pipeline2 = [
            [
                '$group' => [
                    '_id' => '$specialization'
                ]
            ],
            [
                '$match' => [
                    '$and' => [
                        ['_id' => ['$ne' => null]],
                        ['_id' => ['$ne' => '']],
                    ]
                ]
            ],
            [
                '$sort' => ['_id' => 1]
            ]
        ];
        $cursor2 = $db->doctors->aggregate($pipeline2);
        foreach ($cursor2 as $doc) {
            $name = (string)$doc['_id'];
            if (!empty($name)) {
                $departments[] = ['name' => $name];
            }
        }
    }

    // Add some default ones if still empty (just in case)
    if (empty($departments)) {
        $departments = [
            ['name' => 'Cardiology'],
            ['name' => 'Neurology'],
            ['name' => 'Pediatrics'],
            ['name' => 'General Medicine']
        ];
    }

    json_out([
        'success'     => true,
        'departments' => $departments,
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Failed to fetch departments: ' . $e->getMessage()], 500);
}
