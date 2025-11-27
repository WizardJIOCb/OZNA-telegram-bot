<?php
// Final test script to verify the removal of the "Метрология" button

echo "=== Final Verification of Метрология Button Removal ===\n\n";

// Test 1: Check MessageService.php for the button
echo "Test 1: Checking MessageService.php for Метрология button\n";
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Check if the specific button definition exists
$buttonPattern = "['text' => '📏 Метрология', 'callback_data' => 'category_Табель_Метрология']";
$buttonExists = strpos($messageServiceContent, $buttonPattern) !== false;

echo "Метрология button definition exists: " . ($buttonExists ? 'YES' : 'NO') . "\n";

// Check for any mention of Метрология in the commissioning keyboard method
$commissioningMethodPattern = 'createCommissioningWorksKeyboardWithMobileActivities';
$methodStart = strpos($messageServiceContent, $commissioningMethodPattern);
if ($methodStart !== false) {
    // Find the end of the method (next function declaration)
    $methodEnd = strpos($messageServiceContent, 'public function', $methodStart + strlen($commissioningMethodPattern));
    if ($methodEnd === false) {
        $methodEnd = strlen($messageServiceContent); // End of file
    }
    
    $methodContent = substr($messageServiceContent, $methodStart, $methodEnd - $methodStart);
    $metrologyInMethod = strpos($methodContent, 'Метрология') !== false;
    echo "Метрология mentioned in commissioning keyboard method: " . ($metrologyInMethod ? 'YES' : 'NO') . "\n";
}

echo "\n";

// Test 2: Check TabelHandler.php for submenu categories
echo "Test 2: Checking TabelHandler.php for submenu categories\n";
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Look for the specific submenu category arrays
$submenuPatterns = [
    "/\\\$submenuCategories\s*=\s*\[\s*'КомплОпробование',\s*'РежимнаяНаладка'\s*\]/", // Correct pattern
    "/'Метрология'/" // Should not be found
];

$correctSubmenuFound = false;
$metrologyFound = false;

// Check if the correct submenu pattern exists (without Метрология)
if (preg_match($submenuPatterns[0], $tabelHandlerContent)) {
    $correctSubmenuFound = true;
}

// Check if Метрология is still mentioned anywhere
if (preg_match($submenuPatterns[1], $tabelHandlerContent)) {
    $metrologyFound = true;
}

echo "Correct submenu arrays (without Метрология) found: " . ($correctSubmenuFound ? 'YES' : 'NO') . "\n";
echo "Метрология still found in file: " . ($metrologyFound ? 'YES' : 'NO') . "\n";

echo "\n=== Final Result ===\n";
if (!$buttonExists && $correctSubmenuFound && !$metrologyFound) {
    echo "✅ SUCCESS: Метрология button has been successfully removed!\n";
    echo "   - Button removed from commissioning works keyboard\n";
    echo "   - Category removed from all submenu arrays\n";
    echo "   - No remaining references to Метрология in relevant code\n";
} else {
    echo "❌ ISSUE DETECTED:\n";
    if ($buttonExists) {
        echo "   - Метрология button still exists in commissioning keyboard\n";
    }
    if (!$correctSubmenuFound) {
        echo "   - Correct submenu arrays not found\n";
    }
    if ($metrologyFound) {
        echo "   - Метрология still referenced in TabelHandler.php\n";
    }
}

echo "\n";
?>