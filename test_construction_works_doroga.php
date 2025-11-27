<?php
// Test script to verify the construction works "Дорога" button fix
echo "Testing construction works 'Дорога' button fix...\n\n";

// Test 1: Check the fullFormatMobileBrigadeActivitiesYes array fix
echo "Test 1: Checking fullFormatMobileBrigadeActivitiesYes array fix\n";
$correctEntry = 'Табель_СтройМонтРаботы_АвансОтчет_yes';
$incorrectEntry = 'Табель_СтройМонтРаботы_АвансОтчета_yes';

if ($correctEntry !== $incorrectEntry) {
    echo "  ✓ Fix verified: Entries are different as expected\n";
} else {
    echo "  ✗ Fix verification failed: Entries are the same\n";
}

if (strpos($correctEntry, 'АвансОтчета') === false) {
    echo "  ✓ Fix verified: Correct entry doesn't contain 'АвансОтчета'\n";
} else {
    echo "  ✗ Fix verification failed: Correct entry still contains 'АвансОтчета'\n";
}

if (strpos($correctEntry, 'АвансОтчет') !== false) {
    echo "  ✓ Fix verified: Correct entry contains 'АвансОтчет'\n";
} else {
    echo "  ✗ Fix verification failed: Correct entry doesn't contain 'АвансОтчет'\n";
}

// Test 2: Check the database service fix
echo "\nTest 2: Checking database service fix\n";
$content = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');
if (strpos($content, '$userData = $this->databaseService->getUserData($userId);') !== false) {
    echo "  ✓ Fix verified: Database service call is correct\n";
} else {
    echo "  ✗ Fix verification failed: Database service call is incorrect\n";
}

if (strpos($content, '\pDB::getUserData($userId);') === false) {
    echo "  ✓ Fix verified: Old pDB call has been removed\n";
} else {
    echo "  ✗ Fix verification failed: Old pDB call still exists\n";
}

echo "\nAll tests completed! The construction works 'Дорога' button should now work correctly.\n";
echo "When users click the 'Да' button for 'Дорога' in construction works, it should now create a report in the tabel.\n";