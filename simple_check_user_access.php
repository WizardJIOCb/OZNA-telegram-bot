<?php

// Simple script to check user access level without requiring MySQL extension

$userId = $argv[1] ?? null;

if (!$userId) {
    echo "Usage: php simple_check_user_access.php <user_id>\n";
    exit(1);
}

// Path to the data file
$dataFile = __DIR__ . '/data/user_data.json';

if (!file_exists($dataFile)) {
    echo "User data file not found: {$dataFile}\n";
    exit(1);
}

$jsonData = file_get_contents($dataFile);
$userData = json_decode($jsonData, true);

if (!isset($userData[$userId])) {
    echo "User {$userId} not found in data file\n";
    exit(1);
}

$userInfo = $userData[$userId];
$accessLevel = $userInfo['access_level'] ?? 0;

echo "User ID: {$userId}\n";
echo "Access Level: {$accessLevel}\n";

// Access level names
$levelNames = [
    0 => "GUEST",
    1 => "USER",
    2 => "SUPERVISOR",
    3 => "ADMIN",
    4 => "SUPER_ADMIN"
];

echo "Access Level Name: " . ($levelNames[$accessLevel] ?? "UNKNOWN") . "\n";

?>