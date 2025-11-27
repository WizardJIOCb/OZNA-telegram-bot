<?php
// Debug script to test warranty equipment callback handling

echo "=== WARRANTY EQUIPMENT DEBUGGING ===\n\n";

// Test 1: Check warranty equipment keyboard creation
echo "1. Testing Warranty Equipment Keyboard Creation:\n";
echo "================================================\n";

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $keyboard = $messageService->createWarrantyEquipmentKeyboard();
    
    echo "✅ Warranty equipment keyboard created successfully!\n";
    echo "Number of rows: " . count($keyboard) . "\n\n";
    
    $buttonCount = 0;
    foreach ($keyboard as $rowIndex => $row) {
        echo "Row " . ($rowIndex + 1) . ":\n";
        foreach ($row as $button) {
            $buttonCount++;
            $text = $button['text'] ?? 'N/A';
            $callbackData = $button['callback_data'] ?? 'N/A';
            $callbackLength = strlen($callbackData);
            
            echo "  - {$text}\n";
            echo "    Callback: {$callbackData} ({$callbackLength} bytes)\n";
            
            // Test individual callback patterns
            if (str_starts_with($callbackData, 'category_Табель_РабГарантии_')) {
                echo "    ✅ Matches warranty equipment pattern\n";
                $equipmentType = str_replace('category_Табель_РабГарантии_', '', $callbackData);
                echo "    Equipment type: {$equipmentType}\n";
            } else {
                echo "    ❌ Does NOT match warranty equipment pattern\n";
            }
            echo "\n";
        }
    }
    
    echo "Total buttons: {$buttonCount}\n\n";
    
} catch (Exception $e) {
    echo "❌ Error creating keyboard: " . $e->getMessage() . "\n\n";
}

// Test 2: Test callback pattern matching
echo "2. Testing Callback Pattern Matching:\n";
echo "====================================\n";

$testCallbacks = [
    'cat_ТабГарантии' => 'Main warranty menu',
    'category_Табель_РабГарантии_ЭлектрОб' => 'Electrical equipment',
    'category_Табель_РабГарантии_НасОб' => 'Pumping equipment',
    'category_Табель_РабГарантии_СрИзм' => 'Measuring instruments',
    'category_Табель_РабГарантии_Прочее' => 'Other equipment'
];

foreach ($testCallbacks as $callback => $description) {
    echo "Testing: {$description}\n";
    echo "Callback: {$callback}\n";
    
    // Test main warranty menu pattern
    if ($callback === 'cat_ТабГарантии' || str_starts_with($callback, 'cat_ТабГарант')) {
        echo "  ✅ Matches main warranty menu pattern\n";
    }
    
    // Test individual warranty equipment pattern
    if (str_starts_with($callback, 'category_Табель_РабГарантии_')) {
        echo "  ✅ Matches individual warranty equipment pattern\n";
        $equipmentType = str_replace('category_Табель_РабГарантии_', '', $callback);
        $activityType = 'РабГарантии_' . $equipmentType;
        echo "  Equipment type: {$equipmentType}\n";
        echo "  Activity type: {$activityType}\n";
    }
    
    echo "\n";
}

// Test 3: Test TabelHandler canHandle logic
echo "3. Testing TabelHandler canHandle Logic:\n";
echo "=======================================\n";

function testCanHandle($callbackData) {
    echo "Testing canHandle for: {$callbackData}\n";
    
    // Simulate TabelHandler canHandle logic
    $canHandle = str_starts_with($callbackData, 'category_Табель') ||
                 str_starts_with($callbackData, 'cat_Таб') ||
                 $callbackData === 'cat_ТабГарантии' ||
                 str_starts_with($callbackData, 'cat_ТабГарант');
    
    echo "Result: " . ($canHandle ? "✅ CAN HANDLE" : "❌ CANNOT HANDLE") . "\n\n";
    return $canHandle;
}

foreach ($testCallbacks as $callback => $description) {
    testCanHandle($callback);
}

// Test 4: Check activity type mappings
echo "4. Testing Activity Type Mappings:\n";
echo "=================================\n";

$expectedActivityTypes = [
    'РабГарантии_ЭлектрОб' => '⚡ Работы по гарантии (Электрооборудование)',
    'РабГарантии_НасОб' => '💧 Работы по гарантии (Насосное оборудование)',
    'РабГарантии_СрИзм' => '📎 Работы по гарантии (Средства измерений)',
    'РабГарантии_ТрубАрм' => '🔧 Работы по гарантии (Трубопроводная арматура)',
    'РабГарантии_ИспМех' => '⚙️ Работы по гарантии (Исполнительные механизмы)',
    'РабГарантии_КонтрОб' => '💻 Работы по гарантии (Контроллерное оборудование)',
    'РабГарантии_Прочее' => '📦 Работы по гарантии (Прочее оборудование)'
];

echo "Expected activity type mappings:\n";
foreach ($expectedActivityTypes as $activityType => $description) {
    echo "  {$activityType} => {$description}\n";
}

echo "\n=== CONCLUSION ===\n";
echo "If all tests pass, the warranty equipment system should work correctly.\n";
echo "If it's not working, the issue might be:\n";
echo "1. Server caching - restart the bot\n";
echo "2. Database connection issues\n";
echo "3. Conversation state problems\n";
echo "4. TabelEntry class issues\n";
?>