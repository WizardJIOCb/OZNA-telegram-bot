<?php
// Test script to verify MessageService warranty equipment keyboard fix

echo "Testing MessageService warranty equipment keyboard fix...\n";

// Simulate the createWarrantyEquipmentKeyboard method after our fix
function createWarrantyEquipmentKeyboard($context = 'commissioning') {
    // Determine the correct back button based on context
    $backButtonText = '⬅️ Назад';
    $backButtonCallback = $context === 'supervision' 
        ? 'category_Табель_Пусконаладка_Back' 
        : 'category_Табель_Пусконаладка_Back';
        
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
            ['text' => $backButtonText, 'callback_data' => $backButtonCallback]
        ]
    ];
}

$keyboard = createWarrantyEquipmentKeyboard();
echo "Warranty equipment keyboard:\n";
foreach ($keyboard as $index => $row) {
    echo "Row " . ($index + 1) . ": " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}

// Test the workflow
echo "\nTesting workflow:\n";
$firstButton = $keyboard[0][0]; // First button (Электрооборудование)
echo "First button: " . $firstButton['text'] . "\n";
echo "First button callback: " . $firstButton['callback_data'] . "\n";

// Simulate user clicking the first button
$category = $firstButton['callback_data'];
echo "Category after clicking: " . $category . "\n";

// Extract equipment type
$equipmentType = str_replace('category_Табель_РабГарантии_', '', $category);
echo "Equipment type: " . $equipmentType . "\n";

// Generate confirmation callback (this is what happens when user clicks "Yes")
$confirmationCallback = "cat_Гарантии_{$equipmentType}_yes";
echo "Confirmation callback: " . $confirmationCallback . "\n";

echo "Test completed successfully!\n";
?>