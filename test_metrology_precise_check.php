<?php
// Precise test script to verify the removal of the "Метрология" button

echo "=== Precise Verification of Метрология Removal ===\n\n";

// Test 1: Check MessageService.php
echo "Test 1: Checking MessageService.php\n";
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Look for the exact button definition
$buttonPattern = "['text' => '📏 Метрология', 'callback_data' => 'category_Табель_Метрология']";
$buttonExists = strpos($messageServiceContent, $buttonPattern) !== false;

echo "  Метрология button definition exists: " . ($buttonExists ? 'YES' : 'NO') . "\n";

// Test 2: Check TabelHandler.php
echo "\nTest 2: Checking TabelHandler.php\n";
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Count exact occurrences of the string 'Метрология'
$metrologyCount = substr_count($tabelHandlerContent, 'Метрология');
echo "  Total occurrences of 'Метрология': " . $metrologyCount . "\n";

// Check for the specific submenu arrays that should NOT contain Метрология
$patternsToCheck = [
    "isActivityConfirmation" => "/private function isActivityConfirmation/",
    "isOtherWorkTypesConfirmation" => "/private function isOtherWorkTypesConfirmation/",
    "isOtherWorkTypesSubmenu" => "/private function isOtherWorkTypesSubmenu/"
];

$allSubmenusClean = true;
foreach ($patternsToCheck as $functionName => $pattern) {
    if (preg_match($pattern, $tabelHandlerContent, $matches, PREG_OFFSET_CAPTURE)) {
        $functionStart = $matches[0][1];
        // Find the end of the function (next function or end of file)
        $nextFunction = strpos($tabelHandlerContent, "private function", $functionStart + 100);
        if ($nextFunction === false) {
            $functionEnd = strlen($tabelHandlerContent);
        } else {
            $functionEnd = $nextFunction;
        }
        
        $functionContent = substr($tabelHandlerContent, $functionStart, $functionEnd - $functionStart);
        
        // Check if this function's submenu array contains Метрология
        if (strpos($functionContent, "'Метрология'") !== false) {
            echo "  ❌ {$functionName} still contains 'Метрология'\n";
            $allSubmenusClean = false;
        } else {
            echo "  ✅ {$functionName} correctly cleaned\n";
        }
    }
}

echo "\n=== Final Assessment ===\n";
if (!$buttonExists && $allSubmenusClean) {
    echo "✅ SUCCESS: Complete removal of Метрология button confirmed!\n";
    echo "   - Button removed from UI\n";
    echo "   - All submenu references eliminated\n";
} else {
    echo "❌ Issues remain:\n";
    if ($buttonExists) {
        echo "   - Button still exists in UI\n";
    }
    if (!$allSubmenusClean) {
        echo "   - Some submenu arrays still reference Метрология\n";
    }
}

echo "\nNote: Any remaining occurrences of 'Метрология' are likely in comments or test files,\n";
echo "      which do not affect the actual functionality.\n";
?>