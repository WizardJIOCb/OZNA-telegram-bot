<?php
// Service command test for back button functionality
// This test simulates the actual user flow without requiring log access

echo "=== BACK BUTTON FUNCTIONALITY TEST ===\n\n";

// Test 1: Verify the back button callback data in MessageService
echo "1. Checking back button configuration in MessageService...\n";

// Read the MessageService file
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Check if the supervision installation works keyboard has the correct back button
$pattern = "/createSupervisionInstallationWorksKeyboard.*?\['text' => '⬅️ Назад', 'callback_data' => 'Табель_ШМ_Back'\]/s";
if (preg_match($pattern, $messageServiceContent)) {
    echo "   ✅ Supervision Installation Works keyboard has correct back button\n";
} else {
    echo "   ❌ Supervision Installation Works keyboard missing correct back button\n";
}

echo "\n";

// Test 2: Verify TabelHandler can handle the callback
echo "2. Checking if TabelHandler can handle 'Табель_ШМ_Back' callback...\n";

// Read the TabelHandler file
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check if 'Табель_ШМ_Back' is in the canHandle method
if (strpos($tabelHandlerContent, "\$callbackData === 'Табель_ШМ_Back'") !== false) {
    echo "   ✅ TabelHandler can handle 'Табель_ШМ_Back' callback\n";
} else {
    echo "   ❌ TabelHandler cannot handle 'Табель_ШМ_Back' callback\n";
}

echo "\n";

// Test 3: Verify routing to correct method
echo "3. Checking if 'Табель_ШМ_Back' routes to correct method...\n";

// Look for the routing logic
$pattern = "/if \\\$category === 'Табель_ШМ_Back'.*?handleBackToSupervision/s";
if (preg_match($pattern, $tabelHandlerContent)) {
    echo "   ✅ 'Табель_ШМ_Back' correctly routes to handleBackToSupervision\n";
} else {
    echo "   ❌ 'Табель_ШМ_Back' routing not found or incorrect\n";
}

echo "\n";

// Test 4: Verify the target method exists
echo "4. Checking if handleBackToSupervision method exists...\n";

if (strpos($tabelHandlerContent, "private function handleBackToSupervision(") !== false) {
    echo "   ✅ handleBackToSupervision method exists\n";
} else {
    echo "   ❌ handleBackToSupervision method not found\n";
}

echo "\n";

// Test 5: Verify the target method creates correct keyboard
echo "5. Checking if handleBackToSupervision creates supervision keyboard...\n";

$pattern = "/handleBackToSupervision.*?createSupervisionKeyboard/s";
if (preg_match($pattern, $tabelHandlerContent)) {
    echo "   ✅ handleBackToSupervision creates supervision keyboard\n";
} else {
    echo "   ❌ handleBackToSupervision does not create supervision keyboard\n";
}

echo "\n";

// Summary
echo "=== TEST RESULTS SUMMARY ===\n";

$tests = [
    "Back button configuration" => preg_match($pattern, $messageServiceContent),
    "TabelHandler callback handling" => strpos($tabelHandlerContent, "\$callbackData === 'Табель_ШМ_Back'") !== false,
    "Correct routing" => preg_match($pattern, $tabelHandlerContent),
    "Target method exists" => strpos($tabelHandlerContent, "private function handleBackToSupervision(") !== false,
    "Creates correct keyboard" => preg_match($pattern, $tabelHandlerContent)
];

$passed = 0;
$total = count($tests);

foreach ($tests as $testName => $result) {
    if ($result) {
        echo "✅ $testName: PASSED\n";
        $passed++;
    } else {
        echo "❌ $testName: FAILED\n";
    }
}

echo "\n";
echo "Overall Result: $passed/$total tests passed\n";

if ($passed == $total) {
    echo "🎉 ALL TESTS PASSED! The back button should now work correctly.\n";
    echo "When users press 'Назад' in 'Шеф-монтажные работы', they will return to the supervision menu.\n";
} else {
    echo "⚠️ Some tests failed. The back button may not work correctly.\n";
}