<?php
/**
 * Check if ОИ651 was added multiple times to database
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== Checking for ОИ651 Duplicates ===\n\n";

$dbService = new DatabaseService();

// Use reflection to access private connection
$reflection = new \ReflectionClass($dbService);
$method = $reflection->getMethod('getConnection');
$method->setAccessible(true);
$conn = $method->invoke($dbService);

// Get ALL entries for ОИ651 (including inactive)
$query = "SELECT id, project_code, current_name, status, is_active, added_by, added_at, moderated_at 
          FROM project_moderation 
          WHERE UPPER(current_name) = 'ОИ651'
          ORDER BY added_at DESC";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " entries for ОИ651:\n\n";
    while ($row = $result->fetch_assoc()) {
        echo "Entry #{$row['id']}:\n";
        echo "  Name: {$row['current_name']}\n";
        echo "  Status: {$row['status']}\n";
        echo "  Active: " . ($row['is_active'] ? 'YES' : 'NO') . "\n";
        echo "  Added by: User ID {$row['added_by']}\n";
        echo "  Added at: {$row['added_at']}\n";
        echo "  Moderated at: " . ($row['moderated_at'] ?? 'Not yet') . "\n";
        echo "  ---\n\n";
    }
} else {
    echo "No entries found for ОИ651\n";
}

echo "\n=== Checking Last 10 Projects Added ===\n\n";

$query = "SELECT id, current_name, status, is_active, added_by, added_at 
          FROM project_moderation 
          ORDER BY added_at DESC 
          LIMIT 10";

$result = $conn->query($query);

if ($result) {
    echo "Last 10 projects added:\n\n";
    while ($row = $result->fetch_assoc()) {
        echo "  ID:{$row['id']} | '{$row['current_name']}' | status:{$row['status']} | active:{$row['is_active']} | user:{$row['added_by']} | {$row['added_at']}\n";
    }
}

echo "\n=== Test Complete ===\n";
