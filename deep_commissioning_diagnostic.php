<?php
// Deep diagnostic test for commissioning works button issue

echo "=== DEEP COMMISSIONING WORKS DIAGNOSTIC ===\n\n";

// Test 1: Check current button configuration
echo "1. Current Button Configuration:\n";
echo "================================\n";

// Simulate the current createCommissioningKeyboard method
$currentButton = [
    'text' => '⚙️ ПН работы',
    'callback_data' => 'cat_ТабПНРаботы'
];

echo "Current button text: '{$currentButton['text']}'\n";
echo "Current callback data: '{$currentButton['callback_data']}'\n";
echo "Callback length: " . strlen($currentButton['callback_data']) . " bytes\n\n";

// Test 2: Check TabelHandler routing
echo "2. TabelHandler Routing Analysis:\n";
echo "=================================\n";

$callbackData = $currentButton['callback_data'];
$category = str_replace('cat_', '', $callbackData);

echo "Callback data: $callbackData\n";
echo "Extracted category: $category\n";

// Check if the category matches any TabelHandler conditions
echo "\nRouting conditions check:\n";
if ($category === 'ТабПНРаботы') {
    echo "✅ MATCH: if (\$category === 'ТабПНРаботы') - Should show commissioning works menu\n";
} else {
    echo "❌ NO MATCH: Category doesn't match 'ТабПНРаботы'\n";
}

// Test 3: Check what activities should be shown
echo "\n3. Expected Activities (from createCommissioningWorksKeyboard):\n";
echo "=============================================================\n";

$expectedActivities = [
    '⚡ Электрооборудование' => 'cat_ТабЭлОб',
    '🤖 Автоматизация' => 'cat_ТабАвтом',
    '🔥 Пожарная сигнализация' => 'cat_ТабПожСигн',
    '🛡️ Охранная сигнализация' => 'cat_ТабОхрСигн',
    '❄️ Отопление и вентиляция' => 'cat_ТабОтоплВент',
    '⚙️ Технологическое оборудование' => 'cat_ТабТехОб',
    '📏 Метрология' => 'cat_ТабМетрол',
    '🔄 Комплексное опробование' => 'cat_ТабКомплОпр',
    '📊 Режимная наладка' => 'cat_ТабРежНал',
    '🚗 Подготовка к служебной поездке' => 'cat_ТабПодгПоезд',
    '🛣️ Дорога' => 'cat_ТабДорога',
    '📋 Инструктаж' => 'cat_ТабИнстр',
    '💰 Сдача авансового отчета' => 'cat_ТабСдачаОтч'
];

$count = 1;
foreach ($expectedActivities as $activity => $callback) {
    $callbackLength = strlen($callback);
    $status = $callbackLength <= 64 ? '✅' : '❌';
    echo "{$count}. {$activity} -> {$callback} ({$callbackLength} bytes) {$status}\n";
    $count++;
}

// Test 4: Issue Analysis
echo "\n4. Issue Analysis:\n";
echo "==================\n";

echo "Problem 1: Button text changed\n";
echo "  Before: '⚙️ Пусконаладочные работы'\n";
echo "  After:  '⚙️ ПН работы'\n";
echo "  Impact: Users see abbreviated text instead of full description\n\n";

echo "Problem 2: Activities not showing when clicked\n";
echo "  Expected: Clicking '⚙️ ПН работы' should show 13 activities\n";
echo "  Actual: Button doesn't show activities\n\n";

// Test 5: Possible root causes
echo "5. Possible Root Causes:\n";
echo "========================\n";

$possibleCauses = [
    "TabelHandler routing issue - callback not reaching handleCommissioningWorksMenu",
    "handleCommissioningWorksMenu method not calling createCommissioningWorksKeyboard",
    "createCommissioningWorksKeyboard method returning empty or invalid keyboard",
    "Message sending failure in MessageService->sendMessage",
    "Conversation state issue - project_code not set properly",
    "Callback data corruption during transmission",
    "Telegram API issue with keyboard display"
];

foreach ($possibleCauses as $i => $cause) {
    echo ($i + 1) . ". $cause\n";
}

echo "\n6. Recommended Solutions:\n";
echo "=========================\n";

echo "Solution 1: Restore original button text\n";
echo "  Change '⚙️ ПН работы' back to '⚙️ Пусконаладочные работы'\n";
echo "  This will improve user experience and clarity\n\n";

echo "Solution 2: Verify TabelHandler routing\n";
echo "  Ensure callback 'cat_ТабПНРаботы' properly routes to handleCommissioningWorksMenu\n\n";

echo "Solution 3: Test complete flow\n";
echo "  1. Click 'Отдел ПНР' -> date selection\n";
echo "  2. Select date -> project selection\n";
echo "  3. Select project -> commissioning menu (with 'ПН работы' button)\n";
echo "  4. Click 'ПН работы' -> should show 13 activities\n\n";

echo "=== DIAGNOSTIC COMPLETE ===\n";
echo "Next step: Fix the button text and verify the routing logic\n";
?>