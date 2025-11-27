<?php
/**
 * Test PersonnelHandler Instantiation
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\PersonnelHandler;

echo "=== TESTING PERSONNEL HANDLER INSTANTIATION ===\n\n";

try {
    echo "1. Loading required classes...\n";
    
    echo "2. Creating MessageService...\n";
    $messageService = new MessageService();
    echo "   ✓ MessageService created\n\n";
    
    echo "3. Creating AdminService...\n";
    $adminService = new AdminService($messageService);
    echo "   ✓ AdminService created\n\n";
    
    echo "4. Creating PersonnelHandler...\n";
    $personnelHandler = new PersonnelHandler($adminService, $messageService);
    echo "   ✓ PersonnelHandler created successfully!\n\n";
    
    echo "5. Testing canHandle method...\n";
    $canHandle1 = $personnelHandler->canHandle('personnel_main');
    $canHandle2 = $personnelHandler->canHandle('personnel_fn_mobile');
    $canHandle3 = $personnelHandler->canHandle('acc_lvl_users');
    
    echo "   personnel_main: " . ($canHandle1 ? "TRUE" : "FALSE") . "\n";
    echo "   personnel_fn_mobile: " . ($canHandle2 ? "TRUE" : "FALSE") . "\n";
    echo "   acc_lvl_users: " . ($canHandle3 ? "TRUE" : "FALSE") . "\n\n";
    
    echo "6. Testing getPriority method...\n";
    $priority = $personnelHandler->getPriority();
    echo "   Priority: {$priority}\n\n";
    
    echo "=== ALL TESTS PASSED ===\n";
    
} catch (\Error $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (\Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
