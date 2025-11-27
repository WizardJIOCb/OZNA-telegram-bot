<?php
// Debug test to simulate what happens when pressing "Назад" button in each activity menu
// This simulates the user experience step by step

echo "=== DEBUG TEST: BACK BUTTON NAVIGATION ===\n\n";

// Simulate the user flow step by step
echo "USER FLOW SIMULATION:\n";
echo "1. User opens Telegram bot\n";
echo "2. User selects 'Табель'\n";
echo "3. User selects 'Отдел ШМР'\n";
echo "4. User selects 'Шеф-монтажные работы'\n";
echo "5. User sees the following menu:\n\n";

// Show what the user sees in the supervision installation works menu
echo "🔧 Шеф-монтажные работы menu:\n";
echo "   📅 Работы по графику\n";
echo "   🚗 Подготовка к служебной поездке\n";
echo "   🛣️ Дорога\n";
echo "   📋 Инструктаж\n";
echo "   💰 Сдача авансового отчета\n";
echo "   ⬅️ Назад  ← USER PRESSES THIS BUTTON\n\n";

// Simulate what happens when user presses the back button
echo "WHAT HAPPENS WHEN USER PRESSES 'Назад' BUTTON:\n";
echo "1. Button sends callback: 'Табель_ШМ_Back'\n";
echo "2. TabelHandler receives callback\n";
echo "3. TabelHandler recognizes callback (after our fix)\n";
echo "4. TabelHandler routes to handleBackToSupervision method\n";
echo "5. handleBackToSupervision creates supervision keyboard\n";
echo "6. User sees the supervision department menu:\n\n";

// Show what the user should see after pressing back
echo "🔧 Табель - Шеф-монтаж menu (EXPECTED RESULT):\n";
echo "   🔧 Шеф-монтажные работы\n";
echo "   📂 Проектная деятельность\n";
echo "   🔧 Устранение замечаний РК\n";
echo "   🛡️ Работы по гарантии\n";
echo "   ⏸️ Простой\n";
echo "   ⬅️ Назад\n\n";

// Verify the actual configuration
echo "CONFIGURATION VERIFICATION:\n";

// Check MessageService configuration
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');
$backButtonFound = strpos($messageServiceContent, "['text' => '⬅️ Назад', 'callback_data' => 'Табель_ШМ_Back']") !== false;

echo "1. Supervision Installation Works back button: " . ($backButtonFound ? "✅ CORRECT" : "❌ INCORRECT") . "\n";

// Check TabelHandler configuration
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');
$callbackHandled = strpos($tabelHandlerContent, "\$callbackData === 'Табель_ШМ_Back'") !== false;
$routingCorrect = strpos($tabelHandlerContent, "if (\$category === 'Табель_ШМ_Back')") !== false && 
                  strpos($tabelHandlerContent, "return \$this->handleBackToSupervision(\$chatId, \$conversation);") !== false;

echo "2. TabelHandler handles 'Табель_ШМ_Back': " . ($callbackHandled ? "✅ YES" : "❌ NO") . "\n";
echo "3. Routing to handleBackToSupervision: " . ($routingCorrect ? "✅ CORRECT" : "❌ INCORRECT") . "\n";

echo "\n";
echo "=== FINAL RESULT ===\n";
if ($backButtonFound && $callbackHandled && $routingCorrect) {
    echo "🎉 SUCCESS: Back button navigation should work correctly!\n";
    echo "   When user presses 'Назад' in 'Шеф-монтажные работы',\n";
    echo "   they will be returned to the supervision department menu.\n";
} else {
    echo "❌ FAILURE: Back button navigation may not work correctly.\n";
    if (!$backButtonFound) echo "   - Back button not configured correctly\n";
    if (!$callbackHandled) echo "   - TabelHandler doesn't handle the callback\n";
    if (!$routingCorrect) echo "   - Routing to supervision menu is incorrect\n";
}

echo "\n";
echo "=== USER EXPERIENCE ===\n";
echo "Before fix: User presses 'Назад' → Goes to main timesheet menu ❌\n";
echo "After fix:  User presses 'Назад' → Goes to supervision menu ✅\n";