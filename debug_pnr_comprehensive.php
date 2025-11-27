<?php
// Comprehensive debug script for PNR (ПНР) button issue

echo "Comprehensive Debug: PNR Button Issue\n";
echo "====================================\n\n";

// 1. Check what callback is sent when PNR button is clicked
echo "1. PNR Button Callback Analysis:\n";
$workTypeKeyboard = [
    [
        ['text' => '💼 Мобильная бригада', 'callback_data' => 'category_Табель_Мобильная_Бригада']
    ],
    [
        ['text' => '🔧 Отдел ШМР', 'callback_data' => 'category_Табель_Шеф_монтаж']
    ],
    [
        ['text' => '⚙️ Отдел ПНР', 'callback_data' => 'category_Табель_Пусконаладка']  // This is what we're interested in
    ],
    [
        ['text' => '📊 Текущая деятельность', 'callback_data' => 'category_Табель_Текущая']
    ]
];

echo "   PNR Button callback_data: " . $workTypeKeyboard[2][0]['callback_data'] . "\n\n";

// 2. Simulate TabelHandler routing
echo "2. TabelHandler Routing Simulation:\n";
$callbackData = 'category_Табель_Пусконаладка';
$category = substr($callbackData, strlen('category_'));

echo "   Extracted category: $category\n";

// Check which handler would be called
if ($category === 'Табель_Пусконаладка') {
    echo "   → Would route to: handleCommissioningDateSelection()\n";
} else {
    echo "   → Would NOT route to handleCommissioningDateSelection()\n";
}

echo "\n";

// 3. Check handleCommissioningDateSelection method
echo "3. handleCommissioningDateSelection Method Analysis:\n";
echo "   Creates date selection keyboard with prefix: category_Табель_Пусконаладка\n";
echo "   Expected buttons:\n";

$prefix = 'category_Табель_Пусконаладка';
$expectedButtons = [
    "{$prefix}_Сегодня",
    "{$prefix}_Вчера", 
    "{$prefix}_Позавчера",
    "{$prefix}_ДругаяДата"
];

foreach ($expectedButtons as $i => $button) {
    echo "     " . ($i+1) . ". $button\n";
}

echo "\n";

// 4. Check if there might be conflicts with other callbacks
echo "4. Potential Callback Conflicts Check:\n";
$potentialConflicts = [
    'category_Табель_ПНЛ',  // Old format
    'category_Табель_ПусконаладочныеРаботы',  // Commissioning works submenu
    'category_Табель_Пусконаладка_Back'  // Back button
];

foreach ($potentialConflicts as $conflict) {
    echo "   Checking: $conflict\n";
    if ($conflict === $callbackData) {
        echo "     → CONFLICT: Matches PNR button callback!\n";
    } else {
        echo "     → No conflict\n";
    }
}

echo "\n";

// 5. Check the actual TabelHandler implementation
echo "5. TabelHandler Implementation Check:\n";
echo "   Looking for handleOtherWorkTypes method that processes 'Табель_Пусконаладка'...\n";

// Simulate the actual logic from handleOtherWorkTypes
$categoryToCheck = 'Табель_Пусконаладка';

echo "   In handleOtherWorkTypes, checking condition:\n";
echo "   if (\$category === 'Табель_Пусконаладка') {\n";
echo "       return \$this->handleCommissioningDateSelection(\$chatId);\n";
echo "   }\n\n";

if ($categoryToCheck === 'Табель_Пусконаладка') {
    echo "   ✅ Condition matches! Should call handleCommissioningDateSelection()\n";
} else {
    echo "   ❌ Condition does not match!\n";
}

echo "\n";

// 6. Check for possible issues with new activities
echo "6. New Activities Impact Analysis:\n";
echo "   Recently added activities might have affected routing logic.\n";
echo "   Need to check if any new conditions are intercepting the 'Табель_Пусконаладка' category.\n\n";

// 7. Summary
echo "7. Debug Summary:\n";
echo "   ✅ PNR button has correct callback_data: category_Табель_Пусконаладка\n";
echo "   ✅ TabelHandler should route this to handleCommissioningDateSelection()\n";
echo "   ✅ handleCommissioningDateSelection() creates date selection keyboard\n";
echo "   ❓ If date selection still not appearing, possible causes:\n";
echo "       - Issue in MessageService->createDateSelectionKeyboard()\n";
echo "       - Issue in MessageService->sendMessage()\n";
echo "       - Conflict with other callback handlers\n";
echo "       - New activities added might be intercepting the callback\n";
echo "       - Telegram API issue with keyboard display\n";
?>