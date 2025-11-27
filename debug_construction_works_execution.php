<?php
// Debug script to test construction works activity execution

echo "=== Debugging Construction Works Activity Execution ===\n";

// Test the pattern matching that's used in isActivityExecution method
$testCategories = [
    'cat_ТабСтройМонт_Инструктаж_yes',
    'cat_ТабСтройМонт_АвансОтчет_yes',
    'cat_ТабСтройМонт_Дорога_yes',
    'cat_ТабПодгПоездки_yes'
];

echo "Testing pattern matching for construction works activities:\n";

foreach ($testCategories as $category) {
    echo "\nTesting category: {$category}\n";
    
    // Check if it matches the short callback format for commissioning works
    if (preg_match('/^cat_(.+)_yes$/u', $category, $matches)) {
        $shortActivityKey = $matches[1];
        echo "  Pattern matched! Short activity key: {$shortActivityKey}\n";
        
        // Check if this is a construction works activity
        $constructionWorksActivities = [
            'ТабПодгПоездки' => 'СтройМонтРаботы_ПодготовкаПоездки',
            'ТабСтройМонт_Дорога' => 'СтройМонтРаботы_Дорога',
            'ТабСтройМонт_Инструктаж' => 'СтройМонтРаботы_Инструктаж',
            'ТабСтройМонт_АвансОтчет' => 'СтройМонтРаботы_АвансОтчет'
        ];
        
        if (isset($constructionWorksActivities[$shortActivityKey])) {
            echo "  ✅ Found construction works activity: {$shortActivityKey} => {$constructionWorksActivities[$shortActivityKey]}\n";
        } else {
            echo "  ❌ Not recognized as construction works activity\n";
            echo "  Available construction works activities:\n";
            foreach ($constructionWorksActivities as $key => $value) {
                echo "    - {$key} => {$value}\n";
            }
        }
    } else {
        echo "  ❌ Pattern did not match\n";
    }
}

echo "\n=== Debug Complete ===\n";
?>