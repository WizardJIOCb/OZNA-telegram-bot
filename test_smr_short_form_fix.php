<?php
/**
 * Test script to verify ШМР short form activity type mappings
 */

echo "=== Testing ШМР Short Form Activity Mappings ===\n\n";

// Extract the activityTypeMappings array from TabelEntry.php
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

// Find the activityTypeMappings array
$start = strpos($tabelEntryContent, 'private array $activityTypeMappings = [');
$end = strpos($tabelEntryContent, '];', $start) + 2;
$arrayContent = substr($tabelEntryContent, $start, $end - $start);

// Evaluate the array content to get the actual mappings
eval('$' . $arrayContent);

echo "Total mappings loaded: " . count($activityTypeMappings) . "\n\n";

// Test the specific case from user's message
$testActivityType = 'ШМР_ВхКонтр_РабПоГр';
echo "Testing problem case: '{$testActivityType}'\n";

if (isset($activityTypeMappings[$testActivityType])) {
    echo "✅ FOUND: '{$activityTypeMappings[$testActivityType]}'\n";
} else {
    echo "❌ NOT FOUND - this explains the issue!\n";
}

echo "\n=== All ШМР Short Form Mappings ===\n";
foreach ($activityTypeMappings as $key => $value) {
    if (strpos($key, 'ШМР_') === 0 && (strpos($key, '_РабПоГр') !== false || strpos($key, '_ПодтВех') !== false)) {
        echo "'{$key}' => '{$value}'\n";
    }
}

echo "\n=== Test createTabelMessage simulation ===\n";
function simulateMessage($activityType, $mappings) {
    $activityText = $mappings[$activityType] ?? $activityType;
    return "✅ Запись в табель от 04.10.2025, в проект XXX о {$activityText} добавлена";
}

$testCases = [
    'ШМР_ВхКонтр_РабПоГр',
    'ШМР_ЗавОбор_РабПоГр',
    'ШМР_ВхКонтр_ПодтВех'
];

foreach ($testCases as $testCase) {
    echo "\nActivity: {$testCase}\n";
    echo "Message: " . simulateMessage($testCase, $activityTypeMappings) . "\n";
}