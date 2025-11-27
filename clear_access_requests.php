<?php

/**
 * Script to clear all admin access requests
 */

echo "Clearing admin access requests...\n";

// Path to the access requests file
$accessRequestsFile = __DIR__ . '/data/access_requests.json';

// Create an empty array for access requests
$emptyRequests = [];

// Save the empty array to the file
if (file_put_contents($accessRequestsFile, json_encode($emptyRequests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo "✅ Successfully cleared all access requests\n";
    echo "The file now contains: []\n";
} else {
    echo "❌ Failed to clear access requests\n";
}

// Also display the current content of the file
if (file_exists($accessRequestsFile)) {
    $content = file_get_contents($accessRequestsFile);
    echo "Current file content:\n";
    echo $content . "\n";
} else {
    echo "File does not exist\n";
}

echo "✅ Done!\n";