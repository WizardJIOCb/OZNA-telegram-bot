<?php
/**
 * Direct test of file-based access requests functionality
 */

echo "=== Direct File Access Requests Test ===\n\n";

// Test reading the access requests file directly
$accessRequestsFile = __DIR__ . '/data/access_requests.json';

echo "Checking access requests file: {$accessRequestsFile}\n";

if (file_exists($accessRequestsFile)) {
    echo "File exists: YES\n";
    
    $jsonData = file_get_contents($accessRequestsFile);
    echo "File size: " . strlen($jsonData) . " bytes\n";
    
    if ($jsonData !== false) {
        $requests = json_decode($jsonData, true);
        if (is_array($requests)) {
            echo "JSON decoded successfully\n";
            echo "Total requests found: " . count($requests) . "\n";
            
            if (!empty($requests)) {
                echo "\nDisplaying first few requests:\n";
                $count = 0;
                foreach ($requests as $request) {
                    if ($count >= 3) break; // Show only first 3
                    echo "  User ID: " . $request['user_id'] . "\n";
                    echo "  User Name: " . $request['user_name'] . "\n";
                    echo "  Timestamp: " . date('Y-m-d H:i:s', $request['timestamp']) . "\n\n";
                    $count++;
                }
                
                if (count($requests) > 3) {
                    echo "  ... and " . (count($requests) - 3) . " more requests\n";
                }
            }
        } else {
            echo "Failed to decode JSON\n";
            echo "JSON error: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "Failed to read file content\n";
    }
} else {
    echo "File exists: NO\n";
}

echo "\nTest completed.\n";