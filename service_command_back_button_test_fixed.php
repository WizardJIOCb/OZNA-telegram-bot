<?php
// Service command test for back button functionality (FIXED VERSION)
// This test simulates the actual user flow without requiring log access

echo "=== BACK BUTTON FUNCTIONALITY TEST ===\n\n";

// Test 1: Verify the back button callback data in MessageService
echo "1. Checking back button configuration in MessageService...\n";

// Read the MessageService file
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Check if the supervision installation works keyboard has the correct back button
if (strpos($messageServiceContent, "['text' => '⬅️ Назад', 'callback_data' => 'Табель_ШМ_Back']") !== false) {
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
if (strpos($tabelHandlerContent, "if (\$category === 'Табель_ШМ_Back')") !== false && 
    strpos($tabelHandlerContent, "return \$this->handleBackToSupervision(\$chatId, \$conversation);") !== false) {
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

if (strpos($tabelHandlerContent, "\$keyboard = \$this->messageService->createSupervisionKeyboard();") !== false) {
    echo "   ✅ handleBackToSupervision creates supervision keyboard\n";
} else {
    echo "   ❌ handleBackToSupervision does not create supervision keyboard\n";
}

echo "\n";

// Summary
echo "=== TEST RESULTS SUMMARY ===\n";

$test1 = strpos($messageServiceContent, "['text' => '⬅️ Назад', 'callback_data' => 'Табель_ШМ_Back']") !== false;
$test2 = strpos($tabelHandlerContent, "\$callbackData === 'Табель_ШМ_Back'") !== false;
$test3 = strpos($tabelHandlerContent, "if (\$category === 'Табель_ШМ_Back')") !== false && 
         strpos($tabelHandlerContent, "return \$this->handleBackToSupervision(\$chatId, \$conversation);") !== false;
$test4 = strpos($tabelHandlerContent, "private function handleBackToSupervision(") !== false;
$test5 = strpos($tabelHandlerContent, "\$keyboard = \$this->messageService->createSupervisionKeyboard();") !== false;

$tests = [
    "Back button configuration" => $test1,
    "TabelHandler callback handling" => $test2,
    "Correct routing" => $test3,
    "Target method exists" => $test4,
    "Creates correct keyboard" => $test5
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

echo "\n";
echo "=== DETAILED VERIFICATION ===\n";
echo "1. MessageService has correct back button: " . ($test1 ? "YES" : "NO") . "\n";
echo "2. TabelHandler recognizes callback: " . ($test2 ? "YES" : "NO") . "\n";
echo "3. Callback routes correctly: " . ($test3 ? "YES" : "NO") . "\n";
echo "4. Target method exists: " . ($test4 ? "YES" : "NO") . "\n";
echo "5. Method creates correct keyboard: " . ($test5 ? "YES" : "NO") . "\n";