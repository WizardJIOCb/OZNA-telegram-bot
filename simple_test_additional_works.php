<?php
/**
 * Simple test script for "Дополнительные работы" button functionality
 */

echo "Testing 'Дополнительные работы' button functionality...\n\n";

// Test 1: Check if the button exists in the electrical equipment keyboard
echo "Test 1: Checking electrical equipment keyboard in MessageService.php...\n";

// Read the MessageService.php file
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Check if the button is defined
if (strpos($messageServiceContent, 'Дополнительные работы') !== false && 
    strpos($messageServiceContent, 'cat_ТабЭлОб_ДопРаб') !== false) {
    echo "  ✓ 'Дополнительные работы' button found in MessageService.php\n";
} else {
    echo "  ✗ 'Дополнительные работы' button not found in MessageService.php\n";
}

// Test 2: Check if the activity type is defined in TabelHandler.php
echo "\nTest 2: Checking TabelHandler activity types...\n";

// Read the TabelHandler.php file
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check if the activity type is defined
if (strpos($tabelHandlerContent, 'ДопРаб_ЭлектрОб') !== false) {
    echo "  ✓ 'ДопРаб_ЭлектрОб' activity type found in TabelHandler.php\n";
} else {
    echo "  ✗ 'ДопРаб_ЭлектрОб' activity type not found in TabelHandler.php\n";
}

// Test 3: Check callback mapping
echo "\nTest 3: Checking callback mapping...\n";

// Check if the callback mapping exists
if (strpos($tabelHandlerContent, "'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб'") !== false) {
    echo "  ✓ Callback mapping found in TabelHandler.php\n";
} else {
    echo "  ✗ Callback mapping not found in TabelHandler.php\n";
}

// Test 4: Check if the activity type is defined in TabelEntry.php
echo "\nTest 4: Checking TabelEntry activity types...\n";

// Read the TabelEntry.php file
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

// Check if the activity type is defined
if (strpos($tabelEntryContent, 'ДопРаб_ЭлектрОб') !== false) {
    echo "  ✓ 'ДопРаб_ЭлектрОб' activity type found in TabelEntry.php\n";
} else {
    echo "  ✗ 'ДопРаб_ЭлектрОб' activity type not found in TabelEntry.php\n";
}

// Test 5: Check if the shortToFullMap includes ДопРаб
echo "\nTest 5: Checking shortToFullMap in handleActivityExecution...\n";

// Check if the mapping exists
if (strpos($tabelHandlerContent, "'ДопРаб' => 'ДопРаб_ЭлектрОб'") !== false) {
    echo "  ✓ 'ДопРаб' mapping found in shortToFullMap\n";
} else {
    echo "  ✗ 'ДопРаб' mapping not found in shortToFullMap\n";
}

echo "\nTest completed.\n";