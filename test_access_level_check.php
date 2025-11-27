<?php
// Diagnostic script to check user access level

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Access Level Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .test-result { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Access Level Check</h1>";

try {
    echo "<div class='test-result info'>Initializing services...</div>";
    
    // Initialize services
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Test Super Admin IDs from the code
    $superAdminIds = [230379668, 5261465966];
    
    echo "<div class='test-result info'>Checking Super Admin IDs...</div>";
    
    foreach ($superAdminIds as $userId) {
        $isSuperAdmin = $adminService->isSuperAdmin($userId);
        echo "<div class='test-result " . ($isSuperAdmin ? 'success' : 'error') . "'>User ID {$userId} is Super Admin: " . ($isSuperAdmin ? 'true' : 'false') . "</div>";
        
        $accessLevel = $adminService->getUserAccessLevel($userId);
        $levelName = $adminService->getAccessLevelName($accessLevel);
        echo "<div class='test-result info'>User ID {$userId} access level: {$accessLevel} ({$levelName})</div>";
    }
    
    // Test DatabaseService constants
    echo "<div class='test-result info'>Checking DatabaseService constants...</div>";
    
    $constants = [
        'ACCESS_LEVEL_GUEST' => defined('Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\DatabaseService::ACCESS_LEVEL_GUEST') ? DatabaseService::ACCESS_LEVEL_GUEST : 'Not defined',
        'ACCESS_LEVEL_USER' => defined('Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\DatabaseService::ACCESS_LEVEL_USER') ? DatabaseService::ACCESS_LEVEL_USER : 'Not defined',
        'ACCESS_LEVEL_ADMIN' => defined('Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\DatabaseService::ACCESS_LEVEL_ADMIN') ? DatabaseService::ACCESS_LEVEL_ADMIN : 'Not defined',
        'ACCESS_LEVEL_SUPER_ADMIN' => defined('Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\DatabaseService::ACCESS_LEVEL_SUPER_ADMIN') ? DatabaseService::ACCESS_LEVEL_SUPER_ADMIN : 'Not defined'
    ];
    
    foreach ($constants as $name => $value) {
        echo "<div class='test-result info'>{$name}: {$value}</div>";
    }
    
    echo "<div class='test-result success'>Test completed successfully!</div>";
    
} catch (Exception $e) {
    echo "<div class='test-result error'>Error: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</div>
</body>
</html>";
?>