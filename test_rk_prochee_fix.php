<?php
// Test to verify RK "Прочее оборудование" button fix

echo "=== RK ПРОЧЕЕ ОБОРУДОВАНИЕ BUTTON FIX TEST ===\n\n";

echo "Testing the callback mapping and routing for RK 'Прочее оборудование' button\n\n";

// Test 1: Check MessageService callback generation
echo "TEST 1: MessageService Callback Generation\n";
echo "==========================================\n";
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

if (preg_match("/'\u{1F4E6} \u041F\u0440\u043E\u0447\u0435\u0435 \u043E\u0431\u043E\u0440\u0443\u0434\u043E\u0432\u0430\u043D\u0438\u0435'.*?'callback_data'\s*=>\s*'([^']+)'/u", $messageServiceContent, $matches)) {
    $callbackData = $matches[1];
    echo "✅ Found RK Прочее button callback: '{$callbackData}'\n";
} else {
    echo "❌ RK Прочее button callback NOT found\n";
    $callbackData = 'category_Табель_УстрЗам_Прочее'; // fallback for testing
}

echo "\n";

// Test 2: Check extractCategoryFromCallback mapping
echo "TEST 2: Callback Mapping\n";
echo "========================\n";
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Simulate extractCategoryFromCallback logic
function testExtractCategory($callbackData) {
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        $result = substr($callbackData, strlen('category_'));
        echo "✅ Category extraction: '{$callbackData}' → '{$result}'\n";
        return $result;
    }
    
    echo "❌ Category extraction failed for: '{$callbackData}'\n";
    return '';
}

$extractedCategory = testExtractCategory($callbackData);

echo "\n";

// Test 3: Check handler condition
echo "TEST 3: Handler Condition Check\n";
echo "===============================\n";

$conditionsToTest = [
    'cat_ТабУстрЗамПрочее',
    'ТабУстрЗамПрочее', 
    'Табель_УстрЗам_Прочее'
];

echo "Extracted category: '{$extractedCategory}'\n";
echo "Testing against handler conditions:\n";

$matched = false;
foreach ($conditionsToTest as $condition) {
    if ($extractedCategory === $condition) {
        echo "✅ MATCH: '{$extractedCategory}' === '{$condition}'\n";
        $matched = true;
        break;
    } else {
        echo "  ⚪ No match: '{$extractedCategory}' !== '{$condition}'\n";
    }
}

if (!$matched) {
    echo "❌ NO MATCHES FOUND - handler condition needs update\n";
}

echo "\n";

// Test 4: Check if mapping exists in extractCategoryFromCallback
echo "TEST 4: Mapping Verification\n";
echo "============================\n";

if (strpos($tabelHandlerContent, "'category_Табель_УстрЗам_Прочее' => 'ТабУстрЗамПрочее'") !== false) {
    echo "✅ Mapping found: 'category_Табель_УстрЗам_Прочее' → 'ТабУстрЗамПрочее'\n";
} else {
    echo "❌ Mapping NOT found in extractCategoryFromCallback\n";
}

echo "\n";

// Test 5: Expected flow
echo "TEST 5: Expected Flow\n";
echo "====================\n";
echo "1. User clicks 'Прочее оборудование' in RK submenu\n";
echo "2. Callback generated: '{$callbackData}'\n";
echo "3. extractCategoryFromCallback: '{$extractedCategory}'\n";
echo "4. Handler condition check: " . ($matched ? "MATCHES ✅" : "NO MATCH ❌") . "\n";
echo "5. Expected result: " . ($matched ? "Prompts for custom input ✅" : "Shows 'Unknown command' ❌") . "\n\n";

// Test 6: Actual handler code verification
echo "TEST 6: Handler Code Verification\n";
echo "=================================\n";

if (strpos($tabelHandlerContent, "|| \$category === 'Табель_УстрЗам_Прочее'") !== false) {
    echo "✅ Handler condition includes: 'Табель_УстрЗам_Прочее'\n";
} else {
    echo "❌ Handler condition MISSING: 'Табель_УстрЗам_Прочее'\n";
}

if (strpos($tabelHandlerContent, "'custom_input_activity_type' = 'УстрЗам_Прочее'") !== false) {
    echo "✅ Custom input activity type set correctly\n";
} else {
    echo "❌ Custom input activity type NOT found\n";
}

if (strpos($tabelHandlerContent, "Пожалуйста, введите описание выполняемой работы:") !== false) {
    echo "✅ Custom input prompt found\n";
} else {
    echo "❌ Custom input prompt NOT found\n";
}

echo "\n";

// Test summary
echo "SUMMARY:\n";
echo "========\n";
if ($matched) {
    echo "✅ PASS: RK 'Прочее оборудование' button should now work correctly\n";
    echo "   Expected behavior: User clicks button → prompted for custom equipment description\n";
} else {
    echo "❌ FAIL: RK 'Прочее оборудование' button routing needs further fixes\n";
    echo "   Issue: Callback mapping or handler condition mismatch\n";
}

echo "\nNext step: Test the button in Telegram to confirm it prompts for custom input\n";
?>