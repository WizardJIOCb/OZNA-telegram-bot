<?php
/**
 * Test script for regex pattern matching
 * Tests if the callback data patterns match correctly
 */

echo "Testing regex pattern matching...\n\n";

// Test pattern
$pattern = '/^Табель_(.+)_yes$/';

// Test case 1: Works by schedule confirmation callback
$callback1 = 'Табель_РаботыПоГрафику_Электрооборудование_yes';
echo "1. Testing callback: {$callback1}\n";

if (preg_match($pattern, $callback1, $matches)) {
    $activityType = $matches[1];
    echo "   Extracted activity type: {$activityType}\n";
    
    // Check if this activity type is in our expected list
    $activityTypes = [
        'РаботыПоГрафику_Электрооборудование',
        'ФормированиеИД_Электрооборудование'
    ];
    
    if (in_array($activityType, $activityTypes)) {
        echo "   ✅ Activity type found in expected list\n";
    } else {
        echo "   ❌ Activity type NOT found in expected list\n";
        echo "   Expected types: " . implode(', ', $activityTypes) . "\n";
    }
} else {
    echo "   ❌ Pattern did not match\n";
}

// Test case 2: ID formation confirmation callback
$callback2 = 'Табель_ФормированиеИД_Электрооборудование_yes';
echo "\n2. Testing callback: {$callback2}\n";

if (preg_match($pattern, $callback2, $matches)) {
    $activityType = $matches[1];
    echo "   Extracted activity type: {$activityType}\n";
    
    if (in_array($activityType, $activityTypes)) {
        echo "   ✅ Activity type found in expected list\n";
    } else {
        echo "   ❌ Activity type NOT found in expected list\n";
        echo "   Expected types: " . implode(', ', $activityTypes) . "\n";
    }
} else {
    echo "   ❌ Pattern did not match\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed!\n";
echo str_repeat("=", 50) . "\n";