<?php
// Simple verification script to check if the fix was properly implemented

echo "=== Verifying 'Пусконаладочные работы' button fix ===\n\n";

// Read the TabelHandler file
$tabelHandlerContent = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

// Check if the handler for Табель_ПусконаладочныеРаботы exists
if (strpos($tabelHandlerContent, 'if ($category === \'Табель_ПусконаладочныеРаботы\')') !== false) {
    echo "✓ Handler for 'Табель_ПусконаладочныеРаботы' found in handleOtherWorkTypes method\n";
} else {
    echo "✗ Handler for 'Табель_ПусконаладочныеРаботы' NOT found in handleOtherWorkTypes method\n";
}

// Check if the handleCommissioningWorksMenu method exists
if (strpos($tabelHandlerContent, 'private function handleCommissioningWorksMenu') !== false) {
    echo "✓ handleCommissioningWorksMenu method found\n";
} else {
    echo "✗ handleCommissioningWorksMenu method NOT found\n";
}

// Check if the method calls createCommissioningWorksKeyboardWithMobileActivities
if (strpos($tabelHandlerContent, 'createCommissioningWorksKeyboardWithMobileActivities') !== false) {
    echo "✓ handleCommissioningWorksMenu calls createCommissioningWorksKeyboardWithMobileActivities\n";
} else {
    echo "✗ handleCommissioningWorksMenu does NOT call createCommissioningWorksKeyboardWithMobileActivities\n";
}

echo "\nVerification completed.\n";
?>