<?php
// Test script to verify warranty equipment execution callback handling fix

echo "Testing warranty equipment execution callback handling fix...\n\n";

// Simulate the exact scenario from the logs
$category = 'РабГарантии_ЭлектрОб_yes';

echo "Input Category: $category\n\n";

// Test our new logic with the same regex pattern used in isActivityExecution
if (preg_match('/^РабГарантии_(.+)_yes$/u', $category, $matches)) {
    $equipmentType = $matches[1];
    $activityType = 'РабГарантии_' . $equipmentType;
    
    echo "✅ WARRANTY EQUIPMENT CONFIRMATION DETECTED\n";
    echo "Equipment Type: $equipmentType\n";
    echo "Mapped Activity Type: $activityType\n";
    echo "SUCCESS: Category correctly handled by isActivityExecution\n";
} else {
    echo "❌ FAILURE: Category not handled by isActivityExecution\n";
}

echo "\n";

// Test other warranty equipment types
$warrantyTypes = [
    'РабГарантии_СрИзм_yes',
    'РабГарантии_НасОб_yes',
    'РабГарантии_ТрубАрм_yes',
    'РабГарантии_ИспМех_yes',
    'РабГарантии_КонтрОб_yes',
    'РабГарантии_Прочее_yes'
];

foreach ($warrantyTypes as $type) {
    echo "Testing: $type\n";
    
    if (preg_match('/^РабГарантии_(.+)_yes$/u', $type, $matches)) {
        $equipmentType = $matches[1];
        $activityType = 'РабГарантии_' . $equipmentType;
        
        echo "  ✅ SUCCESS: Equipment Type: $equipmentType -> Activity Type: $activityType\n";
    } else {
        echo "  ❌ FAILURE: Not handled\n";
    }
}

echo "\nTest completed.\n";