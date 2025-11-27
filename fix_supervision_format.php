<?php
/**
 * FIX: Ensure supervision installation works use correct format
 */

echo "🔧 FIXING SUPERVISION INSTALLATION WORKS FORMAT\n";
echo "===============================================\n\n";

// Read the current TabelEntry.php
$tabelEntryPath = __DIR__ . '/CustomCommands/Services/TabelEntry.php';
$content = file_get_contents($tabelEntryPath);

echo "📋 CURRENT SUPERVISION INSTALLATION WORKS ARRAY:\n";

// Extract current supervision array
if (preg_match('/\$supervisionInstallationWorks\s*=\s*\[(.*?)\];/s', $content, $matches)) {
    echo "Found: " . trim($matches[0]) . "\n\n";
} else {
    echo "❌ Array not found!\n\n";
}

echo "🔍 CHECKING FOR POTENTIAL ISSUES:\n";
echo "=================================\n";

// Check if there are any issues with the supervision format check
$issues = [];

// Issue 1: Check if array exists and is properly positioned
if (strpos($content, '$supervisionInstallationWorks') === false) {
    $issues[] = "Supervision installation works array not found";
}

// Issue 2: Check if format check exists
if (strpos($content, 'in_array($activityType, $supervisionInstallationWorks)') === false) {
    $issues[] = "Format check for supervision installation works not found";
}

// Issue 3: Check position of supervision check vs other checks
$supervisionPos = strpos($content, 'in_array($activityType, $supervisionInstallationWorks)');
$automationPos = strpos($content, 'in_array($activityType, $automationEquipmentActivities)');
$defaultPos = strpos($content, 'error_log("🚨 TabelEntry: ❌ FALLING THROUGH to default format');

if ($supervisionPos && $automationPos && $supervisionPos > $automationPos) {
    $issues[] = "Supervision check comes AFTER automation check - this could cause conflicts";
}

if ($supervisionPos && $defaultPos && $supervisionPos > $defaultPos) {
    $issues[] = "Supervision check comes AFTER default fallback - will never be reached!";
}

if (empty($issues)) {
    echo "✅ No obvious issues found in the code structure.\n\n";
} else {
    echo "❌ FOUND ISSUES:\n";
    foreach ($issues as $i => $issue) {
        echo ($i + 1) . ". {$issue}\n";
    }
    echo "\n";
}

echo "🎯 MOST LIKELY CAUSE:\n";
echo "=====================\n";
echo "The supervision installation works are probably falling back to DEFAULT_FORMAT\n";
echo "because the wrong activity type is being passed to createTabelMessage().\n\n";

echo "Expected activity types:\n";
$expectedTypes = [
    'ШМ_РабПоГрафику',
    'ШМ_ПодготовкаКПоездке', 
    'ШМ_Дорога',
    'ШМ_Инструктаж',
    'ШМ_СдачаАвансовогоОтчета'
];

foreach ($expectedTypes as $type) {
    echo "- {$type}\n";
}

echo "\n💡 SOLUTION:\n";
echo "============\n";
echo "Since we can't check logs, let's add a temporary debug message\n";
echo "that will show directly in the bot what activity type is being used.\n\n";

echo "I will modify the TabelEntry.php to show debug info in the report itself.\n";

?>