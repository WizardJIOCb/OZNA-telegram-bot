<?php
// Simple test script to verify the removal of the "Метрология" button

echo "=== Testing Removal of Метрология Button ===\n\n";

// Test 1: Check commissioning works keyboard after removal
echo "Test 1: Checking commissioning works keyboard after removal\n";

// Read the MessageService.php file directly to check if the button is still there
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

$metrologyButtonFound = strpos($messageServiceContent, 'Метрология') !== false;
$metrologyInCommissioningKeyboard = strpos($messageServiceContent, "['text' => '📏 Метрология', 'callback_data' => 'category_Табель_Метрология']") !== false;

echo "Метрология text found in MessageService.php: " . ($metrologyButtonFound ? 'YES' : 'NO') . "\n";
echo "Метрология button in commissioning keyboard: " . ($metrologyInCommissioningKeyboard ? 'YES' : 'NO') . "\n\n";

// Test 2: Check TabelHandler submenu categories after removal
echo "Test 2: Checking TabelHandler submenu categories after removal\n";

// Read the TabelHandler.php file directly to check if Метрология is still in submenu categories
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Count occurrences of 'Метрология' in the file
$metrologyCount = substr_count($tabelHandlerContent, "'Метрология'");
echo "Number of occurrences of 'Метрология' in TabelHandler.php: " . $metrologyCount . "\n";

// Check if any of the submenu category arrays still contain 'Метрология'
$submenuPatterns = [
    "/\\\$submenuCategories = \[\s*'Метрология'/",
    "/'Метрология',\s*'КомплОпробование'/",
    "/'КомплОпробование',\s*'Метрология'/"
];

$metrologyInSubmenu = false;
foreach ($submenuPatterns as $pattern) {
    if (preg_match($pattern, $tabelHandlerContent)) {
        $metrologyInSubmenu = true;
        break;
    }
}

echo "Метрология found in submenu categories: " . ($metrologyInSubmenu ? 'YES' : 'NO') . "\n\n";

echo "=== Test Complete ===\n";
if (!$metrologyInCommissioningKeyboard && !$metrologyInSubmenu) {
    echo "✅ SUCCESS: Метрология button has been successfully removed!\n";
    echo "   - Button removed from commissioning works keyboard\n";
    echo "   - Category removed from all submenu arrays\n";
} else {
    echo "❌ FAILURE: Метрология button was not fully removed.\n";
    if ($metrologyInCommissioningKeyboard) {
        echo "   - Button still present in commissioning works keyboard\n";
    }
    if ($metrologyInSubmenu) {
        echo "   - Category still present in submenu arrays\n";
    }
}
?>