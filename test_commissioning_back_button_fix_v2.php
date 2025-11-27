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

// Find the handleBackToCommissioning method
$pattern = '/private function handleBackToCommissioning\(string \$chatId, Conversation \$conversation\): ServerResponse\s*{[^}]+}/s';
if (preg_match($pattern, $content, $matches)) {
    $methodContent = $matches[0];
    
    // Check if the method now uses createCommissioningWorksKeyboard
    if (strpos($methodContent, 'createCommissioningWorksKeyboard()') !== false) {
        echo "   ✓ handleBackToCommissioning correctly uses createCommissioningWorksKeyboard()\n";
    } else {
        echo "   ✗ handleBackToCommissioning does not use createCommissioningWorksKeyboard()\n";
        echo "     Method content: " . substr($methodContent, 0, 200) . "...\n";
    }
    
    // Check that it no longer uses createCommissioningKeyboard
    if (strpos($methodContent, 'createCommissioningKeyboard()') === false) {
        echo "   ✓ handleBackToCommissioning no longer uses createCommissioningKeyboard()\n";
    } else {
        echo "   ✗ handleBackToCommissioning still uses createCommissioningKeyboard()\n";
    }
    
    // Check that it has the correct message text
    if (strpos($methodContent, 'Пусконаладочные работы для проекта') !== false) {
        echo "   ✓ Message text correctly mentions 'Пусконаладочные работы для проекта'\n";
    } else {
        echo "   ✗ Message text does not mention 'Пусконаладочные работы для проекта'\n";
    }
} else {
    echo "   ✗ Could not find handleBackToCommissioning method\n";
}

// Test 2: Verify that other instances of createCommissioningKeyboard still exist (they should)
echo "\n2. Verifying other createCommissioningKeyboard instances are unchanged...\n";

// Count total instances of createCommissioningKeyboard
$count = substr_count($content, 'createCommissioningKeyboard()');
if ($count > 0) {
    echo "   ✓ Found $count instances of createCommissioningKeyboard() (expected)\n";
} else {
    echo "   ✗ No instances of createCommissioningKeyboard() found\n";
}

echo "\nFix verification complete.\n";
echo "The back button from equipment submenus should now correctly navigate to the 'Пусконаладочные работы' list.\n";
?>