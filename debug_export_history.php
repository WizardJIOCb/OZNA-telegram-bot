<?php
require_once 'vendor/autoload.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/ExportSecurityService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ExportSecurityService;

$db = new DatabaseService();
$exportService = new ExportSecurityService($db);

// Get logs
$logs = $db->getAllExportLogs(10, 0);

echo "Found " . count($logs) . " logs\n\n";

foreach ($logs as $log) {
    echo "Log ID: {$log['id']}\n";
    echo "User ID: {$log['user_id']}\n";
    
    // Get user data
    $userData = $db->getUserData($log['user_id']);
    echo "User data: " . json_encode($userData, JSON_UNESCAPED_UNICODE) . "\n";
    echo "FIO from data: " . ($userData['fio'] ?? 'EMPTY') . "\n";
    echo "---\n";
}

// Now test the formatter
echo "\n=== Testing formatter ===\n";
$formatted = $exportService->formatExportHistoryWithUsers($logs);
echo $formatted;
