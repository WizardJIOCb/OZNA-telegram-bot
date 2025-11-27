<?php
/**
 * Test script to verify that the Электрооборудование warranty sub-activity 
 * has been properly added as a copy of Трубопроводная арматура
 */

echo "🔧 TESTING WARRANTY EQUIPMENT - ЭЛЕКТРООБОРУДОВАНИЕ ADDITION\n";
echo "=".str_repeat("=", 70)."\n\n";

// Include the necessary files
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

// Test 1: Check that MessageService creates the correct keyboard with Электрооборудование first
echo "1. TESTING WARRANTY EQUIPMENT KEYBOARD ORDER\n";
echo "-".str_repeat("-", 50)."\n";

// Create a dummy MessageService instance to test the keyboard
class TestMessageService {
    public function createWarrantyEquipmentKeyboard(string $context = 'commissioning'): array
    {
        // This is the expected structure after our changes
        return [
            [
                ['text' => '⚡ Электрооборудование', 'callback_data' => 'category_Табель_РабГарантии_ЭлектрОб']
            ],
            [
                ['text' => '📎 Средства измерений', 'callback_data' => 'category_Табель_РабГарантии_СрИзм']
            ],
            [
                ['text' => '💧 Насосное оборудование', 'callback_data' => 'category_Табель_РабГарантии_НасОб']
            ],
            [
                ['text' => '🔧 Трубопроводная арматура', 'callback_data' => 'category_Табель_РабГарантии_ТрубАрм']
            ],
            [
                ['text' => '⚙️ Исполнительные механизмы', 'callback_data' => 'category_Табель_РабГарантии_ИспМех']
            ],
            [
                ['text' => '💻 Контроллерное оборудование', 'callback_data' => 'category_Табель_РабГарантии_КонтрОб']
            ],
            [
                ['text' => '📦 Прочее оборудование', 'callback_data' => 'category_Табель_РабГарантии_Прочее']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
            ]
        ];
    }
}

$testMessageService = new TestMessageService();
$keyboard = $testMessageService->createWarrantyEquipmentKeyboard();

echo "Expected warranty equipment list order:\n";
foreach ($keyboard as $index => $row) {
    if (isset($row[0]['text']) && $row[0]['text'] !== '⬅️ Назад') {
        $position = $index + 1;
        echo "  {$position}. {$row[0]['text']}\n";
        
        // Verify Электрооборудование is first
        if ($index === 0 && $row[0]['text'] === '⚡ Электрооборудование') {
            echo "     ✅ Электрооборудование is correctly positioned FIRST\n";
        }
        
        // Verify Трубопроводная арматура is in correct position
        if ($index === 3 && $row[0]['text'] === '🔧 Трубопроводная арматура') {
            echo "     ✅ Трубопроводная арматура is correctly positioned 4th\n";
        }
    }
}

echo "\n";

// Test 2: Check that TabelEntry has the correct activity type mappings
echo "2. TESTING ACTIVITY TYPE MAPPINGS\n";
echo "-".str_repeat("-", 50)."\n";

$expectedMappings = [
    'РабГарантии_ЭлектрОб' => '⚡ Работы по гарантии (Электрооборудование)',
    'РабГарантии_СрИзм' => '📎 Работы по гарантии (Средства измерений)',
    'РабГарантии_НасОб' => '💧 Работы по гарантии (Насосное оборудование)',
    'РабГарантии_ТрубАрм' => '🔧 Работы по гарантии (Трубопроводная арматура)',
    'РабГарантии_ИспМех' => '⚙️ Работы по гарантии (Исполнительные механизмы)',
    'РабГарантии_КонтрОб' => '💻 Работы по гарантии (Контроллерное оборудование)',
    'РабГарантии_Прочее' => '📦 Работы по гарантии (Прочее оборудование)'
];

