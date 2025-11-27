<?php
echo "=== Testing Электрооборудование Warranty Equipment Fix ===\n\n";

// Test 1: Check callback length
$callback = 'category_Табель_РабГарантии_ЭлектрОб';
echo "1. Callback Length Check:\n";
echo "   Callback: $callback\n";
echo "   Length: " . strlen($callback) . " bytes (limit: 64)\n";
echo "   Status: " . (strlen($callback) <= 64 ? "✅ PASS" : "❌ FAIL") . "\n\n";

// Test 2: Check if handler exists in current file
echo "2. Handler Existence Check:\n";
$tabelHandlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    // Check for the specific handler
    $handlerPattern = "if \(\$category === 'Табель_РабГарантии_ЭлектрОб'\)";
    $hasHandler = preg_match('/' . preg_quote($handlerPattern, '/') . '/u', $content);
    
    echo "   Looking for: $handlerPattern\n";
    echo "   Handler found: " . ($hasHandler ? "✅ YES" : "❌ NO") . "\n";
    
    if ($hasHandler) {
        // Check for complete handler structure
        $patterns = [
            'error_log.*handling warranty work for electrical equipment',
            'getConfirmationMessage.*РабГарантии_ЭлектрОб',
            'createConfirmationKeyboard.*cat_Гарантии_ЭлектрОб_yes'
        ];
        
        echo "   Handler components:\n";
        foreach ($patterns as $pattern) {
            $found = preg_match('/' . $pattern . '/u', $content);
            echo "     - " . str_replace('.*', ' + ', $pattern) . ": " . ($found ? "✅" : "❌") . "\n";
        }
    }
} else {
    echo "   ❌ TabelHandler.php not found\n";
}

echo "\n3. Callback Mapping Check:\n";
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    // Check for callback mappings
    $mappings = [
        "'category_Табель_РабГарантии_ЭлектрОб' => 'Табель_РабГарантии_ЭлектрОб'",
        "'cat_Гарантии_ЭлектрОб_yes' => 'РабГарантии_ЭлектрОб_yes'"
    ];
    
    foreach ($mappings as $mapping) {
        $found = strpos($content, $mapping) !== false;
        echo "   - " . substr($mapping, 0, 50) . "...: " . ($found ? "✅" : "❌") . "\n";
    }
}

echo "\n4. Activity Type Check:\n";
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    // Check if activity type is in allowed array
    $activityTypeFound = strpos($content, "'РабГарантии_ЭлектрОб'") !== false;
    echo "   - РабГарантии_ЭлектрОб in activityTypes: " . ($activityTypeFound ? "✅" : "❌") . "\n";
}

echo "\n5. Flow Test:\n";
echo "   Expected flow:\n";
echo "   1. User clicks '⚡ Электрооборудование' button\n";
echo "   2. Callback 'category_Табель_РабГарантии_ЭлектрОб' sent\n";
echo "   3. extractCategoryFromCallback maps to 'Табель_РабГарантии_ЭлектрОб'\n";
echo "   4. handleOtherWorkTypes processes category\n";
echo "   5. Handler creates confirmation message\n";
echo "   6. User sees confirmation dialog\n\n";

echo "=== Test Complete ===\n";
?>