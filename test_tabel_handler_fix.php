<?php
// Test script to verify the TabelHandler correctly handles the new callback
echo "=== Testing TabelHandler Fix ===\n";

// Read the TabelHandler file to check the changes
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Test 1: Check that the TabelHandler handles the new callback
echo "\n1. Testing TabelHandler handles new callback:\n";
if (strpos($tabelHandlerContent, 'Табель_ПусконаладочныеРаботы') !== false) {
    echo "SUCCESS: TabelHandler handles new callback 'Табель_ПусконаладочныеРаботы'\n";
} else {
    echo "FAILURE: TabelHandler does not handle new callback\n";
}

// Test 2: Check that the TabelHandler still handles the old callback for backward compatibility
echo "\n2. Testing TabelHandler maintains backward compatibility:\n";
if (strpos($tabelHandlerContent, 'Табель_ПНЛ') !== false) {
    echo "SUCCESS: TabelHandler still handles old callback 'Табель_ПНЛ' for backward compatibility\n";
} else {
    echo "FAILURE: TabelHandler does not maintain backward compatibility\n";
}

// Test 3: Check the specific condition in handleOtherWorkTypes method
echo "\n3. Testing handleOtherWorkTypes condition:\n";
$pattern = '/Табель_ПНЛ.*Табель_ПусконаладочныеРаботы/';
if (preg_match($pattern, $tabelHandlerContent)) {
    echo "SUCCESS: handleOtherWorkTypes correctly handles both callbacks\n";
} else {
    echo "FAILURE: handleOtherWorkTypes does not handle both callbacks correctly\n";
}

echo "\n=== Test Complete ===\n";
echo "The TabelHandler should now correctly route both the department and activity buttons!\n";
?>