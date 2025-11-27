<?php
// Read the access requests file
$filePath = __DIR__ . '/data/access_requests.json';
$content = file_get_contents($filePath);
$requests = json_decode($content, true);

if (!is_array($requests)) {
    echo "Error: Could not parse access requests file\n";
    exit(1);
}

echo "Original requests count: " . count($requests) . "\n";

// Remove duplicates by user_id, keeping only the latest entry for each user
$uniqueRequests = [];
$userIds = [];

// Process requests in reverse order to keep the latest entries
foreach (array_reverse($requests) as $request) {
    $userId = $request['user_id'];
    if (!in_array($userId, $userIds)) {
        $userIds[] = $userId;
        $uniqueRequests[] = $request;
    }
}

// Reverse back to maintain chronological order
$uniqueRequests = array_reverse($uniqueRequests);

echo "Unique requests count: " . count($uniqueRequests) . "\n";

// Save the cleaned data
$jsonData = json_encode($uniqueRequests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($filePath, $jsonData);

echo "Duplicate access requests removed successfully.\n";