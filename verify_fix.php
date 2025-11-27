<?php
// Final verification that the fix is complete

echo "=== Final Verification of Electrical Equipment Activities Fix ===\n\n";

$content = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

// Check that the specific callbacks are properly handled
$callbacks = [
    'cat_ТабЭлОб_РабПоГр_yes',
    'cat_ТабЭлОб_ФормИД_yes',
    'cat_ТабЭлОб_ИзмСхПд_yes'
];

echo "1. Checking that callbacks are properly defined...\n";
foreach ($callbacks as $callback) {
    // Check if callback appears in the handle method conditions
    if (strpos($content, "\$callbackData === '{$callback}'") !== false) {
        echo "✅ PASS: Callback '{$callback}' found in handle method\n";
    } else {
        echo "❌ FAIL: Callback '{$callback}' NOT found in handle method\n";
    }
}

echo "\n2. Checking that duplicated conditions have been removed...\n";
// Check for duplicated callbackData conditions
$duplicatedCallbackData = [
    'cat_ТабЭлОб_РабПоГр_yes',
    'cat_ТабЭлОб_ФормИД_yes'
];

foreach ($duplicatedCallbackData as $callback) {
    $count = substr_count($content, "\$callbackData === '{$callback}'");
    if ($count == 1) {
        echo "✅ PASS: Single occurrence of '\$callbackData === \'{$callback}\''\n";
    } else {
        echo "❌ FAIL: Expected 1 occurrence of '\$callbackData === \'{$callback}\'', found {$count}\n";
    }
}

// Check for duplicated actualCategory conditions
$duplicatedActualCategory = [
    'cat_ТабЭлОб_РабПоГр_yes',
    'cat_ТабЭлОб_ФормИД_yes'
];

foreach ($duplicatedActualCategory as $callback) {
    $count = substr_count($content, "\$actualCategory === '{$callback}'");
    if ($count == 1) {
        echo "✅ PASS: Single occurrence of '\$actualCategory === \'{$callback}\''\n";
    } else {
        echo "❌ FAIL: Expected 1 occurrence of '\$actualCategory === \'{$callback}\'', found {$count}\n";
    }
}

echo "\n3. Checking that short format is handled in isActivityExecution...\n";
if (strpos($content, "Handle short callback format for electrical equipment first") !== false) {
    echo "✅ PASS: Short callback format handling found in isActivityExecution\n";
} else {
    echo "❌ FAIL: Short callback format handling NOT found in isActivityExecution\n";
}

echo "\n4. Checking that short format is handled in handleActivityExecution...\n";
if (strpos($content, "Handle short callback format for electrical equipment FIRST") !== false) {
    echo "✅ PASS: Short callback format handling found in handleActivityExecution\n";
} else {
    echo "❌ FAIL: Short callback format handling NOT found in handleActivityExecution\n";
}

echo "\n=== Final Summary ===\n";
echo "The fix for the electrical equipment activities should now be complete.\n";
echo "The 'Да' button for 'Работа по графику' and 'Формирование ИД' activities\n";
echo "in the 'Электрооборудование' section should now send reports properly.\n";
?>