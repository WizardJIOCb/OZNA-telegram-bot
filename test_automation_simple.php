<?php
// Simple test script to check the automation equipment functionality
echo "=== Testing Automation Equipment Functionality ===\n";

// Test the callback data patterns
$callbackData1 = 'category_Табель_Автоматизация';
$callbackData2 = 'category_Табель_РабПоГр_Автоматизация';
$callbackData3 = 'category_Табель_РабПоГр_Автом_yes';

echo "Callback data 1: $callbackData1\n";
echo "Callback data 2: $callbackData2\n";
echo "Callback data 3: $callbackData3\n";

// Test pattern matching for activity execution
function isActivityExecution($category) {
    echo "Checking isActivityExecution for: $category\n";
    
    // Check standard activity execution
    if (strpos($category, '_yes') !== false) {
        // Extract the activity type from the category
        $pattern = '/^Табель_(.+)_yes$/';
        if (preg_match($pattern, $category, $matches)) {
            $activityType = $matches[1];
            echo "Extracted activity type: $activityType\n";
            
            // Check if this is an automation equipment activity
            $automationActivities = [
                'РабПоГр_Автом',
                'ФормИД_Автом',
                'ПодтВех_Автом',
                'ДопРаб_Автом',
                'ИзмСх_Автом',
                'МонтКб_Автом',
                'РаскКб_Автом',
                'МонтАСУ_Автом',
                'УдалПО_Автом',
                'Прочее_Автом'
            ];
            
            $result = in_array($activityType, $automationActivities);
            echo "Activity type in automation activities: " . ($result ? 'true' : 'false') . "\n";
            return $result;
        }
    }
    
    return false;
}

// Test with confirmation callback
$result = isActivityExecution($callbackData3);
echo "isActivityExecution result: " . ($result ? 'true' : 'false') . "\n";

// Test category extraction
function extractCategory($callbackData) {
    echo "Extracting category from: $callbackData\n";
    
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        echo "Extracted category: $category\n";
        return $category;
    }
    
    return $callbackData;
}

$category1 = extractCategory($callbackData1);
$category2 = extractCategory($callbackData2);
$category3 = extractCategory($callbackData3);

echo "Category 1: $category1\n";
echo "Category 2: $category2\n";
echo "Category 3: $category3\n";

// Test if categories match expected values
$expectedCategory1 = 'Табель_Автоматизация';
$expectedCategory2 = 'Табель_РабПоГр_Автоматизация';
$expectedCategory3 = 'Табель_РабПоГр_Автом_yes';

echo "Category 1 matches expected: " . ($category1 === $expectedCategory1 ? 'true' : 'false') . "\n";
echo "Category 2 matches expected: " . ($category2 === $expectedCategory2 ? 'true' : 'false') . "\n";
echo "Category 3 matches expected: " . ($category3 === $expectedCategory3 ? 'true' : 'false') . "\n";

echo "\nTest completed!\n";
?>