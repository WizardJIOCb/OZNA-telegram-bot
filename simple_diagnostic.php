<?php

echo "=== SIMPLE SERVER DIAGNOSTIC ===\n\n";

// Check if our fixes are present in the file
$tabelEntryFile = __DIR__ . '/CustomCommands/Services/TabelEntry.php';

if (!file_exists($tabelEntryFile)) {
    echo "❌ ERROR: TabelEntry.php file not found at: $tabelEntryFile\n";
    exit(1);
}

$fileContent = file_get_contents($tabelEntryFile);

echo "1. CHECKING FOR OUR FIXES:\n";

// Check for our emergency override
if (strpos($fileContent, '🚨🚨🚨 EMERGENCY OVERRIDE') !== false) {
    echo "   ✅ Emergency Override found\n";
} else {
    echo "   ❌ Emergency Override NOT found - FILE NOT UPDATED!\n";
}

// Check for our absolute protection
if (strpos($fileContent, 'createTabelMessageInternal') !== false) {
    echo "   ✅ Absolute Protection wrapper found\n";
} else {
    echo "   ❌ Absolute Protection wrapper NOT found - FILE NOT UPDATED!\n";
}

echo "\n2. FILE INFO:\n";
echo "   File size: " . filesize($tabelEntryFile) . " bytes\n";
echo "   Last modified: " . date('Y-m-d H:i:s', filemtime($tabelEntryFile)) . "\n";

echo "\n3. SEARCHING FOR PROBLEM SOURCE:\n";

// Look for the old format that's causing issues
$problemLines = [];
$lines = explode("\n", $fileContent);
foreach ($lines as $lineNum => $line) {
    if (strpos($line, 'Запись в табель от') !== false && strpos($line, 'sprintf') !== false) {
        $problemLines[] = "Line " . ($lineNum + 1) . ": " . trim($line);
    }
}

if (!empty($problemLines)) {
    echo "   ⚠️  Found old format strings:\n";
    foreach ($problemLines as $problemLine) {
        echo "   $problemLine\n";
    }
} else {
    echo "   ✅ No problematic old format strings found\n";
}

echo "\n=== RESULT ===\n";

if (strpos($fileContent, '🚨🚨🚨 EMERGENCY OVERRIDE') === false) {
    echo "❌ THE FILE ON SERVER IS NOT UPDATED!\n";
    echo "   You need to replace the TabelEntry.php file with the fixed version.\n";
} else {
    echo "✅ File contains our fixes.\n";
    echo "   The issue might be:\n";
    echo "   - PHP OPcache (needs restart)\n";
    echo "   - Different execution path\n";
    echo "   - Server pointing to different file\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Copy the updated TabelEntry.php to the server\n";
echo "2. Clear any PHP cache (OPcache)\n";
echo "3. Test the button again\n";

?>