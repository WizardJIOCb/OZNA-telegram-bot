<?php
// Test script to debug the "Пусконаладочные работы" button issue

echo "=== Testing Commissioning Works Button ===\n\n";

// Test 1: Check the button callback data
echo "1. Button Configuration Test:\n";
$buttonText = '⚙️ Пусконаладочные работы';
$callbackData = 'cat_ТабПНРаботы';

echo "Button text: $buttonText\n";
echo "Callback data: $callbackData\n";
echo "Callback length: " . strlen($callbackData) . " bytes\n";

if (strlen($callbackData) > 64) {
    echo "❌ ISSUE: Callback data exceeds Telegram's 64-byte limit!\n";
} else {
    echo "✅ OK: Callback data is within Telegram's 64-byte limit\n";
}

echo "\n";

// Test 2: Check category extraction
echo "2. Category Extraction Test:\n";
$category = str_replace('cat_', '', $callbackData);
echo "Extracted category: $category\n";

// Test 3: Check TabelHandler routing logic
echo "\n3. TabelHandler Routing Logic Test:\n";
echo "In handleOtherWorkTypes method:\n";
echo "  if (\$category === 'ТабПНРаботы') {\n";
echo "      // Should call handleCommissioningWorksMenu\n";
echo "  }\n";

if ($category === 'ТабПНРаботы') {
    echo "✅ MATCH: Category matches the condition in TabelHandler\n";
    echo "✅ Should call handleCommissioningWorksMenu() method\n";
} else {
    echo "❌ NO MATCH: Category does not match the condition!\n";
}

echo "\n";

// Test 4: Check what handleCommissioningWorksMenu should do
echo "4. handleCommissioningWorksMenu Method Test:\n";
echo "This method should:\n";
echo "  1. Get project_code from conversation\n";
echo "  2. Create commissioning works keyboard (13 activities)\n";
echo "  3. Send message with keyboard\n";

echo "\n";

// Test 5: Check if there are any conflicts
echo "5. Potential Conflicts Check:\n";
$potentialConflicts = [
    'Duplicate handlers for ТабПНРаботы',
    'Project code not set in conversation',
    'Wrong keyboard being created',
    'Message sending failure'
];

foreach ($potentialConflicts as $i => $conflict) {
    echo "  " . ($i + 1) . ". $conflict\n";
}

echo "\n";

// Test 6: Verify the commissioning works keyboard content
echo "6. Commissioning Works Keyboard Content:\n";
$expectedActivities = [
    '⚡ Электрооборудование',
    '🤖 Автоматизация', 
    '🔥 Пожарная сигнализация',
    '🛡️ Охранная сигнализация',
    '❄️ Отопление и вентиляция',
    '⚙️ Технологическое оборудование',
    '📏 Метрология',
    '🔄 Комплексное опробование', 
    '📊 Режимная наладка',
    '🚗 Подготовка к служебной поездке',
    '🛣️ Дорога',
    '📋 Инструктаж',
    '💰 Сдача авансового отчета'
];

echo "Expected " . count($expectedActivities) . " activities:\n";
foreach ($expectedActivities as $i => $activity) {
    echo "  " . ($i + 1) . ". $activity\n";
}

echo "\n=== Test Complete ===\n";
echo "\nNext steps for debugging:\n";
echo "1. Check TabelHandler logs for 'handleCommissioningWorksMenu'\n";
echo "2. Check if conversation has project_code set\n";
echo "3. Verify MessageService->createCommissioningWorksKeyboard() is called\n";
echo "4. Check if message is sent successfully\n";
?>