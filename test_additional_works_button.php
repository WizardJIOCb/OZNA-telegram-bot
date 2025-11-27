<?php
/**
 * Test script for "Дополнительные работы" button functionality
 */

// Include necessary files
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Testing 'Дополнительные работы' button functionality...\n\n";

// Test 1: Check if the button exists in the electrical equipment keyboard
echo "Test 1: Checking electrical equipment keyboard...\n";
$messageService = new MessageService();
$keyboard = $messageService->createElectricalEquipmentKeyboard();

$buttonFound = false;
foreach ($keyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && $button['text'] === '🛠️ Дополнительные работы') {
            $buttonFound = true;
            echo "  ✓ Button found: " . $button['text'] . "\n";
            echo "  ✓ Callback data: " . $button['callback_data'] . "\n";
            break 2;
        }
    }
}

if (!$buttonFound) {
    echo "  ✗ Button not found in electrical equipment keyboard\n";
}

echo "\nTest 2: Checking TabelHandler activity types...\n";
// Create a mock database service
class MockDatabaseService {
    public function getUserData($userId) {
        return ['fio' => 'Test User'];
    }
}

// Create a mock project selector
class MockProjectSelector {
    public function isDateSelection($category) {
        return false;
    }
    
    public function isProjectSelection($category) {
        return false;
    }
    
    public function extractProjectCode($category) {
        return 'TEST';
    }
}

// Create a mock admin service
class MockAdminService {
    public function getUserAccessLevel($userId) {
        return 1;
    }
}

// Create instances of the services
$databaseService = new MockDatabaseService();
$projectSelector = new MockProjectSelector();
$tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService, $messageService);
$adminService = new MockAdminService();

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Check if 'ДопРаб_ЭлектрОб' is in the activity types
$reflection = new ReflectionClass($tabelHandler);
$activityTypesProperty = $reflection->getProperty('activityTypes');
$activityTypesProperty->setAccessible(true);
$activityTypes = $activityTypesProperty->getValue($tabelHandler);

if (in_array('ДопРаб_ЭлектрОб', $activityTypes)) {
    echo "  ✓ 'ДопРаб_ЭлектрОб' found in activity types\n";
} else {
    echo "  ✗ 'ДопРаб_ЭлектрОб' not found in activity types\n";
}

echo "\nTest 3: Checking callback mapping...\n";
// Test callback mapping
$callbackData = 'cat_ТабЭлОб_ДопРаб';
$method = new ReflectionMethod($tabelHandler, 'extractCategoryFromCallback');
$method->setAccessible(true);
$extractedCategory = $method->invoke($tabelHandler, $callbackData);

echo "  Input callback: $callbackData\n";
echo "  Extracted category: $extractedCategory\n";

if ($extractedCategory === 'Табель_ДопРаб_ЭлектрОб') {
    echo "  ✓ Callback mapping is correct\n";
} else {
    echo "  ✗ Callback mapping is incorrect\n";
}

echo "\nTest 4: Checking TabelEntry activity types...\n";
// Check if 'ДопРаб_ЭлектрОб' is in TabelEntry activity types
$reflection = new ReflectionClass($tabelEntry);
$activityTypesProperty = $reflection->getProperty('activityTypes');
$activityTypesProperty->setAccessible(true);
$activityTypes = $activityTypesProperty->getValue($tabelEntry);

if (isset($activityTypes['ДопРаб_ЭлектрОб'])) {
    echo "  ✓ 'ДопРаб_ЭлектрОб' found in TabelEntry activity types\n";
    echo "  ✓ Display text: " . $activityTypes['ДопРаб_ЭлектрОб'] . "\n";
} else {
    echo "  ✗ 'ДопРаб_ЭлектрОб' not found in TabelEntry activity types\n";
}

echo "\nTest completed.\n";