<?php

echo "=== Comprehensive Debug for Supervision Installation Works Routing ===\n\n";

// Test the complete callback flow
$callback = 'category_Табель_ШМ_ШМР';
echo "1. Testing callback: {$callback}\n\n";

// Read the TabelHandler content
$content = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

// Check if ШМ_ШМР is being excluded from activity type checks
echo "2. Checking if ШМ_ШМР should be excluded from activity type checks:\n";

// Look for the exact condition around the activity type check
$lines = explode("\n", $content);
$foundActivityCheck = false;
$foundSubmenuHandler = false;

foreach ($lines as $lineNum => $line) {
    // Look for the activity type check section
    if (strpos($line, "in_array(\$strippedCategory, \$this->activityTypes)") !== false) {
        echo "   Activity type check found at line " . ($lineNum + 1) . "\n";
        
        // Show context around this line
        echo "   Context:\n";
        for ($i = max(0, $lineNum - 5); $i <= min(count($lines) - 1, $lineNum + 10); $i++) {
            $marker = ($i == $lineNum) ? " >>> " : "     ";
            echo $marker . "Line " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
        }
        $foundActivityCheck = true;
        break;
    }
}

echo "\n3. Looking for possible problems:\n";

// Check if ШМ_ШМР might be matching the activity type check before the submenu handler
$activityTypesPattern = '/activityTypes\s*=\s*\[(.*?)\]/s';
if (preg_match($activityTypesPattern, $content, $matches)) {
    $activityTypesString = $matches[1];
    if (strpos($activityTypesString, "'ШМ_ШМР'") !== false) {
        echo "   ⚠️ PROBLEM: 'ШМ_ШМР' IS in activityTypes array\n";
        echo "   This means the callback will be processed as:\n";
        echo "   1. 'category_Табель_ШМ_ШМР' → 'Табель_ШМ_ШМР'\n";
        echo "   2. Strip 'Табель_' → 'ШМ_ШМР'\n";
        echo "   3. Check if 'ШМ_ШМР' is in activityTypes → YES (matches!)\n";
        echo "   4. Show confirmation dialog (wrong behavior)\n\n";
        
        echo "   SOLUTION: Need to exclude 'ШМ_ШМР' from the activity type check\n";
        echo "   or ensure the submenu handler comes before the activity type check\n";
    } else {
        echo "   ✅ 'ШМ_ШМР' not found in activityTypes array\n";
    }
}

// Check if there are multiple places where activity confirmation might occur
echo "\n4. Checking for multiple activity confirmation points:\n";

$confirmationPatterns = [
    'in_array.*activityTypes',
    'isActivityConfirmation',
    'activity.*confirmation',
    'strippedCategory.*activityTypes'
];

foreach ($confirmationPatterns as $pattern) {
    $matches = preg_grep("/{$pattern}/i", $lines);
    if (!empty($matches)) {
        echo "   Found pattern '{$pattern}' in " . count($matches) . " places:\n";
        foreach ($matches as $lineNum => $line) {
            echo "     Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
        echo "\n";
    }
}

echo "5. Recommendation:\n";
echo "   The most likely fix is to remove 'ШМ_ШМР' from the activityTypes array\n";
echo "   since it should be handled as a submenu, not as a direct activity.\n";
echo "   Submenu items should NOT be in the activityTypes array.\n";

echo "\n=== Debug Complete ===\n";