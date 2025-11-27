<?php
// Simple test to verify the logic for "Сварочные" activity type

// Simulate the isActivityExecution method logic
function testIsActivityExecution($category) {
    // Check standard activity execution
    if (strpos($category, '_yes') !== false) {
        // Extract the activity type from the category
        $pattern = '/^Табель_(.+)_yes$/';
        if (preg_match($pattern, $category, $matches)) {
            $activityType = $matches[1];
            
            // Special handling for сварочные works due to encoding issues
            if ($activityType === 'Сварочные') {
                echo "Special handling for сварочные works\n";
                return true;
            }
            
            // This would be the normal check against activityTypes array
            // For this test, we'll just show what would happen
            echo "Checking if '{$activityType}' is in activityTypes array\n";
            return false; // Simulate not found in array
        }
    }
    
    return false;
}

// Test the "Сварочные" activity execution
$testCategory = 'Табель_Сварочные_yes';
$result = testIsActivityExecution($testCategory);

echo "Testing isActivityExecution with category: {$testCategory}\n";
echo "Result: " . ($result ? 'true' : 'false') . "\n";

// Test another category for comparison
$testCategory2 = 'Табель_Электромонтажные_yes';
$result2 = testIsActivityExecution($testCategory2);

echo "\nTesting isActivityExecution with category: {$testCategory2}\n";
echo "Result: " . ($result2 ? 'true' : 'false') . "\n";
?>