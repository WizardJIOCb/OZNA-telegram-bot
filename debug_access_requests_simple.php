<?php

// Simple debug script to test access requests functionality

echo "=== Debug Access Requests (Simple) ===\n\n";

// Check if mbstring is available
echo "1. Checking if mbstring extension is available...\n";
if (function_exists('mb_convert_encoding')) {
    echo "✓ mbstring extension is available\n";
} else {
    echo "✗ mbstring extension is NOT available\n";
}
echo "\n";

// Read the access requests file directly
echo "2. Reading access requests file...\n";
$filePath = __DIR__ . '/data/access_requests.json';
if (file_exists($filePath)) {
    echo "✓ File exists\n";
    $content = file_get_contents($filePath);
    echo "File size: " . strlen($content) . " bytes\n";
    
    // Try to decode JSON
    $requests = json_decode($content, true);
    if ($requests !== null) {
        echo "✓ JSON decoded successfully\n";
        echo "Number of requests: " . count($requests) . "\n";
        
        if (!empty($requests)) {
            echo "\nRequests details:\n";
            foreach ($requests as $index => $request) {
                echo "Request #" . ($index + 1) . ":\n";
                echo "  User ID: " . $request['user_id'] . "\n";
                echo "  User Name: " . $request['user_name'] . "\n";
                echo "  Chat ID: " . $request['chat_id'] . "\n";
                echo "  Timestamp: " . date('Y-m-d H:i:s', $request['timestamp']) . "\n";
                echo "  ---\n";
            }
        }
    } else {
        echo "✗ Failed to decode JSON\n";
        echo "JSON error: " . json_last_error_msg() . "\n";
    }
} else {
    echo "✗ File does not exist\n";
}
echo "\n";

// Test clearing the requests
echo "3. Testing clear functionality...\n";
echo "Creating backup of current file...\n";
if (file_exists($filePath)) {
    copy($filePath, $filePath . '.backup');
    echo "✓ Backup created\n";
    
    echo "Clearing requests (writing empty array)...\n";
    $emptyRequests = [];
    $jsonData = json_encode($emptyRequests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($filePath, $jsonData);
    echo "✓ File cleared\n";
    
    echo "Verifying file is empty...\n";
    $content = file_get_contents($filePath);
    $requests = json_decode($content, true);
    echo "Requests after clearing: " . count($requests) . "\n";
    
    echo "Restoring backup...\n";
    rename($filePath . '.backup', $filePath);
    echo "✓ Backup restored\n";
} else {
    echo "Cannot test clear functionality - file does not exist\n";
}

echo "\n=== Debug Complete ===\n";