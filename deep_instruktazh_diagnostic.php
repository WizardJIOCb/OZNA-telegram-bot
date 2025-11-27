<?php

require_once 'config.php';
require_once 'custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

echo "=== DEEP DIAGNOSTIC: INSTRUKTAZH CALLBACK ISSUE ===\n\n";

// Create service instances
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test the problematic callback
$problematicCallback = 'cat_ТабИнстр_yes';
echo "TESTING CALLBACK: '{$problematicCallback}'\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Step 1: Test canHandle method
echo "1. TESTING canHandle() METHOD:\n";
echo "------------------------------\n";
$canHandle = $tabelHandler->canHandle($problematicCallback);
echo "TabelHandler->canHandle('{$problematicCallback}'): " . ($canHandle ? 'TRUE ✅' : 'FALSE ❌') . "\n";

if (!$canHandle) {
    echo "❌ CRITICAL ISSUE: TabelHandler cannot handle this callback!\n";
    echo "   This means the callback is not being routed to TabelHandler at all.\n\n";
    
    // Test individual patterns
    echo "   Testing individual canHandle patterns:\n";
    echo "   - str_starts_with('{$problematicCallback}', 'category_Табель'): " . (str_starts_with($problematicCallback, 'category_Табель') ? 'TRUE' : 'FALSE') . "\n";
    echo "   - str_starts_with('{$problematicCallback}', 'cat_ТабПНРаботы'): " . (str_starts_with($problematicCallback, 'cat_ТабПНРаботы') ? 'TRUE' : 'FALSE') . "\n";
    echo "   - str_starts_with('{$problematicCallback}', 'cat_Таб'): " . (str_starts_with($problematicCallback, 'cat_Таб') ? 'TRUE' : 'FALSE') . "\n";
    echo "   - callback === 'новое_прочее_yes': " . ($problematicCallback === 'новое_прочее_yes' ? 'TRUE' : 'FALSE') . "\n";
    echo "   - callback === 'cat_ТабЭлОб_Проч_yes': " . ($problematicCallback === 'cat_ТабЭлОб_Проч_yes' ? 'TRUE' : 'FALSE') . "\n\n";
}

// Step 2: Test category extraction
echo "2. TESTING extractCategoryFromCallback() METHOD:\n";
echo "------------------------------------------------\n";

$reflection = new ReflectionClass($tabelHandler);
$extractMethod = $reflection->getMethod('extractCategoryFromCallback');
$extractMethod->setAccessible(true);

try {
    $extractedCategory = $extractMethod->invokeArgs($tabelHandler, [$problematicCallback]);
    echo "extractedCategory: '{$extractedCategory}'\n";
} catch (Exception $e) {
    echo "❌ ERROR in extractCategoryFromCallback: " . $e->getMessage() . "\n";
    $extractedCategory = '';
}

// Step 3: Test isConfirmationCallback
echo "\n3. TESTING isConfirmationCallback() METHOD:\n";
echo "-------------------------------------------\n";

$isConfirmationMethod = $reflection->getMethod('isConfirmationCallback');
$isConfirmationMethod->setAccessible(true);

