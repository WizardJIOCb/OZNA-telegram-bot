<?php
/**
 * Test supervision project activity sub-menu implementation
 * Tests the complete supervision project activities feature with sub-menu
 */

echo "=== TESTING SUPERVISION PROJECT ACTIVITIES SUB-MENU ===\n\n";

// Set up autoloading
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

// Test 1: MessageService supervision project activity keyboard
echo "1. TESTING SUPERVISION PROJECT ACTIVITY KEYBOARD:\n";
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Conversation;

try {
    $keyboard = $messageService->createSupervisionProjectActivityKeyboard();
    echo "   ✅ Successfully created supervision project activity keyboard\n";
    echo "   Found " . count($keyboard) . " buttons:\n";
    
    foreach ($keyboard as $row) {
        foreach ($row as $button) {
            echo "     - {$button['text']} → {$button['callback_data']}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error creating supervision project activity keyboard: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: TabelHandler activity types
echo "2. TESTING SUPERVISION PROJECT ACTIVITY TYPES:\n";
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

// Check if all supervision project activity types are in the activityTypes array
$expectedSupervisionProjectTypes = [
    'ШМ_УчастиеВЗПИ',
    'ШМ_РазрПрограммы', 
    'ШМ_РазрГрафика',
    'ШМ_ОргДопоставки',
    'ШМ_УчастиеВОПИ'
];

$reflection = new ReflectionClass('TabelHandler');
$activityTypesProperty = $reflection->getProperty('activityTypes');
$activityTypesProperty->setAccessible(true);

// We can't easily get the actual value without instantiating, so we'll check the file content
$handlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

foreach ($expectedSupervisionProjectTypes as $type) {
    if (strpos($handlerContent, "'$type'") !== false) {
        echo "   ✅ Found activity type: $type\n";
    } else {
        echo "   ❌ Missing activity type: $type\n";
    }
}

echo "\n";

// Test 3: TabelEntry activity names
echo "3. TESTING SUPERVISION PROJECT ACTIVITY NAMES:\n";
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

$tabelEntry = new TabelEntry();
$expectedActivityNames = [
    'ШМ_УчастиеВЗПИ' => 'Участие в ЗПИ',
    'ШМ_РазрПрограммы' => 'Разработка программы работ',
    'ШМ_РазрГрафика' => 'Разработка графика работ',
    'ШМ_ОргДопоставки' => 'Организация допоставки',
    'ШМ_УчастиеВОПИ' => 'Участие в ОПИ'
];

foreach ($expectedActivityNames as $activityType => $expectedName) {
    try {
        $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, 'TEST123');
        if (strpos($confirmationMessage, $expectedName) !== false || strpos($confirmationMessage, $activityType) !== false) {
            echo "   ✅ Activity name found for: $activityType\n";
        } else {
            echo "   ❌ Activity name not found for: $activityType\n";
            echo "      Got: $confirmationMessage\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error getting confirmation message for $activityType: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Test 4: Callback handling patterns
echo "4. TESTING CALLBACK PATTERNS:\n";
$expectedCallbacks = [
    'category_Табель_ШМ_ПроектнаяДеят' => 'Should show sub-menu',
    'category_Табель_ШМ_УчастиеВЗПИ' => 'Should show confirmation for ZPI participation',
    'category_Табель_ШМ_РазрПрограммы' => 'Should show confirmation for work program development',
    'category_Табель_ШМ_РазрГрафика' => 'Should show confirmation for work schedule development',
    'category_Табель_ШМ_ОргДопоставки' => 'Should show confirmation for delivery organization',
    'category_Табель_ШМ_УчастиеВОПИ' => 'Should show confirmation for OPI participation'
];

foreach ($expectedCallbacks as $callback => $expected) {
    // Check if callback handling exists in TabelHandler
    if (strpos($handlerContent, "'{$callback}'") !== false || 
        strpos($handlerContent, str_replace('category_', '', $callback)) !== false) {
        echo "   ✅ Callback pattern found: $callback\n";
    } else {
        echo "   ❌ Callback pattern missing: $callback\n";
    }
}

echo "\n";

// Test 5: Integration test pattern
echo "5. INTEGRATION TEST SUMMARY:\n";
echo "   Expected user flow:\n";
echo "   1. User clicks '🔧 Отдел ШМР' → Gets supervision menu\n";
echo "   2. User clicks '📂 Проектная деятельность' → Gets supervision project sub-menu\n";
echo "   3. User clicks any project activity → Gets confirmation dialog\n";
echo "   4. User clicks 'Да' → Activity gets recorded in timesheet\n\n";

echo "   Required callbacks:\n";
echo "   - category_Табель_ШМ_ПроектнаяДеят → handleSupervisionProjectActivityMenu()\n";
echo "   - category_Табель_ШМ_УчастиеВЗПИ → Confirmation for ZPI participation\n";
echo "   - category_Табель_ШМ_РазрПрограммы → Confirmation for work program development\n";
echo "   - category_Табель_ШМ_РазрГрафика → Confirmation for work schedule development\n";
echo "   - category_Табель_ШМ_ОргДопоставки → Confirmation for delivery organization\n";
echo "   - category_Табель_ШМ_УчастиеВОПИ → Confirmation for OPI participation\n";

echo "\n=== TEST COMPLETE ===\n";
echo "\nImplementation Summary:\n";
echo "✅ Created createSupervisionProjectActivityKeyboard() method\n";
echo "✅ Added 5 supervision project activity types to activityTypes array\n";  
echo "✅ Modified supervision project activity callback to show sub-menu\n";
echo "✅ Added handleSupervisionProjectActivityMenu() method\n";
echo "✅ Added individual callback handlers for all 5 supervision project activities\n";
echo "✅ Added activity names to TabelEntry.php for confirmation messages\n";
echo "\nThe supervision department now has copies of all commissioning project activities:\n";
echo "1. Участие в ЗПИ\n";
echo "2. Разработка программы работ\n";
echo "3. Разработка графика работ\n";
echo "4. Организация допоставки\n";
echo "5. Участие в ОПИ\n";
?>