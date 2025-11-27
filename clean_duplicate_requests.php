<?php

// Script to clean duplicate access requests

echo "=== Clean Duplicate Access Requests ===\n\n";

// Read the access requests file
$filePath = __DIR__ . '/data/access_requests.json';
if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    $requests = json_decode($content, true);
    
    if (!empty($requests)) {
        echo "Found " . count($requests) . " requests\n";
        
        // Remove duplicates by user_id
        $uniqueRequests = [];
        $seenUserIds = [];
        
        foreach ($requests as $request) {
            $userId = $request['user_id'];
            
            if (!in_array($userId, $seenUserIds)) {
                $uniqueRequests[] = $request;
                $seenUserIds[] = $userId;
            }
        }
        
        echo "After removing duplicates: " . count($uniqueRequests) . " requests remain\n";
        
        // Save the unique requests back to the file
        $jsonData = json_encode($uniqueRequests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filePath, $jsonData);
        
        echo "✓ Duplicate requests removed\n";
        
        if (count($uniqueRequests) > 0) {
            echo "\nRemaining requests:\n";
            foreach ($uniqueRequests as $index => $request) {
                echo "Request #" . ($index + 1) . ":\n";
                echo "  User ID: " . $request['user_id'] . "\n";
                echo "  User Name: " . $request['user_name'] . "\n";
                echo "  Chat ID: " . $request['chat_id'] . "\n";
                echo "  Timestamp: " . date('Y-m-d H:i:s', $request['timestamp']) . "\n";
                echo "  ---\n";
            }
        }
    } else {
        echo "No requests found\n";
    }
} else {
    echo "access_requests.json file not found\n";
}

echo "\n=== Cleanup Complete ===\n";