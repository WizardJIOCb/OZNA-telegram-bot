<?php
/**
 * Debug script to check specific projects: ТЕСТ1234 and 📁 Проекты
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Debug Specific Projects ===\n\n";

$problemProjects = ['ТЕСТ1234', '📁 Проекты'];

try {
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    foreach ($problemProjects as $projectCode) {
        echo "Checking project: '{$projectCode}'\n";
        echo str_repeat('-', 60) . "\n";
        
        // 1. Check in database
        echo "1. Database check:\n";
        $query = "SELECT * FROM project_moderation 
                  WHERE current_name = ? OR UPPER(current_name) = ?";
        $stmt = $databaseService->getConnection()->prepare($query);
        $upperCode = strtoupper($projectCode);
        $stmt->bind_param("ss", $projectCode, $upperCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $found = false;
        while ($row = $result->fetch_assoc()) {
            $found = true;
            echo "   ✅ Found in database:\n";
            echo "      ID: {$row['id']}\n";
            echo "      current_name: '{$row['current_name']}'\n";
            echo "      status: {$row['status']}\n";
            echo "      is_active: {$row['is_active']}\n";
            echo "      added_by: {$row['added_by']}\n";
            echo "      added_at: {$row['added_at']}\n";
            if ($row['moderation_comment']) {
                echo "      comment: {$row['moderation_comment']}\n";
            }
        }
        
        if (!$found) {
            echo "   ❌ Not found in database\n";
        }
        
        // 2. Check in JSON file
        echo "\n2. JSON file check:\n";
        $existsInJson = $adminService->projectExists($projectCode);
        if ($existsInJson) {
            echo "   ✅ Found in projects.json\n";
        } else {
            echo "   ❌ Not found in projects.json\n";
        }
        
        // 3. Check projectExists() method
        echo "\n3. DatabaseService->projectExists() check:\n";
        $existsInDb = $databaseService->projectExists($projectCode);
        echo "   Result: " . ($existsInDb ? "TRUE" : "FALSE") . "\n";
        
        // 4. Test callback data length
        echo "\n4. Callback data length check:\n";
        $callbackData = "category_Адм_УдПр_{$projectCode}";
        $byteLength = strlen($callbackData);
        echo "   Callback: '{$callbackData}'\n";
        echo "   Length: {$byteLength} bytes\n";
        if ($byteLength > 64) {
            echo "   ⚠️  WARNING: Exceeds Telegram's 64-byte limit!\n";
        } else {
            echo "   ✅ Within Telegram's 64-byte limit\n";
        }
        
        // 5. Test extraction
        echo "\n5. Project code extraction test:\n";
        $extracted = str_replace('Адм_УдПр_', '', "Адм_УдПр_{$projectCode}");
        echo "   Extracted: '{$extracted}'\n";
        echo "   Match: " . ($extracted === $projectCode ? "YES" : "NO") . "\n";
        
        // 6. Test deletion query
        echo "\n6. Deletion query test (DRY RUN):\n";
        $upperProjectCode = strtoupper(trim($projectCode));
        $query = "SELECT * FROM project_moderation 
                  WHERE UPPER(current_name) = ? AND is_active = 1";
        $stmt = $databaseService->getConnection()->prepare($query);
        $stmt->bind_param("s", $upperProjectCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $count = $result->num_rows;
        echo "   Projects that would be affected: {$count}\n";
        while ($row = $result->fetch_assoc()) {
            echo "      - ID {$row['id']}: '{$row['current_name']}' (status: {$row['status']})\n";
        }
        
        echo "\n" . str_repeat('=', 60) . "\n\n";
    }
    
    // 7. Show all active projects in database
    echo "All active projects in database:\n";
    echo str_repeat('-', 60) . "\n";
    $query = "SELECT current_name, status, is_active, added_at 
              FROM project_moderation 
              WHERE is_active = 1
              ORDER BY current_name ASC";
    $result = $databaseService->getConnection()->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $indicator = ($row['status'] === 'rejected') ? '⚠️' : '✅';
        echo "{$indicator} '{$row['current_name']}' - status: {$row['status']}, is_active: {$row['is_active']}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
