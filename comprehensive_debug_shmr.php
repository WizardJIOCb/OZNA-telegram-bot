<?php

echo "=== COMPREHENSIVE DEBUG: ШМР Routing Issue ===\n\n";

echo "1. CALLBACK FLOW ANALYSIS:\n";
$callbackData = 'category_Табель_ШМ_ШМР';
echo "   Original callback: {$callbackData}\n";

// Extract category like TabelHandler does
$category = substr($callbackData, strlen('category_'));
echo "   Extracted category: {$category}\n";

echo "\n2. HANDLER CHECKS (in order):\n";

// Check 1: Supervision Work Types (lines 2259-2267)
$supervisionWorkTypes = ['Табель_ШМР', 'Табель_ШМ_Простой'];
if (in_array($category, $supervisionWorkTypes)) {
    echo "   ❌ CAUGHT by supervision work types handler (lines 2259-2267)\n";
    echo "   This would show activity confirmation (creates report)!\n";
    exit;
} else {
    echo "   ✅ NOT caught by supervision work types handler\n";
    echo "   Category '{$category}' not in " . json_encode($supervisionWorkTypes) . "\n";
}

// Check 2: Supervision Project Activity (line 2272)
if ($category === 'Табель_ШМ_ПроектнаяДеят') {
    echo "   ❌ CAUGHT by supervision project activity handler\n";
    exit;
} else {
    echo "   ✅ NOT caught by supervision project activity handler\n";
}

// Check 3: Supervision Installation Works (line 2281) - THE CORRECT ONE
if ($category === 'Табель_ШМ_ШМР') {
    echo "   ✅ SHOULD BE CAUGHT by supervision installation works handler (line 2281)\n";
    echo "   This should show the submenu with 5 sub-activities\n";
} else {
    echo "   ❌ NOT caught by supervision installation works handler\n";
}

echo "\n3. ACTIVITY TYPES CHECK:\n";
$strippedCategory = str_replace('Табель_', '', $category);
echo "   Stripped category: '{$strippedCategory}'\n";

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
    
    if (in_array($strippedCategory, $activityTypes)) {
        echo "   ❌ PROBLEM: '{$strippedCategory}' is in activityTypes array!\n";
        echo "   This would trigger activity confirmation instead of submenu!\n";
    } else {
        echo "   ✅ Good: '{$strippedCategory}' is NOT in activityTypes array\n";
    }
} else {
    echo "   ❌ Could not extract activityTypes array\n";
}

echo "\n4. CONCLUSION:\n";
if ($category === 'Табель_ШМ_ШМР') {
    echo "   The callback 'category_Табель_ШМ_ШМР' should reach the submenu handler\n";
    echo "   at line 2281 and show the 5 sub-activities.\n";
    echo "   If it's still creating a report, there might be a different issue.\n";
} else {
    echo "   There's a routing problem - the category doesn't match expected patterns.\n";
}

?>