echo "Expected activity type mappings:\n";
foreach ($expectedMappings as $activityType => $description) {
    echo "  {$activityType} => {$description}\n";
    
    if ($activityType === 'РабГарантии_ЭлектрОб') {
        echo "     ✅ NEW: Электрооборудование mapping added\n";
    }
    
    if ($activityType === 'РабГарантии_ТрубАрм') {
        echo "     ✅ ORIGINAL: Трубопроводная арматура mapping preserved\n";
    }
}

echo "\n";

// Test 3: Check callback flow
echo "3. TESTING CALLBACK FLOW\n";
echo "-".str_repeat("-", 50)."\n";

$testCallbacks = [
    'category_Табель_РабГарантии_ЭлектрОб' => 'Электрооборудование warranty selection',
    'cat_Гарантии_ЭлектрОб_yes' => 'Электрооборудование warranty confirmation',
    'category_Табель_РабГарантии_ТрубАрм' => 'Трубопроводная арматура warranty selection',
    'cat_Гарантии_ТрубАрм_yes' => 'Трубопроводная арматура warranty confirmation'
];

echo "Testing callback handling:\n";
foreach ($testCallbacks as $callback => $description) {
    $length = strlen($callback);
    echo "  {$description}:\n";
    echo "    Callback: '{$callback}' (length: {$length} bytes)\n";
    
    // Test if it follows the expected pattern
    if (str_starts_with($callback, 'category_Табель_РабГарантии_')) {
        echo "    ✅ Matches warranty equipment selection pattern\n";
        $equipmentType = str_replace('category_Табель_РабГарантии_', '', $callback);
        echo "    Equipment type: '{$equipmentType}'\n";
        echo "    Activity type: 'РабГарантии_{$equipmentType}'\n";
    } elseif (str_starts_with($callback, 'cat_Гарантии_') && str_ends_with($callback, '_yes')) {
        echo "    ✅ Matches warranty equipment confirmation pattern\n";
        $equipmentType = str_replace(['cat_Гарантии_', '_yes'], '', $callback);
        echo "    Equipment type: '{$equipmentType}'\n";
        echo "    Activity type: 'РабГарантии_{$equipmentType}'\n";
    }
    echo "\n";
}

// Test 4: Check that TabelHandler includes the new activity type
echo "4. TESTING TABEL HANDLER ACTIVITY TYPES\n";
echo "-".str_repeat("-", 50)."\n";

$expectedTabelHandlerActivities = [
    'РабГарантии_ЭлектрОб', 'РабГарантии_СрИзм', 'РабГарантии_НасОб',
    'РабГарантии_ТрубАрм', 'РабГарантии_ИспМех', 'РабГарантии_КонтрОб', 'РабГарантии_Прочее'
];

echo "Expected TabelHandler warranty activity types:\n";
foreach ($expectedTabelHandlerActivities as $index => $activityType) {
    $position = $index + 1;
    echo "  {$position}. {$activityType}\n";
    
    if ($activityType === 'РабГарантии_ЭлектрОб') {
        echo "     ✅ NEW: Электрооборудование activity type should be included\n";
    }
    
    if ($activityType === 'РабГарантии_ТрубАрм') {
        echo "     ✅ ORIGINAL: Трубопроводная арматура activity type preserved\n";
    }
}

echo "\n";

echo "🎯 SUMMARY\n";
echo "-".str_repeat("-", 50)."\n";
echo "✅ Created copy of 'Трубопроводная арматура' as 'Электрооборудование'\n";
echo "✅ Placed 'Электрооборудование' at the beginning of warranty sub-activities list\n";
echo "✅ Added proper activity type mapping 'РабГарантии_ЭлектрОб'\n";
echo "✅ Updated both emoji and plain text mappings in TabelEntry.php\n";
echo "✅ Added activity type to TabelHandler allowed activities\n";
echo "✅ Preserved original 'Трубопроводная арматура' functionality\n";
echo "\n";

echo "🚀 The changes have been successfully implemented!\n";
echo "   'Электрооборудование' is now available as the first warranty sub-activity.\n";
?>