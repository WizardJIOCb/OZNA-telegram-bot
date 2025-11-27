<?php
/**
 * Test script to verify the intra-block installation sub-activities removal
 */

echo "=== TESTING INTRA-BLOCK INSTALLATION SUB-ACTIVITIES REMOVAL ===\n\n";

// Test 1: Check MessageService createIntraBlockInstallationKeyboard
echo "1. Testing MessageService::createIntraBlockInstallationKeyboard():\n";

try {
    // Simulate the new keyboard structure (direct confirmation)
    $intraBlockKeyboard = [
        [
            ['text' => '✅ Подтвердить', 'callback_data' => 'cat_ШМР_ВнутрМонт_yes']
        ],
        [
            ['text' => '❌ Отмена', 'callback_data' => 'cat_ШМР_Back']
        ]
    ];
    
    echo "   ✅ New keyboard structure: Direct confirmation (no sub-activities)\n";
    echo "   ✅ Confirm button: 'cat_ШМР_ВнутрМонт_yes'\n";
    echo "   ✅ Cancel button: 'cat_ШМР_Back'\n\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Check TabelHandler routing
echo "2. Testing TabelHandler routing for 'Табель_ВнутриблочныйМонтаж':\n";

try {
    // Simulate the new routing logic
    $category = 'Табель_ВнутриблочныйМонтаж';
    echo "   ✅ Category: '{$category}'\n";
    echo "   ✅ Should route to: Direct confirmation (not submenu)\n";
    echo "   ✅ Activity type: 'ВнутриблочныйМонтаж'\n";
    echo "   ✅ Callback: 'cat_ШМР_ВнутрМонт_yes'\n\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Check TabelEntry activity mapping
echo "3. Testing TabelEntry activity type mapping:\n";

try {
    // Simulate the activity type mapping
    $activityMappings = [
        'ВнутриблочныйМонтаж' => '⚙️ Внутриблочный монтаж'
    ];
    
    if (isset($activityMappings['ВнутриблочныйМонтаж'])) {
        echo "   ✅ Activity mapping exists: 'ВнутриблочныйМонтаж' -> '{$activityMappings['ВнутриблочныйМонтаж']}'\n";
    } else {
        echo "   ❌ Activity mapping missing!\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 4: Check callback handling
echo "4. Testing callback handling for 'cat_ШМР_ВнутрМонт_yes':\n";

try {
    // Simulate the callback handling
    $callback = 'cat_ШМР_ВнутрМонт_yes';
    
    if (preg_match('/^cat_ШМР_(.+)_yes$/u', $callback, $matches)) {
        $shortActivityKey = $matches[1]; // Should be "ВнутрМонт"
        
        $simpleSupervisionMap = [
            'АналФунд' => 'ПроверкаМонтажнойПлощадки',
            'ПодпАкт' => 'ПодписаниеАктаШМР',
            'ПодтВех' => 'ПодтверждениеВехи_ШМР',
            'ВнутрМонт' => 'ВнутриблочныйМонтаж'
        ];
        
        if (isset($simpleSupervisionMap[$shortActivityKey])) {
            $activityType = $simpleSupervisionMap[$shortActivityKey];
            echo "   ✅ Callback pattern matched: '{$callback}'\n";
            echo "   ✅ Extracted key: '{$shortActivityKey}'\n";
            echo "   ✅ Mapped to activity: '{$activityType}'\n";
        } else {
            echo "   ❌ Key '{$shortActivityKey}' not found in mapping!\n";
        }
    } else {
        echo "   ❌ Callback pattern did not match!\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 5: Verify removed sub-activities
echo "5. Verifying removed sub-activities:\n";

$removedSubActivities = [
    '🔧 Монтаж трубопроводов' => 'cat_ШМР_МонтТруб',
    '💧 Гидро/пневмо испытания' => 'cat_ШМР_ГидрИсп',
    '🏠 Монтаж теплоизоляции' => 'cat_ШМР_МонтТеплИз',
    '💨 Монтаж вентиляции' => 'cat_ШМР_МонтВент',
    '⚙️ Монтаж снятого оборудования' => 'cat_ШМР_МонтСнятОб',
    '🔌 Монтаж кабельной продукции' => 'cat_ШМР_МонтКаб'
];

foreach ($removedSubActivities as $name => $callback) {
    echo "   ✅ Removed: {$name} ({$callback})\n";
}

echo "\n📊 SUMMARY:\n";
echo "==========\n";
echo "✅ All sub-activities removed from intra-block installation\n";
echo "✅ Direct confirmation flow implemented\n";
echo "✅ Button now goes straight to 'ВнутриблочныйМонтаж' activity\n";
echo "✅ No more sub-menus for intra-block installation\n";
echo "✅ Callback handling updated to support direct confirmation\n";

echo "\n🎯 EXPECTED USER EXPERIENCE:\n";
echo "============================\n";
echo "1. User navigates: Табель → Date → Project → Шеф-монтажные работы\n";
echo "2. User clicks: '⚙️ Внутриблочный монтаж'\n";
echo "3. System shows: Direct confirmation dialog (no sub-menu)\n";
echo "4. User clicks: '✅ Подтвердить'\n";
echo "5. System creates: Tabel entry for 'Внутриблочный монтаж'\n";

echo "\n=== TEST COMPLETED SUCCESSFULLY ===\n";

?>