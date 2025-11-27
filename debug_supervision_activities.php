<?php
echo "=== SUPERVISION CONSTRUCTION ACTIVITIES DEBUG ===\n\n";

// Read the TabelHandler file to get the activityTypes array
$content = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

// Extract the activityTypes array
$start = strpos($content, 'private array $activityTypes = [');
$end = strpos($content, '];', $start) + 2;
$arrayContent = substr($content, $start, $end - $start);

echo "Activity types found in file:\n";
echo "==============================\n";

// Problematic activities that should be in the array
$problematicActivities = [
    'АнализИнформацииФундамент',
    'МонтажПлощадокОбслуживания',
    'МонтажЭлементовУкрытия',
    'ГидроПневмоИспытания',
    'МонтажСнятогоОборудования',
    'МонтажКабельнойПродукции'
];

echo "Checking problematic activities:\n";
foreach ($problematicActivities as $activity) {
    if (strpos($arrayContent, "'$activity'") !== false) {
        echo "✅ FOUND: '$activity'\n";
    } else {
        echo "❌ MISSING: '$activity'\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "If any activities are MISSING, that's the root cause!\n";
echo "The ultra-short callbacks work but fail validation.\n";
echo str_repeat("=", 50) . "\n";
?>