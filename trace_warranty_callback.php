<?php
// Comprehensive warranty callback tracing script
echo "=== COMPREHENSIVE WARRANTY CALLBACK TRACING ===\n\n";

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Create actual services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

echo "1. TESTING WARRANTY EQUIPMENT KEYBOARD CREATION:\n";
echo "================================================\n";

$keyboard = $messageService->createWarrantyEquipmentKeyboard();
echo "Warranty equipment keyboard created with " . count($keyboard) . " rows\n";

foreach ($keyboard as $index => $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && isset($button['callback_data'])) {
            echo "Button: '{$button['text']}' → Callback: '{$button['callback_data']}'\n";
        }
    }
}

echo "\n2. TESTING TABELHANDLER CANHANDLE METHOD:\n";
echo "==========================================\n";

$testCallbacks = [
    'cat_ТабГарантии',
    'category_Табель_РабГарантии_ЭлектрОб',
    'category_Табель_РабГарантии_НасОб'
];

foreach ($testCallbacks as $callback) {
    echo "Testing callback: {$callback}\n";
    $canHandle = $tabelHandler->canHandle($callback);
    echo "TabelHandler canHandle: " . ($canHandle ? "✅ YES" : "❌ NO") . "\n\n";
}

echo "3. TESTING CATEGORY EXTRACTION:\n";
echo "===============================\n";

// Use reflection to access the private extractCategoryFromCallback method
$reflection = new ReflectionClass($tabelHandler);
$extractMethod = $reflection->getMethod('extractCategoryFromCallback');
$extractMethod->setAccessible(true);

foreach ($testCallbacks as $callback) {
    echo "Original callback: {$callback}\n";
    $extractedCategory = $extractMethod->invoke($tabelHandler, $callback);
    echo "Extracted category: {$extractedCategory}\n";
    
    // Test the routing condition
    if (str_starts_with($extractedCategory, 'Табель_РабГарантии_')) {
        echo "Routing condition: ✅ MATCHES (str_starts_with 'Табель_РабГарантии_')\n";
        $equipmentType = str_replace('Табель_РабГарантии_', '', $extractedCategory);
        echo "Equipment type: {$equipmentType}\n";
        $activityType = 'РабГарантии_' . $equipmentType;
        echo "Activity type: {$activityType}\n";
    } elseif ($extractedCategory === 'cat_ТабГарантии') {
        echo "Routing condition: ✅ MATCHES (main warranty menu)\n";
    } else {
        echo "Routing condition: ❌ NO MATCH\n";
    }
    echo "\n";
}

echo "4. TESTING ACTIVITY TYPE MAPPINGS:\n";
echo "==================================\n";

// Test if the activity types exist in TabelEntry
$testActivityTypes = [
    'РабГарантии_ЭлектрОб',
    'РабГарантии_НасОб',
    'РабГарантии_СрИзм'
];

foreach ($testActivityTypes as $activityType) {
    echo "Testing activity type: {$activityType}\n";
    try {
        $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, 'Test Project');
        echo "Confirmation message: {$confirmationMessage}\n";
        echo "Status: ✅ ACTIVITY TYPE EXISTS\n";
    } catch (Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        echo "Status: ❌ ACTIVITY TYPE NOT FOUND\n";
    }
    echo "\n";
}

echo "5. SIMULATING COMPLETE CALLBACK FLOW:\n";
echo "====================================\n";

$electricalCallback = 'category_Табель_РабГарантии_ЭлектрОб';
echo "Simulating callback: {$electricalCallback}\n\n";

// Step 1: canHandle
echo "Step 1: TabelHandler->canHandle()\n";
$canHandle = $tabelHandler->canHandle($electricalCallback);
echo "Result: " . ($canHandle ? "✅ CAN HANDLE" : "❌ CANNOT HANDLE") . "\n\n";

if ($canHandle) {
    // Step 2: Category extraction
    echo "Step 2: extractCategoryFromCallback()\n";
    $category = $extractMethod->invoke($tabelHandler, $electricalCallback);
    echo "Extracted: {$category}\n\n";
    
    // Step 3: Routing condition check
    echo "Step 3: Routing condition check\n";
    if (str_starts_with($category, 'Табель_РабГарантии_')) {
        echo "Condition: str_starts_with('{$category}', 'Табель_РабГарантии_') = ✅ TRUE\n";
        
        // Step 4: Equipment type extraction
        echo "Step 4: Equipment type extraction\n";
        $equipmentType = str_replace('Табель_РабГарантии_', '', $category);
        echo "Equipment type: {$equipmentType}\n";
        
        // Step 5: Activity type creation
        echo "Step 5: Activity type creation\n";
        $activityType = 'РабГарантии_' . $equipmentType;
        echo "Activity type: {$activityType}\n";
        
        // Step 6: Confirmation message generation
        echo "Step 6: Confirmation message generation\n";
        try {
            $confirmationText = $tabelEntry->getConfirmationMessage($activityType, 'Test Project');
            echo "Confirmation: {$confirmationText}\n";
            echo "Status: ✅ SUCCESS! Message should be shown to user\n";
        } catch (Exception $e) {
            echo "Error: {$e->getMessage()}\n";
            echo "Status: ❌ FAILED! This is why the button doesn't work\n";
        }
    } else {
        echo "Condition: str_starts_with('{$category}', 'Табель_РабГарантии_') = ❌ FALSE\n";
        echo "Status: ❌ ROUTING CONDITION FAILED\n";
    }
} else {
    echo "Status: ❌ TABELHANDLER CANNOT HANDLE THE CALLBACK\n";
}

echo "\n6. CONCLUSION:\n";
echo "=============\n";
echo "This diagnostic will show exactly where the warranty equipment callback processing fails.\n";
echo "Look for any ❌ status messages above to identify the root cause.\n";
?>