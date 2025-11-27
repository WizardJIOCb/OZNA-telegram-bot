<?php

echo "=== Verification of Supervision Installation Works Fix ===\n\n";

// Read the current TabelHandler content
$content = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

// Find both positions more accurately
$lines = explode("\n", $content);
$submenuLineNumber = -1;
$activityTypeLineNumber = -1;

foreach ($lines as $lineNumber => $line) {
    // Look for the submenu handler
    if (strpos($line, "if (\$category === 'Табель_ШМ_ШМР')") !== false) {
        $submenuLineNumber = $lineNumber + 1; // Convert to 1-based line numbers
    }
    
    // Look for the activity type check
    if (strpos($line, "\$strippedCategory = str_replace('Табель_', '', \$category);") !== false) {
        $activityTypeLineNumber = $lineNumber + 1;
    }
}

echo "1. Current positions in file:\n";
echo "   Submenu handler: line {$submenuLineNumber}\n";
echo "   Activity type check: line {$activityTypeLineNumber}\n";

if ($submenuLineNumber > 0 && $activityTypeLineNumber > 0) {
    if ($submenuLineNumber < $activityTypeLineNumber) {
        echo "   ✅ CORRECT ORDER: Submenu handler comes before activity type check\n";
        echo "   The fix should now work!\n";
    } else {
        echo "   ❌ WRONG ORDER: Activity type check still comes before submenu handler\n";
        echo "   The fix did not work properly\n";
    }
} else {
    echo "   ❌ Could not find one or both handlers\n";
    echo "   Submenu found: " . ($submenuLineNumber > 0 ? "YES" : "NO") . "\n";
    echo "   Activity type found: " . ($activityTypeLineNumber > 0 ? "YES" : "NO") . "\n";
}

// Check the context around the submenu handler
echo "\n2. Context around submenu handler:\n";
if ($submenuLineNumber > 0) {
    $startLine = max(0, $submenuLineNumber - 3);
    $endLine = min(count($lines) - 1, $submenuLineNumber + 2);
    
    for ($i = $startLine; $i <= $endLine; $i++) {
        $marker = ($i == $submenuLineNumber - 1) ? " >>> " : "     ";
        echo $marker . "Line " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
    }
}

// Check the context around the activity type check
echo "\n3. Context around activity type check:\n";
if ($activityTypeLineNumber > 0) {
    $startLine = max(0, $activityTypeLineNumber - 3);
    $endLine = min(count($lines) - 1, $activityTypeLineNumber + 2);
    
    for ($i = $startLine; $i <= $endLine; $i++) {
        $marker = ($i == $activityTypeLineNumber - 1) ? " >>> " : "     ";
        echo $marker . "Line " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
    }
}

echo "\n4. Final assessment:\n";
if ($submenuLineNumber > 0 && $activityTypeLineNumber > 0 && $submenuLineNumber < $activityTypeLineNumber) {
    echo "   🎉 SUCCESS: The routing order has been fixed!\n";
    echo "   Now when 'category_Табель_ШМ_ШМР' is processed:\n";
    echo "   1. It will match the submenu condition FIRST\n";
    echo "   2. Call handleSupervisionInstallationWorksMenu()\n";
    echo "   3. Show the 5 sub-activities menu\n";
    echo "   4. Never reach the activity type check\n";
} else {
    echo "   ❌ The fix may not be complete\n";
    echo "   Further investigation needed\n";
}

echo "\n=== Verification Complete ===\n";