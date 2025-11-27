<?php
echo "=== VERIFICATION: ШМР Header Text Fix ===\n\n";

// Check TabelHandler.php for the updated menu header
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "Checking TabelHandler.php menu header...\n";

// Look for the updated text without ШМР
if (strpos($tabelHandlerContent, 'Проектная деятельность для проекта') !== false) {
    echo "✅ SUCCESS: Menu header updated correctly\n";
    echo "   New text: 'Проектная деятельность для проекта {projectCode}'\n";
} else {
    echo "❌ ISSUE: Menu header not found or not updated\n";
}

// Check for any remaining "ШМР" in menu headers
if (strpos($tabelHandlerContent, 'Проектная деятельность ШМР') !== false) {
    echo "❌ WARNING: Still found 'Проектная деятельность ШМР' in code\n";
} else {
    echo "✅ CLEAN: No remaining 'Проектная деятельность ШМР' found\n";
}

echo "\nChecking TabelEntry.php comments...\n";

// Check TabelEntry.php comments
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

$oldCommentPattern = 'Проектная деятельность ШМР';
$newCommentPattern = 'Проектная деятельность';

if (strpos($tabelEntryContent, $oldCommentPattern) !== false) {
    echo "❌ WARNING: Still found old comment format in TabelEntry.php\n";
} else {
    echo "✅ CLEAN: Comments updated in TabelEntry.php\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "The menu header should now show:\n";
echo "📂 Проектная деятельность для проекта ОИ768\n\n";
echo "Instead of:\n";
echo "📂 Проектная деятельность ШМР для проекта ОИ768\n";