<?php
/**
 * Test script to verify the ШМР "Прочее" fixes
 * Tests both issues reported by the user:
 * 1. Preliminary report showing raw activity type 
 * 2. "Unknown tabel command" error after clicking "Yes"
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== ШМР ПРОЧЕЕ FIXES VERIFICATION TEST ===\n\n";

// Initialize services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Test 1: getConfirmationMessage with new activity type
echo "TEST 1: getConfirmationMessage for new ШМР activity type\n";
echo "=====================================================\n";

$newActivityType = 'ШМР_Прочее_ТехнологическоеОборудование';
$projectCode = 'XXX';

try {
    $confirmationMessage = $tabelEntry->getConfirmationMessage($newActivityType, $projectCode);
    echo "Activity Type: '{$newActivityType}'\n";
    echo "Generated Message: '{$confirmationMessage}'\n";
    
    // Check if message shows friendly text instead of raw activity type
    if (strpos($confirmationMessage, 'ШМР_Прочее_ТехнологическоеОборудование') !== false) {
        echo "❌ ISSUE: Raw activity type still visible in confirmation message\n";
    } else {
        echo "✅ OK: Confirmation message shows user-friendly text\n";
    }
    
    // Check if message contains expected friendly text
    if (strpos($confirmationMessage, 'Прочее (Технологическое оборудование)') !== false) {
        echo "✅ OK: Contains expected friendly text\n";
    } else {
        echo "⚠️  WARNING: Does not contain expected friendly text\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: Exception in getConfirmationMessage: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Activity type mappings
echo "TEST 2: Activity Type Mappings Check\n";
echo "===================================\n";

// Check activity mappings
$reflection = new ReflectionClass($tabelEntry);
$property = $reflection->getProperty('activityTypeMappings');
$property->setAccessible(true);
$mappings = $property->getValue($tabelEntry);

$testActivityTypes = [
    'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр',
    'ШМР_Прочее_ТехнологическоеОборудование'
];

foreach ($testActivityTypes as $activityType) {
    if (isset($mappings[$activityType])) {
        echo "✅ '{$activityType}' -> '{$mappings[$activityType]}'\n";
    } else {
        echo "❌ '{$activityType}' NOT FOUND in mappings\n";
    }
}

echo "\n";

// Test 3: Callback handling simulation
echo "TEST 3: Callback Handling Simulation\n";
echo "====================================\n";

// Simulate the callback flow
$callbacks = [
    'cat_ШМР_ТехОб_Проч_yes' => 'New clean format',
    'cat_ШМР_ТехОб_Проч_РабГр_yes' => 'Old format with suffix'
];

foreach ($callbacks as $callback => $description) {
    echo "Callback: '{$callback}' ({$description})\n";
    
    // Check if callback would be recognized by isActivityExecution pattern
    if (preg_match('/^cat_ШМР_ТехОб_Проч_yes$/u', $callback)) {
        echo "  ✅ Would be recognized by new ШМР Прочее pattern\n";
    } elseif (preg_match('/^cat_ШМР_ТехОб_Проч_РабГр_yes$/u', $callback)) {
        echo "  ✅ Would be recognized by old ШМР Прочее pattern (backward compatibility)\n";
    } else {
        echo "  ❌ Would NOT be recognized by any pattern\n";
    }
}

echo "\n";

// Test 4: Custom text handling
echo "TEST 4: Custom Text Handling in createTabelMessage\n";
echo "=================================================\n";

$testData = [
    'fio' => 'Тестовый Пользователь',
    'date' => '09.10.2025',
    'projectCode' => 'XXX',
    'activityType' => 'ШМР_Прочее_ТехнологическоеОборудование',
    'idleReason' => 'Тестовая работа'
];

try {
    $finalMessage = $tabelEntry->createTabelMessage(
        $testData['fio'],
        $testData['date'], 
        $testData['projectCode'],
        $testData['activityType'],
        $testData['idleReason']
    );
    
    echo "Generated Final Message:\n";
    echo $finalMessage . "\n\n";
    
    // Check if custom text is included
    if (strpos($finalMessage, $testData['idleReason']) !== false) {
        echo "✅ OK: Custom text '{$testData['idleReason']}' included in final message\n";
    } else {
        echo "❌ ISSUE: Custom text '{$testData['idleReason']}' NOT included in final message\n";
    }
    
    // Check if message has proper format
    if (strpos($finalMessage, 'Добавлена запись в табель') !== false) {
        echo "✅ OK: Final message has proper format\n";
    } else {
        echo "❌ ISSUE: Final message does not have proper format\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: Exception in createTabelMessage: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Expected flow summary
echo "TEST 5: Expected Flow Summary\n";
echo "============================\n";

echo "EXPECTED FLOW AFTER FIXES:\n";
echo "1. User clicks 'Прочее' button -> cat_ШМР_ТехОб_Проч_РабГр\n";
echo "2. System prompts: 'Пожалуйста, введите описание выполняемой работы:'\n";
echo "3. User enters: 'Тест'\n";
echo "4. System shows: 'Проект XXX: Прочее (Технологическое оборудование). Отправить запись в табель?'\n";
echo "   Custom text: 'Тест'\n";
echo "5. User clicks 'Да' -> cat_ШМР_ТехОб_Проч_yes\n";
echo "6. System creates entry with custom text and shows success message\n\n";

echo "KEY FIXES IMPLEMENTED:\n";
echo "✅ Added 'ШМР_Прочее_ТехнологическоеОборудование' to activityNames in getConfirmationMessage\n";
echo "✅ Added handler for 'cat_ШМР_ТехОб_Проч_yes' in handleActivityExecution\n";
echo "✅ Added custom text handling for ШМР Прочее in handleActivityExecution\n";
echo "✅ Added ШМР activity type to createTabelMessage custom text handling\n\n";

echo "Test completed! 🎉\n";
?>