try {
    $isConfirmation = $isConfirmationMethod->invokeArgs($tabelHandler, [$problematicCallback]);
    echo "isConfirmationCallback('{$problematicCallback}'): " . ($isConfirmation ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    
    // Test the pattern manually
    $patternMatch = preg_match('/^cat_(ТабИнстр|ТабДорога|ТабПодгПоезд|ТабСдачаОтч|ТабОхрСигн|ТабОтоплВент|ТабТехОб|ТабМетрол|ТабКомплОпр|ТабРежНал|ТабПНРаботы)_yes$/', $problematicCallback);
    echo "Manual pattern test: " . ($patternMatch ? 'MATCH ✅' : 'NO MATCH ❌') . "\n";
} catch (Exception $e) {
    echo "❌ ERROR in isConfirmationCallback: " . $e->getMessage() . "\n";
}

// Step 4: Test isActivityExecution
echo "\n4. TESTING isActivityExecution() METHOD:\n";
echo "---------------------------------------\n";

$isActivityExecutionMethod = $reflection->getMethod('isActivityExecution');
$isActivityExecutionMethod->setAccessible(true);

try {
    $isActivityExecution = $isActivityExecutionMethod->invokeArgs($tabelHandler, [$problematicCallback]);
    echo "isActivityExecution('{$problematicCallback}'): " . ($isActivityExecution ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    
    // Test the pattern manually
    if (preg_match('/^cat_(.+)_yes$/', $problematicCallback, $matches)) {
        $shortActivityKey = $matches[1];
        echo "Extracted shortActivityKey: '{$shortActivityKey}'\n";
        
        $basicCommissioningActivities = [
            'ТабИнстр' => 'Инструктаж',
            'ТабДорога' => 'Дорога',
        ];
        
        $activityExists = isset($basicCommissioningActivities[$shortActivityKey]);
        echo "Activity mapping exists: " . ($activityExists ? 'YES ✅' : 'NO ❌') . "\n";
        
        if ($activityExists) {
            $activityType = $basicCommissioningActivities[$shortActivityKey];
            echo "Mapped activity type: '{$activityType}'\n";
            
            // Check if it's in the activityTypes array
            $activityTypesProperty = $reflection->getProperty('activityTypes');
            $activityTypesProperty->setAccessible(true);
            $activityTypes = $activityTypesProperty->getValue($tabelHandler);
            
            $inActivityTypes = in_array($activityType, $activityTypes);
            echo "In activityTypes array: " . ($inActivityTypes ? 'YES ✅' : 'NO ❌') . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ ERROR in isActivityExecution: " . $e->getMessage() . "\n";
}

// Step 5: Check actual routing path
echo "\n5. SIMULATING ROUTING PATH:\n";
echo "---------------------------\n";

if ($canHandle) {
    echo "✅ TabelHandler would receive this callback\n";
    
    if (isset($isConfirmation) && $isConfirmation) {
        echo "✅ Would be recognized as confirmation callback\n";
    } else {
        echo "❌ Would NOT be recognized as confirmation callback\n";
    }
    
    if (isset($isActivityExecution) && $isActivityExecution) {
        echo "✅ Would be routed to handleActivityExecution()\n";
    } else {
        echo "❌ Would NOT be routed to handleActivityExecution()\n";
        echo "   This means it would fall through to 'Неизвестная команда табеля'\n";
    }
} else {
    echo "❌ TabelHandler would NOT receive this callback\n";
    echo "   This callback would be ignored or handled by another handler\n";
}

// Step 6: Check the actual code in TabelHandler
echo "\n6. CHECKING ACTUAL CODE IN TABELHANDLER:\n";
echo "----------------------------------------\n";

$tabelHandlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerFile)) {
    $content = file_get_contents($tabelHandlerFile);
    
    // Check if our fixes are actually in the file
    $hasBasicCommissioningActivities = strpos($content, 'basicCommissioningActivities') !== false;
    echo "File contains 'basicCommissioningActivities': " . ($hasBasicCommissioningActivities ? 'YES ✅' : 'NO ❌') . "\n";
    
    $hasInstruktazhMapping = strpos($content, "'ТабИнстр' => 'Инструктаж'") !== false;
    echo "File contains Инструктаж mapping: " . ($hasInstruktazhMapping ? 'YES ✅' : 'NO ❌') . "\n";
    
    $hasConfirmationPattern = strpos($content, 'ТабИнстр|ТабДорога') !== false;
    echo "File contains confirmation pattern: " . ($hasConfirmationPattern ? 'YES ✅' : 'NO ❌') . "\n";
    
    if (!$hasBasicCommissioningActivities || !$hasInstruktazhMapping || !$hasConfirmationPattern) {
        echo "\n❌ CRITICAL: The fixes are not present in the actual file!\n";
        echo "   This suggests the file was not properly saved or deployed.\n";
    }
} else {
    echo "❌ ERROR: Cannot find TabelHandler.php file\n";
}

// Step 7: Check if server restart is needed
echo "\n7. SERVER RESTART STATUS:\n";
echo "-------------------------\n";
echo "⚠️  Handler changes require web server restart to take effect.\n";
echo "   If the fixes are in the file but still not working,\n";
echo "   the server needs to be restarted.\n";

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "Check the results above to identify the exact issue.\n";
echo "Most likely causes:\n";
echo "1. File not properly saved/deployed\n";
echo "2. Web server not restarted after changes\n";
echo "3. Another handler intercepting the callback\n";
echo "4. Pattern matching issue in the code\n";

?>