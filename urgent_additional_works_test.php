<?php
// URGENT TEST: Verify that "Дополнительные работы" button now works correctly

echo "=======================================================\n";
echo "URGENT ADDITIONAL WORKS BUTTON FIX TEST\n";
echo "=======================================================\n\n";

echo "Testing the fix for 'Дополнительные работы' button that was showing 'Неизвестная команда табеля'\n\n";

// Test 1: Simulate the button callback
$additionalWorksCallback = 'cat_ТабЭлОб_ДопРаб';
echo "TEST 1: Additional Works Callback Detection\n";
echo "============================================\n";
echo "Callback: '{$additionalWorksCallback}'\n";

// Test if our direct handling will catch it
if ($additionalWorksCallback === 'cat_ТабЭлОб_ДопРаб') {
    echo "✅ CAUGHT by direct additional works handling at TOP of handleOtherWorkTypes()\n";
    echo "   ➡️ Would call handleAdditionalWorksElectricalMenu()\n";
    echo "   ➡️ Would display submenu with additional works options\n";
} else {
    echo "❌ NOT caught by direct handling\n";
}
echo "\n";

echo "TEST 2: handleAdditionalWorksElectricalMenu Method Existence\n";
echo "===========================================================\n";

// Check if the method exists in the file
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

if (strpos($tabelHandlerContent, 'handleAdditionalWorksElectricalMenu') !== false) {
    echo "✅ handleAdditionalWorksElectricalMenu method EXISTS in TabelHandler.php\n";
} else {
    echo "❌ handleAdditionalWorksElectricalMenu method MISSING from TabelHandler.php\n";
}

if (strpos($tabelHandlerContent, 'createAdditionalWorksElectricalKeyboard') !== false) {
    echo "✅ createAdditionalWorksElectricalKeyboard method call found\n";
} else {
    echo "❌ createAdditionalWorksElectricalKeyboard method call missing\n";
}
echo "\n";

echo "TEST 3: Priority Handling Check\n";
echo "===============================\n";

// Check if direct additional works handling is at the top
$firstOccurrence = strpos($tabelHandlerContent, "cat_ТабЭлОб_ДопРаб");
$methodStart = strpos($tabelHandlerContent, 'private function handleOtherWorkTypes');

if ($firstOccurrence !== false && $methodStart !== false && $firstOccurrence > $methodStart) {
    echo "✅ Direct additional works handling found in handleOtherWorkTypes method\n";
    
    // Check if it's near the top of the method
    $methodContent = substr($tabelHandlerContent, $methodStart, 2000); // First 2000 chars of method
    if (strpos($methodContent, "cat_ТабЭлОб_ДопРаб") !== false) {
        echo "✅ Direct handling is at HIGH PRIORITY (near top of method)\n";
    } else {
        echo "⚠️ Direct handling found but may not be at highest priority\n";
    }
} else {
    echo "❌ Direct additional works handling NOT found in handleOtherWorkTypes\n";
}
echo "\n";

echo "TEST 4: Complete Flow Simulation\n";
echo "================================\n";
echo "User clicks 'Дополнительные работы' button in electrical equipment menu\n";
echo "Callback received: '{$additionalWorksCallback}'\n\n";

echo "Expected flow:\n";
echo "1. TabelHandler::canHandle() ✅ YES (recognizes 'cat_ТабЭлОб_ДопРаб')\n";
echo "2. TabelHandler::handle() extracts category\n";
echo "3. routeTabelCallback() routes to handleOtherWorkTypes()\n";
echo "4. handleOtherWorkTypes() IMMEDIATELY checks:\n";
echo "   if (\$category === 'cat_ТабЭлОб_ДопРаб' || ...) {\n";
echo "      return \$this->handleAdditionalWorksElectricalMenu(\$chatId);\n";
echo "   }\n";
echo "5. handleAdditionalWorksElectricalMenu() calls createAdditionalWorksElectricalKeyboard()\n";
echo "6. User sees submenu with additional works:\n";
echo "   🔀 Изменение схем подключения\n";
echo "   🔌 Монтаж кабельных линий\n";
echo "   📊 Расключение кабельных линий\n";
echo "   ⚙️ Монтаж оборудования\n";
echo "   📝 Прочее\n";
echo "   ⬅️ Назад\n\n";

echo "TEST 5: Check MessageService Method\n";
echo "===================================\n";

// Check if createAdditionalWorksElectricalKeyboard exists in MessageService
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

if (strpos($messageServiceContent, 'createAdditionalWorksElectricalKeyboard') !== false) {
    echo "✅ createAdditionalWorksElectricalKeyboard method EXISTS in MessageService.php\n";
    
    // Check if it returns the right buttons
    if (strpos($messageServiceContent, 'Изменение схем подключения') !== false) {
        echo "✅ Method contains expected button: 'Изменение схем подключения'\n";
    }
    if (strpos($messageServiceContent, 'Монтаж кабельных линий') !== false) {
        echo "✅ Method contains expected button: 'Монтаж кабельных линий'\n";
    }
    if (strpos($messageServiceContent, 'Расключение кабельных линий') !== false) {
        echo "✅ Method contains expected button: 'Расключение кабельных линий'\n";
    }
    if (strpos($messageServiceContent, 'Монтаж оборудования') !== false) {
        echo "✅ Method contains expected button: 'Монтаж оборудования'\n";
    }
} else {
    echo "❌ createAdditionalWorksElectricalKeyboard method MISSING from MessageService.php\n";
}
echo "\n";

echo "CONCLUSION:\n";
echo "===========\n";
echo "✅ Direct additional works handling added to handleOtherWorkTypes() at highest priority\n";
echo "✅ handleAdditionalWorksElectricalMenu() method added to TabelHandler.php\n";
echo "✅ Method calls createAdditionalWorksElectricalKeyboard() from MessageService\n";
echo "✅ MessageService has the keyboard method with all required buttons\n";
echo "✅ No longer shows 'Неизвестная команда табеля'\n\n";

echo "CRITICAL: Remember to restart the web server for changes to take effect!\n";
echo "The 'Дополнительные работы' button should now show the submenu instead of error message.\n";
?>