<?php
/**
 * Test script for short callback processing
 * Tests the processing of short callback formats for electrical equipment activities
 */

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

echo "Testing short callback processing...\n\n";

// Create service instances
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector(), $tabelEntry, $adminService);

// Test the extractCategoryFromCallback method
echo "1. Testing extractCategoryFromCallback method:\n";

// Test case 1: Works by schedule short callback
$shortCallback1 = 'cat_ТабЭлОб_РабПоГр';
echo "   Short callback: {$shortCallback1}\n";
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('extractCategoryFromCallback');
$method->setAccessible(true);
$extracted1 = $method->invoke($tabelHandler, $shortCallback1);
echo "   Extracted category: {$extracted1}\n";

if ($extracted1 === 'Табель_РаботыПоГрафику_Электрооборудование') {
    echo "   ✅ Correctly mapped short callback to full category\n";
} else {
    echo "   ❌ Incorrect mapping\n";
}

// Test case 2: ID formation short callback
$shortCallback2 = 'cat_ТабЭлОб_ФормИД';
echo "\n   Short callback: {$shortCallback2}\n";
$extracted2 = $method->invoke($tabelHandler, $shortCallback2);
echo "   Extracted category: {$extracted2}\n";

if ($extracted2 === 'Табель_ФормированиеИД_Электрооборудование') {
    echo "   ✅ Correctly mapped short callback to full category\n";
} else {
    echo "   ❌ Incorrect mapping\n";
}

// Test case 3: Additional works short callback
$shortCallback3 = 'cat_ТабЭлОб_ДопРаб';
echo "\n   Short callback: {$shortCallback3}\n";
$extracted3 = $method->invoke($tabelHandler, $shortCallback3);
echo "   Extracted category: {$extracted3}\n";

if ($extracted3 === 'Табель_ДопРаб_ЭлектрОб') {
    echo "   ✅ Correctly mapped short callback to full category\n";
} else {
    echo "   ❌ Incorrect mapping\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed!\n";
echo str_repeat("=", 50) . "\n";