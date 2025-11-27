<?php

// Test script to add a real access request

// Read the current access requests
$filePath = __DIR__ . '/data/access_requests.json';
$data = file_get_contents($filePath);

// Decode JSON
$requests = json_decode($data, true);

// Clear existing requests
$requests = [];

// Add a new test request with a realistic user ID
$newRequest = [
    "user_id" => 1090118386,
    "user_name" => "Близнецов Денис Дмитриевич",
    "user_telegram_name" => "Blizi",
    "user_fio" => "Близнецов Денис Дмитриевич",
    "chat_id" => "5261465966",
    "timestamp" => 1725120000
];

// Add to requests array
$requests[] = $newRequest;

// Save back to file
$jsonData = json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($filePath, $jsonData);

echo "Added new access request for user: Близнецов Денис Дмитриевич\n";
echo "User ID: 1090118386\n";
echo "File updated successfully.\n";