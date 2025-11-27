<?php
/**
 * Simple test script to verify file-based access requests functionality
 */

// Test adding an access request to the JSON file directly
$accessRequestsFile = __DIR__ . '/data/access_requests_db.json';

// Create test data
$testRequest = [
    [
        'id' => 1,
        'user_id' => 123456,
        'user_name' => 'Test User',
        'user_telegram_name' => 'test_user',
        'user_fio' => 'Test User Full Name',
        'chat_id' => 123456,
        'timestamp' => time(),
        'status' => 'pending'
    ]
];

// Save test data to file
file_put_contents($accessRequestsFile, json_encode($testRequest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Test access request saved to file.\n";

// Read test data from file
$requests = [];
if (file_exists($accessRequestsFile)) {
    $jsonData = file_get_contents($accessRequestsFile);
    if ($jsonData !== false) {
        $requests = json_decode($jsonData, true);
        if (is_array($requests)) {
            echo "Successfully read " . count($requests) . " access requests from file.\n";
            print_r($requests);
        }
    }
}

echo "File-based access requests test completed.\n";