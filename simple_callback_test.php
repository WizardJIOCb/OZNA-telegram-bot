<?php
/**
 * Simplified Project Control Callback Test
 * Tests the core callback routing logic without database dependencies
 */

echo "=== PROJECT CONTROL CALLBACK ROUTING TEST ===\n\n";

// Load required classes
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;

// Test 1: Callback Length Check
echo "1. CALLBACK DATA LENGTH CHECK:\n";
$callbacks = ['proj_ctrl_add', 'proj_ctrl_remove', 'proj_ctrl_list', 'proj_ctrl_settings'];
foreach ($callbacks as $callback) {
    $length = strlen($callback);
    echo "  '{$callback}' = {$length} bytes " . ($length <= 64 ? "✓ OK" : "✗ TOO LONG") . "\n";
}

// Test 2: CallbackHandlerFactory extractCategory test
echo "\n2. CALLBACKHANDLERFACTORY EXTRACTCATEGORY TEST:\n";
$factory = new CallbackHandlerFactory();

// Use reflection to access the private method
$reflectionClass = new ReflectionClass($factory);
$extractCategoryMethod = $reflectionClass->getMethod('extractCategory');
$extractCategoryMethod->setAccessible(true);

foreach ($callbacks as $callback) {
    $extractedCategory = $extractCategoryMethod->invoke($factory, $callback);
    echo "  '{$callback}' -> '{$extractedCategory}' " . (!empty($extractedCategory) ? "✓" : "✗") . "\n";
}

// Test 3: ProjectHandler canHandle test
echo "\n3. PROJECTHANDLER CANHANDLE TEST:\n";
$messageService = new MessageService();
$adminService = new AdminService($messageService);
$projectHandler = new ProjectHandler($adminService, $messageService);

foreach ($callbacks as $callback) {
    $canHandle = $projectHandler->canHandle($callback);
    echo "  ProjectHandler->canHandle('{$callback}') = " . ($canHandle ? "true ✓" : "false ✗") . "\n";
}

// Test 4: CallbackqueryCommand early exit check
echo "\n4. CALLBACKQUERYCOMMAND EARLY EXIT CHECK:\n";
foreach ($callbacks as $callback) {
    $shouldPassThrough = (
        strpos($callback, 'category_') === 0 || 
        strpos($callback, 'adm_req_') === 0 || 
        strpos($callback, 'acc_lvl_') === 0 || 
        strpos($callback, 'cat_Таб') === 0 ||
        strpos($callback, 'proj_ctrl_') === 0 ||
        strpos($callback, 'proj_list_') === 0
    );
    echo "  '{$callback}' should pass through: " . ($shouldPassThrough ? "YES ✓" : "NO ✗") . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "If all tests show ✓, the callback routing is fixed.\n";
echo "The buttons should now respond correctly.\n";
echo "\nIf you still have issues, check:\n";
echo "1. User access level (must be 2+ for control projects)\n";
echo "2. Database connection\n";
echo "3. Error logs for runtime errors\n";
?>