<?php

echo "=== DEBUG: ШМР Routing Issue ===\n\n";

echo "1. CALLBACK ANALYSIS:\n";
$callbackData = 'category_Табель_ШМ_ШМР';
echo "   Original callback: {$callbackData}\n";

// Extract category like TabelHandler does
$category = substr($callbackData, strlen('category_'));
echo "   Extracted category: {$category}\n";

// Strip Табель_ prefix
$strippedCategory = str_replace('Табель_', '', $category);
echo "   Stripped category: {$strippedCategory}\n";

echo "\n2. ACTIVITY TYPES CHECK:\n";
// Read the actual activityTypes from TabelHandler.php
$fileContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

// Extract the activityTypes array
if (preg_match('/private array \$activityTypes = \[(.*?)\];/s', $fileContent, $matches)) {
    $activityTypesString = $matches[1];
    // Remove comments and clean up
    $activityTypesString = preg_replace('/\/\/.*$/m', '', $activityTypesString);
    $activityTypesString = str_replace(["\n", "\r", "\t", ' '], '', $activityTypesString);
    
    // Extract individual activity types
    preg_match_all("/'([^']+)'/", $activityTypesString, $typeMatches);
    $activityTypes = $typeMatches[1];
    
    echo "   Activity types array contains " . count($activityTypes) . " items\n";
    
    // Check if ШМР is in the array
    if (in_array('ШМР', $activityTypes)) {
        echo "   ❌ PROBLEM: 'ШМР' is in activityTypes array!\n";
    } else {
        echo "   ✅ Good: 'ШМР' is NOT in activityTypes array\n";
    }
    
    // Check what our stripped category would match
    if (in_array($strippedCategory, $activityTypes)) {
        echo "   ❌ PROBLEM: '{$strippedCategory}' is in activityTypes array!\n";
        echo "   This would trigger activity confirmation instead of submenu!\n";
    } else {
        echo "   ✅ Good: '{$strippedCategory}' is NOT in activityTypes array\n";
    }
    
} else {
    echo "   ❌ Could not extract activityTypes array from TabelHandler.php\n";
}

echo "\n3. ROUTING LOGIC SIMULATION:\n";
echo "   The callback 'category_Табель_ШМ_ШМР' should:\n";
echo "   1. Extract category: 'Табель_ШМ_ШМР'\n";
echo "   2. Look for specific submenu handler\n";
echo "   3. If found, show submenu (good)\n";
echo "   4. If not found, check activityTypes array\n";
echo "   5. If in activityTypes, show confirmation dialog (BAD - creates report)\n";

echo "\n4. ANALYSIS:\n";
if (isset($activityTypes) && in_array($strippedCategory, $activityTypes)) {
    echo "   ❌ ROOT CAUSE: '{$strippedCategory}' is incorrectly in activityTypes array\n";
    echo "   SOLUTION: Remove '{$strippedCategory}' from activityTypes array\n";
} else {
    echo "   ❓ Need to check submenu handler logic\n";
}

echo "\n5. CHECKING SPECIFIC SUBMENU HANDLER:\n";
// Check if there's a handler for 'Табель_ШМ_ШМР'
if (strpos($fileContent, "category === 'Табель_ШМ_ШМР'") !== false) {
    echo "   ✅ Submenu handler found for 'Табель_ШМ_ШМР'\n";
} else {
    echo "   ❌ No submenu handler found for 'Табель_ШМ_ШМР'\n";
}

?>