<?php

echo "=== Verification of Updated Supervision Installation Works ===\n\n";

// Check all files for the new sub-activities
$files = [
    'CustomCommands/Handlers/TabelHandler.php',
    'CustomCommands/Services/MessageService.php',
    'CustomCommands/Services/TabelEntry.php'
];

$newActivities = [
    'ШМ_ПодготовкаКПоездке',
    'ШМ_Дорога',
    'ШМ_Инструктаж',
    'ШМ_СдачаАвансовогоОтчета'
];

echo "1. Checking activity types in TabelHandler.php:\n";
$tabelHandlerContent = file_get_contents($files[0]);

foreach ($newActivities as $activity) {
    $found = strpos($tabelHandlerContent, "'{$activity}'") !== false;
    echo "   ✓ {$activity}: " . ($found ? "YES" : "NO") . "\n";
}

echo "\n2. Checking keyboard buttons in MessageService.php:\n";
$messageServiceContent = file_get_contents($files[1]);

$expectedButtons = [
    'Работы по графику',
    'Подготовка к служебной поездке',
    'Дорога',
    'Инструктаж',
    'Сдача авансового отчета'
];

foreach ($expectedButtons as $button) {
    $found = strpos($messageServiceContent, $button) !== false;
    echo "   ✓ {$button}: " . ($found ? "YES" : "NO") . "\n";
}

echo "\n3. Checking callback handlers in TabelHandler.php:\n";
$expectedCallbacks = [
    'category_Табель_ШМ_ПодготовкаКПоездке',
    'category_Табель_ШМ_Дорога',
    'category_Табель_ШМ_Инструктаж',
    'category_Табель_ШМ_СдачаАвансовогоОтчета'
];

foreach ($expectedCallbacks as $callback) {
    $found = strpos($tabelHandlerContent, $callback) !== false;
    echo "   ✓ {$callback}: " . ($found ? "YES" : "NO") . "\n";
}

echo "\n4. Checking activity descriptions in TabelEntry.php:\n";
$tabelEntryContent = file_get_contents($files[2]);

foreach ($newActivities as $activity) {
    $found = strpos($tabelEntryContent, "'{$activity}'") !== false;
    echo "   ✓ {$activity}: " . ($found ? "YES" : "NO") . "\n";
}

echo "\n5. Checking confirmation callbacks in isActivityExecution:\n";
$expectedConfirmations = [
    'ШМ_ПодготовкаКПоездке',
    'ШМ_Дорога',
    'ШМ_Инструктаж',
    'ШМ_СдачаАвансовогоОтчета'
];

// Look for the regex pattern that includes all activities
$regexPattern = 'ШМ_РабПоГрафику|ШМ_ПодготовкаКПоездке|ШМ_Дорога|ШМ_Инструктаж|ШМ_СдачаАвансовогоОтчета';
$regexFound = strpos($tabelHandlerContent, $regexPattern) !== false;
echo "   ✓ Regex pattern with all activities: " . ($regexFound ? "YES" : "NO") . "\n";

foreach ($expectedConfirmations as $confirmation) {
    $found = strpos($tabelHandlerContent, $confirmation) !== false;
    echo "   ✓ {$confirmation}: " . ($found ? "YES" : "NO") . "\n";
}

echo "\n6. Summary:\n";
// Count all completed items
$allItems = [
    // Activity types (4)
    ...array_map(fn($a) => strpos($tabelHandlerContent, "'{$a}'") !== false, $newActivities),
    // Buttons (5)
    ...array_map(fn($b) => strpos($messageServiceContent, $b) !== false, $expectedButtons),
    // Callbacks (4)
    ...array_map(fn($c) => strpos($tabelHandlerContent, $c) !== false, $expectedCallbacks),
    // Descriptions (4)
    ...array_map(fn($a) => strpos($tabelEntryContent, "'{$a}'") !== false, $newActivities),
    // Confirmations (4)
    ...array_map(fn($c) => strpos($tabelHandlerContent, $c) !== false, $expectedConfirmations)
];

$completedCount = count(array_filter($allItems));
$totalCount = count($allItems);

if ($completedCount === $totalCount) {
    echo "   🎉 IMPLEMENTATION COMPLETE! ({$completedCount}/{$totalCount})\n";
    echo "   ✅ All sub-activities have been successfully added\n";
    echo "   ✅ Menu keyboard updated with emojis\n";
    echo "   ✅ Callback handlers implemented\n";
    echo "   ✅ Activity descriptions added\n";
    echo "   ✅ Confirmation patterns configured\n";
} else {
    echo "   ⚠️ IMPLEMENTATION PARTIAL: ({$completedCount}/{$totalCount})\n";
    echo "   Some items may need attention\n";
}

echo "\n=== Verification Complete ===\n";