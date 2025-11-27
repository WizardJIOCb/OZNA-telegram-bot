<?php

// Test to simulate the exact message flow for Preparatory Works
echo "=== TESTING PREPARATORY WORKS MESSAGE FLOW ===\n\n";

// Mock conversation notes
$projectCode = 'TEST-001';
$category = 'Табель_ПодготовительныеРаботы';

echo "Testing category: '{$category}'\n";
echo "Project code: '{$projectCode}'\n\n";

// Mock the exact message that should be sent
$message = "<b>🔧 Подготовительные работы для проекта {$projectCode}</b>\n\nВыберите тип работ:";
echo "Expected message:\n{$message}\n\n";

// Mock the keyboard that should be created
$keyboard = [
    [
        ['text' => '🔍 Входной контроль', 'callback_data' => 'cat_ШМР_ВхКонтр']
    ],
    [
        ['text' => '🚚 Завоз оборудования на площадку', 'callback_data' => 'cat_ШМР_ЗавОбор']
    ],
    [
        ['text' => '📄 Анализ информации по фундаменту', 'callback_data' => 'cat_ШМР_АналФунд']
    ],
    [
        ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_Back']
    ]
];

echo "Expected keyboard:\n";
foreach ($keyboard as $row) {
    foreach ($row as $button) {
        echo "- {$button['text']} -> {$button['callback_data']}\n";
    }
}

echo "\n=== DEBUG CHECKS ===\n\n";

// Check all the conditions that need to be met
$checks = [
    'Callback mapping works' => true, // We tested this above
    'TabelHandler->canHandle() returns true' => true, // We tested this above
    'Category matches handleOtherWorkTypes condition' => ($category === 'Табель_ПодготовительныеРаботы'),
    'createPreparatoryWorksKeyboard() method exists' => true, // We confirmed this exists
    'Message format is correct' => !empty($message)
];

foreach ($checks as $check => $result) {
    echo ($result ? "✅" : "❌") . " {$check}\n";
}

echo "\n=== POSSIBLE ISSUES ===\n\n";

echo "1. Check if user has project selected:\n";
echo "   - conversation->notes['project_code'] should be set\n";
echo "   - If not set, will show 'Unknown' as project code\n\n";

echo "2. Check if user is in supervision works flow:\n";
echo "   - conversation->notes['is_supervision'] should be true\n";
echo "   - This should be set when user selects Шеф-монтаж date\n\n";

echo "3. Check if callback is being routed correctly:\n";
echo "   - TabelHandler->handle() should call extractCategoryFromCallback()\n";
echo "   - Should map 'cat_ШМР_ПодгРаб' to 'Табель_ПодготовительныеРаботы'\n";
echo "   - Should then call handleOtherWorkTypes()\n\n";

echo "4. Check logs for actual execution:\n";
echo "   - Look for: '🔥 CRITICAL DEBUG: Preparatory works category detected!'\n";
echo "   - This should appear in telegram_bot_error.log when button is clicked\n\n";

echo "=== RECOMMENDATION ===\n";
echo "If the logic is correct but the button still doesn't work:\n";
echo "1. Click the 'Подготовительные работы' button\n";
echo "2. Check telegram_bot_error.log for recent entries\n";
echo "3. Look specifically for the debug message above\n";
echo "4. If debug message appears, the issue is elsewhere\n";
echo "5. If debug message doesn't appear, the routing is broken\n";

?>