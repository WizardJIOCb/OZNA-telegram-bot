<?php

// Simple test script to verify admin fixes

echo "=== Testing Admin Fixes ===\n\n";

// Test 1: Check current access requests
echo "1. Checking current access requests file...\n";
$filePath = __DIR__ . '/data/access_requests_db.json';

if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    $requests = json_decode($content, true);
    
    echo "   File exists: YES\n";
    echo "   File size: " . strlen($content) . " bytes\n";
    echo "   Number of requests: " . count($requests) . "\n";
    
    if (!empty($requests)) {
        foreach ($requests as $index => $request) {
            echo "   Request $index: User ID " . $request['user_id'] . ", Status: " . ($request['status'] ?? 'unknown') . "\n";
        }
    }
} else {
    echo "   File exists: NO\n";
}

echo "\n";

// Test 2: Test clearing all access requests by writing empty array
echo "2. Testing clearing all access requests...\n";
echo "   Writing empty array to file...\n";

$emptyRequests = [];
$jsonData = json_encode($emptyRequests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($filePath, $jsonData);

// Check requests after clearing
$contentAfter = file_get_contents($filePath);
$requestsAfter = json_decode($contentAfter, true);
echo "   Number of requests after clearing: " . count($requestsAfter) . "\n";

echo "\n=== Test Complete ===\n";