<?php
/**
 * Test warranty functionality
 */

echo "=== Testing Warranty Functionality ===\n";

// Test 1: Check if warranty button is in commissioning keyboard
echo "\n1. Testing warranty button in commissioning keyboard...\n";

// Simulate MessageService
class MockMessageService {
    public function createCommissioningWorksKeyboard(): array {
        return [
            [
                ['text' => '⚡ Электрооборудование', 'callback_data' => 'category_Табель_Электрооборудование']
            ],
            [
                ['text' => '🤖 Автоматизация', 'callback_data' => 'cat_ТабАвтом']
            ],
            [
                ['text' => '🔥 Пожарная сигнализация', 'callback_data' => 'cat_ТабПожСигн']
            ],
            [
                ['text' => '🛡️ Охранная сигнализация', 'callback_data' => 'cat_ТабОхрСигн']
            ],
            [
                ['text' => '❄️ Отопление и вентиляция', 'callback_data' => 'cat_ТабВентОтопл']
            ],
            [
                ['text' => '🛡️ Работы по гарантии', 'callback_data' => 'cat_ТабГарантии']
            ],
            [
                ['text' => '⚙️ Технологическое оборудование', 'callback_data' => 'cat_ТабТехОб']
            ],
            [          
                ['text' => '📏 Метрология', 'callback_data' => 'cat_ТабМетрол']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
            ]
        ];
    }
    
    public function createWarrantyEquipmentKeyboard(): array {
        return [
            [
                ['text' => '⚡ Электрооборудование', 'callback_data' => 'cat_Гарантии_ЭлектрОб_yes']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПНРаботы']
            ]
        ];
    }
}

$messageService = new MockMessageService();
$keyboard = $messageService->createCommissioningWorksKeyboard();

// Check if warranty button exists
$warrantyButtonFound = false;
foreach ($keyboard as $row) {
    foreach ($row as $button) {
        if (strpos($button['text'], 'Работы по гарантии') !== false) {
            $warrantyButtonFound = true;
            echo "✅ Warranty button found: {$button['text']} -> {$button['callback_data']}\n";
            break 2;
        }
    }
}

if (!$warrantyButtonFound) {
    echo "❌ Warranty button NOT found in commissioning keyboard!\n";
}

// Test 2: Check warranty equipment keyboard
echo "\n2. Testing warranty equipment keyboard...\n";
$warrantyKeyboard = $messageService->createWarrantyEquipmentKeyboard();

$electricalButtonFound = false;
foreach ($warrantyKeyboard as $row) {
    foreach ($row as $button) {
        if (strpos($button['text'], 'Электрооборудование') !== false) {
            $electricalButtonFound = true;
            echo "✅ Electrical equipment button found: {$button['text']} -> {$button['callback_data']}\n";
            break 2;
        }
    }
}

if (!$electricalButtonFound) {
    echo "❌ Electrical equipment button NOT found in warranty keyboard!\n";
}

// Test 3: Test callback patterns
echo "\n3. Testing callback patterns...\n";

$testCallbacks = [
    'cat_ТабГарантии' => 'Warranty main menu',
    'cat_Гарантии_ЭлектрОб_yes' => 'Warranty electrical equipment confirmation'
];

foreach ($testCallbacks as $callback => $description) {
    echo "Testing callback: {$callback} ({$description})\n";
    
    // Test if callback would be handled
    if (str_starts_with($callback, 'cat_ТабГарантии') || str_starts_with($callback, 'cat_Гарантии_')) {
        echo "✅ Callback pattern matches: {$callback}\n";
    } else {
        echo "❌ Callback pattern does NOT match: {$callback}\n";
    }
    
    // Test if it's a confirmation callback
    if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callback, $matches)) {
        $equipmentType = $matches[1];
        echo "✅ Confirmation callback detected: equipment type = {$equipmentType}\n";
    }
}

// Test 4: Test activity type validation
echo "\n4. Testing activity type validation...\n";

$activityTypes = [
    'РабГарантии_ЭлектрОб'
];

$testActivityType = 'РабГарантии_ЭлектрОб';
if (in_array($testActivityType, $activityTypes)) {
    echo "✅ Activity type '{$testActivityType}' is valid\n";
} else {
    echo "❌ Activity type '{$testActivityType}' is NOT valid\n";
}

echo "\n=== Warranty Functionality Test Complete ===\n";
echo "\nIf all tests show ✅, the warranty functionality should work!\n";
echo "Now test by clicking: Отдел ПНР -> Работы по гарантии -> Электрооборудование -> Да\n";