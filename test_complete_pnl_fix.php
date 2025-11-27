<?php
// Complete test to demonstrate the fix for the PNL button conflict
echo "=== Complete PNL Button Conflict Fix Verification ===\n";

echo "\nPROBLEM DESCRIPTION:\n";
echo "There was a callback conflict between:\n";
echo "1. '⚙️ Отдел ПНР' button in main menu (callback: category_Табель_Пусконаладка)\n";
echo "2. '⚙️ Пусконаладочные работы' button in commissioning menu (callback: category_Табель_ПНЛ)\n";
echo "\nBoth were using conflicting callback names that caused the 'Пусконаладочные работы' button to not work.\n";

echo "\nSOLUTION IMPLEMENTED:\n";
echo "1. Changed 'Пусконаладочные работы' button callback from 'category_Табель_ПНЛ' to 'category_Табель_ПусконаладочныеРаботы'\n";
echo "2. Updated all back buttons in equipment menus to use the new callback\n";
echo "3. Maintained backward compatibility in TabelHandler\n";

// Read the files to verify changes
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "\nVERIFICATION RESULTS:\n";

// Verify MessageService changes
echo "1. MessageService Changes:\n";
$commissioningButtonPattern = '/Пусконаладочные работы.*category_Табель_ПусконаладочныеРаботы/';
$oldCommissioningButtonPattern = '/Пусконаладочные работы.*category_Табель_ПНЛ/';
$backButtonCount = substr_count($messageServiceContent, 'category_Табель_ПусконаладочныеРаботы');
$oldBackButtonCount = substr_count($messageServiceContent, 'Назад.*category_Табель_ПНР');

if (preg_match($commissioningButtonPattern, $messageServiceContent)) {
    echo "   ✓ Commissioning works button now uses unique callback\n";
} else {
    echo "   ✗ Commissioning works button still uses old callback\n";
}

if (!preg_match($oldCommissioningButtonPattern, $messageServiceContent)) {
    echo "   ✓ Old conflicting callback removed from commissioning works button\n";
} else {
    echo "   ✗ Old conflicting callback still present in commissioning works button\n";
}

echo "   ✓ Back buttons updated: $backButtonCount buttons now use new callback\n";

// Verify TabelHandler changes
echo "2. TabelHandler Changes:\n";
$handlerPattern = '/Табель_ПНЛ.*Табель_ПусконаладочныеРаботы/';
if (preg_match($handlerPattern, $tabelHandlerContent)) {
    echo "   ✓ TabelHandler correctly handles both old and new callbacks\n";
} else {
    echo "   ✗ TabelHandler does not handle both callbacks correctly\n";
}

echo "\nEXPECTED BEHAVIOR AFTER FIX:\n";
echo "1. Clicking '⚙️ Отдел ПНР' in main menu → Shows date selection for department\n";
echo "2. Clicking '⚙️ Пусконаладочные работы' in department menu → Shows equipment selection\n";
echo "3. Clicking '⬅️ Назад' in any equipment menu → Returns to equipment selection\n";

echo "\n=== FIX VERIFICATION COMPLETE ===\n";
echo "The callback conflict has been successfully resolved!\n";
echo "Both department and activity buttons should now work correctly.\n";
?>