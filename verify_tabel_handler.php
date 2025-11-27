<?php
// Verification script to check if TabelHandler properly handles the activity types

echo "Verifying TabelHandler Activity Type Support\n";
echo "==========================================\n\n";

// Read the TabelHandler.php file
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Read the TabelEntry.php file
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

// Check if all the required activity types are present in TabelHandler
$requiredActivityTypes = [
    'Электромонтажные',
    'Сварочные',
    'Слесарные',
    'Теплоизоляция',
    'Документы'
];

echo "Checking TabelHandler.php for activity type support...\n";

$missingTypes = [];

foreach ($requiredActivityTypes as $type) {
    if (strpos($tabelHandlerContent, "'{$type}'") === false) {
        $missingTypes[] = $type;
    }
}

if (empty($missingTypes)) {
    echo "✅ SUCCESS: All required activity types are present in TabelHandler!\n\n";
} else {
    echo "❌ FAILURE: The following activity types are missing from TabelHandler:\n";
    foreach ($missingTypes as $type) {
        echo "  - {$type}\n";
    }
    echo "\n";
}

// Check if all the required activity types are present in TabelEntry
echo "Checking TabelEntry.php for activity type support...\n";

$missingTypesEntry = [];

foreach ($requiredActivityTypes as $type) {
    if (strpos($tabelEntryContent, "'{$type}'") === false) {
        $missingTypesEntry[] = $type;
    }
}

if (empty($missingTypesEntry)) {
    echo "✅ SUCCESS: All required activity types are present in TabelEntry!\n\n";
} else {
    echo "❌ FAILURE: The following activity types are missing from TabelEntry:\n";
    foreach ($missingTypesEntry as $type) {
        echo "  - {$type}\n";
    }
    echo "\n";
}

echo "Verification complete.\n";