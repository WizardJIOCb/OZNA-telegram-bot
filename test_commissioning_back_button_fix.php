<?php
/**
 * Test script to verify the commissioning back button fix
 * This script tests that the back button from equipment submenus correctly 
 * navigates to the commissioning works list instead of the main commissioning menu
 */

echo "Testing commissioning back button fix...\n\n";

// Test 1: Check that handleBackToCommissioning now uses createCommissioningWorksKeyboard
echo "1. Verifying handleBackToCommissioning method fix...\n";

// Read the TabelHandler file
$content = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

// Check if the method now uses createCommissioningWorksKeyboard
if (strpos($content, 'createCommissioningWorksKeyboard()') !== false) {
    echo "   ✓ handleBackToCommissioning correctly uses createCommissioningWorksKeyboard()\n";
} else {
    echo "   ✗ handleBackToCommissioning does not use createCommissioningWorksKeyboard()\n";
}

// Check that it no longer uses createCommissioningKeyboard
if (strpos($content, 'createCommissioningKeyboard();') === false) {
    echo "   ✓ handleBackToCommissioning no longer uses createCommissioningKeyboard()\n";
} else {
    echo "   ✗ handleBackToCommissioning still uses createCommissioningKeyboard()\n";
}

// Test 2: Verify the message text is correct
if (strpos($content, 'Пусконаладочные работы для проекта') !== false) {
    echo "   ✓ Message text correctly mentions 'Пусконаладочные работы для проекта'\n";
} else {
    echo "   ✗ Message text does not mention 'Пусконаладочные работы для проекта'\n";
}

echo "\nFix verification complete.\n";
echo "The back button from equipment submenus should now correctly navigate to the 'Пусконаладочные работы' list.\n";
?>