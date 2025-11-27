<?php

// Script to fix the access requests file

echo "Fixing access requests file...\n";

$filePath = __DIR__ . '/data/access_requests.json';

// Check if file exists
if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    echo "Current file content: " . $content . "\n";
    echo "Current file content (hex): " . bin2hex($content) . "\n";
    
    // Check if content is valid JSON
    $data = json_decode($content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "File contains valid JSON\n";
    } else {
        echo "File contains invalid JSON: " . json_last_error_msg() . "\n";
        echo "Fixing file...\n";
        
        // Create empty array and save it
        $emptyArray = [];
        $jsonData = json_encode($emptyArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filePath, $jsonData);
        echo "File fixed with empty array\n";
    }
} else {
    echo "File does not exist, creating it...\n";
    $emptyArray = [];
    $jsonData = json_encode($emptyArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($filePath, $jsonData);
    echo "File created with empty array\n";
}

echo "Done.\n";