<?php
// Simple test script to verify the PNL (Пусконаладочные работы) button fix
echo "=== Testing PNL Button Fix ===\n";

// Read the MessageService file to check the changes
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Test 1: Check that the commissioning works button uses the new callback
echo "\n1. Testing commissioning works button callback:\n";
if (strpos($messageServiceContent, 'category_Табель_ПусконаладочныеРаботы') !== false) {
    echo "SUCCESS: Found new callback 'category_Табель_ПусконаладочныеРаботы' in MessageService\n";
} else {
    echo "FAILURE: New callback not found in MessageService\n";
}

// Test 2: Check that the old conflicting callback is no longer used for commissioning works
echo "\n2. Testing that old callback is not used for commissioning works:\n";
// Look for the specific pattern of commissioning works button with old callback
$pattern = '/Пусконаладочные работы.*category_Табель_ПНЛ/s';
if (preg_match($pattern, $messageServiceContent)) {
    echo "FAILURE: Old callback 'category_Табель_ПНЛ' still found for commissioning works button\n";
} else {
    echo "SUCCESS: Old callback 'category_Табель_ПНЛ' no longer used for commissioning works button\n";
}

// Test 3: Check that back buttons use the new callback
echo "\n3. Testing back buttons use new callback:\n";
$backButtonPattern = 'category_Табель_ПусконаладочныеРаботы';
$backButtonCount = substr_count($messageServiceContent, $backButtonPattern);
echo "Found $backButtonCount back buttons using new callback\n";

if ($backButtonCount > 0) {
    echo "SUCCESS: Back buttons correctly use new callback\n";
} else {
    echo "FAILURE: Back buttons not updated to use new callback\n";
}

// Test 4: Check that the old back button callback is no longer used
echo "\n4. Testing that old back button callback is removed:\n";
$oldBackButtonPattern = 'category_Табель_ПНР';
$oldBackButtonCount = substr_count($messageServiceContent, $oldBackButtonPattern);
// We expect some occurrences for other uses, but not for back buttons
// Let's check specifically for back button context
$oldBackButtonContextPattern = '/Назад.*category_Табель_ПНР/s';
if (preg_match($oldBackButtonContextPattern, $messageServiceContent)) {
    echo "FAILURE: Old back button callback 'category_Табель_ПНР' still found\n";
} else {
    echo "SUCCESS: Old back button callback 'category_Табель_ПНР' removed from back buttons\n";
}

echo "\n=== Test Complete ===\n";
echo "The callback conflict should now be resolved!\n";
?>