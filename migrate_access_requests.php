<?php
/**
 * Migration script to move access requests from old format to new format
 */

$oldFile = __DIR__ . '/data/access_requests.json';
$newFile = __DIR__ . '/data/access_requests_db.json';

echo "Migrating access requests from old format to new format...\n";

// Check if old file exists
if (!file_exists($oldFile)) {
    echo "Old access requests file not found. Nothing to migrate.\n";
    exit(0);
}

// Read old file
$oldData = file_get_contents($oldFile);
if ($oldData === false) {
    echo "Failed to read old access requests file.\n";
    exit(1);
}

$oldRequests = json_decode($oldData, true);
if (!is_array($oldRequests)) {
    echo "Invalid JSON data in old access requests file.\n";
    exit(1);
}

echo "Found " . count($oldRequests) . " access requests to migrate.\n";

// Transform old requests to new format
$newRequests = [];
$requestId = 1;

foreach ($oldRequests as $oldRequest) {
    $newRequest = [
        'id' => $requestId++,
        'user_id' => $oldRequest['user_id'] ?? 0,
        'user_name' => $oldRequest['user_name'] ?? 'Unknown User',
        'user_telegram_name' => $oldRequest['user_telegram_name'] ?? '',
        'user_fio' => $oldRequest['user_fio'] ?? '',
        'chat_id' => $oldRequest['chat_id'] ?? 0,
        'timestamp' => $oldRequest['timestamp'] ?? time(),
        'status' => 'pending'
    ];
    
    $newRequests[] = $newRequest;
}

// Save new requests to file
$result = file_put_contents($newFile, json_encode($newRequests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($result === false) {
    echo "Failed to write new access requests file.\n";
    exit(1);
}

echo "Successfully migrated " . count($newRequests) . " access requests to new format.\n";
echo "New file saved to: $newFile\n";

// Optionally, backup the original file
$backupFile = $oldFile . '.backup';
if (copy($oldFile, $backupFile)) {
    echo "Original file backed up to: $backupFile\n";
    // Uncomment the next line if you want to remove the original file after migration
    // unlink($oldFile);
    // echo "Original file removed.\n";
} else {
    echo "Warning: Failed to backup original file.\n";
}

echo "Migration completed successfully.\n";