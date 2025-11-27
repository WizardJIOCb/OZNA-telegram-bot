<?php
// Detailed debug script to test construction works activities

echo "=== Detailed Construction Works Activities Debug ===\n";

// Test the pattern matching that's used in isActivityExecution method
$category = 'cat_ТабСтройМонт_Инструктаж_yes';

echo "Testing category: {$category}\n";

// Check if the pattern matches
if (preg_match('/^cat_(.+)_yes$/u', $category, $matches)) {
    $shortActivityKey = $matches[1];
    echo "Pattern matched! Short activity key: {$shortActivityKey}\n";
    
    // Check if this matches our construction works activities
    if ($shortActivityKey === 'ТабСтройМонт_Инструктаж') {
        echo "✅ This matches construction works instruction activity\n";
    }
    
    // Check the construction works activities
    $constructionWorksActivities = [
        'ТабСтройМонт_Инструктаж' => 'СтройМонтРаботы_Инструктаж',
        'ТабСтройМонт_АвансОтчет' => 'СтройМонтРаботы_АвансОтчет',
        'ТабСтройМонт_Дорога' => 'СтройМонтРаботы_Дорога',
        'ТабПодгПоездки' => 'СтройМонтРаботы_ПодготовкаПоездки'
    ];
    
    if (isset($constructionWorksActivities[$shortActivityKey])) {
        echo "✅ Found mapping: {$shortActivityKey} => {$constructionWorksActivities[$shortActivityKey]}\n";
    } else {
        echo "❌ No mapping found for: {$shortActivityKey}\n";
        echo "Available mappings:\n";
        foreach ($constructionWorksActivities as $key => $value) {
            echo "  - {$key} => {$value}\n";
        }
    }
} else {
    echo "❌ Pattern did not match\n";
}

// Test another category
$category2 = 'cat_ТабСтройМонт_АвансОтчет_yes';
echo "\nTesting category: {$category2}\n";

if (preg_match('/^cat_(.+)_yes$/u', $category2, $matches)) {
    $shortActivityKey = $matches[1];
    echo "Pattern matched! Short activity key: {$shortActivityKey}\n";
    
    $constructionWorksActivities = [
        'ТабСтройМонт_Инструктаж' => 'СтройМонтРаботы_Инструктаж',
        'ТабСтройМонт_АвансОтчет' => 'СтройМонтРаботы_АвансОтчет',
        'ТабСтройМонт_Дорога' => 'СтройМонтРаботы_Дорога',
        'ТабПодгПоездки' => 'СтройМонтРаботы_ПодготовкаПоездки'
    ];
    
    if (isset($constructionWorksActivities[$shortActivityKey])) {
        echo "✅ Found mapping: {$shortActivityKey} => {$constructionWorksActivities[$shortActivityKey]}\n";
    } else {
        echo "❌ No mapping found for: {$shortActivityKey}\n";
    }
} else {
    echo "❌ Pattern did not match\n";
}

echo "\n=== Debug Complete ===\n";
?>