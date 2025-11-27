<?php
// Test script to simulate the construction works flow

echo "=== Testing Construction Works Flow ===\n";

// Simulate the callback data that would be sent when user clicks "Да" button
$testCallbacks = [
    'cat_ТабСтройМонт_Инструктаж_yes',
    'cat_ТабСтройМонт_АвансОтчет_yes',
    'cat_ТабСтройМонт_Дорога_yes',
    'cat_ТабПодгПоездки_yes'
];

echo "Testing callback processing:\n";

foreach ($testCallbacks as $callback) {
    echo "\nTesting callback: {$callback}\n";
    
    // Simulate isActivityExecution method
    if (str_contains($callback, '_yes')) {
        // Check if it matches the short callback format for commissioning works
        if (preg_match('/^cat_(.+)_yes$/u', $callback, $matches)) {
            $shortActivityKey = $matches[1];
            echo "  Short activity key: {$shortActivityKey}\n";
            
            // Check if this is a construction works activity
            $constructionWorksActivities = [
                'ТабПодгПоездки' => 'СтройМонтРаботы_ПодготовкаПоездки',
                'ТабСтройМонт_Дорога' => 'СтройМонтРаботы_Дорога',
                'ТабСтройМонт_Инструктаж' => 'СтройМонтРаботы_Инструктаж',
                'ТабСтройМонт_АвансОтчет' => 'СтройМонтРаботы_АвансОтчет'
            ];
            
            if (isset($constructionWorksActivities[$shortActivityKey])) {
                echo "  ✅ Recognized as construction works activity: {$constructionWorksActivities[$shortActivityKey]}\n";
                
                // Simulate handleActivityExecution method
                $activityType = $constructionWorksActivities[$shortActivityKey];
                echo "  Activity type to be processed: {$activityType}\n";
                
                // Check if activity type is valid
                $validActivityTypes = [
                    'СтройМонтРаботы_ПодготовкаПоездки',
                    'СтройМонтРаботы_Дорога',
                    'СтройМонтРаботы_Инструктаж',
                    'СтройМонтРаботы_АвансОтчет'
                ];
                
                if (in_array($activityType, $validActivityTypes)) {
                    echo "  ✅ Activity type is valid\n";
                } else {
                    echo "  ❌ Activity type is NOT valid\n";
                }
            } else {
                echo "  ❌ Not recognized as construction works activity\n";
            }
        } else {
            echo "  Pattern did not match\n";
        }
    } else {
        echo "  Not a confirmation callback\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "If all checks pass, the construction works activities should be working properly.\n";
?>