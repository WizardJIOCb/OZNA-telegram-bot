<?php
// Debug script to test regex pattern matching

$category = 'ТабЭлОб_РабПоГр_yes';

echo "Testing category: {$category}\n";

// Check if it contains '_yes'
if (str_contains($category, '_yes')) {
    echo "Contains '_yes': true\n";
    
    // Test the regex pattern
    if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $category, $matches)) {
        echo "Matches pattern '/^cat_ТабЭлОб_(.+)_yes$/': true\n";
        echo "Matches: " . print_r($matches, true) . "\n";
    } else {
        echo "Matches pattern '/^cat_ТабЭлОб_(.+)_yes$/': false\n";
    }
    
    // Test a modified pattern that might work
    if (preg_match('/^ТабЭлОб_(.+)_yes$/', $category, $matches)) {
        echo "Matches pattern '/^ТабЭлОб_(.+)_yes$/': true\n";
        echo "Matches: " . print_r($matches, true) . "\n";
        
        $shortActivityKey = $matches[1];
        echo "Short activity key: {$shortActivityKey}\n";
        
        // Map short format to full activity type
        $shortToFullMap = [
            'РабПоГр' => 'РабПоГр_ЭлектрОб',
            'ФормИД' => 'ФормИД_ЭлектрОб',
            'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
            'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
            'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
            'ДопРаб' => 'ДопРаб_ЭлектрОб',
            'ПодтВех' => 'ПодтВех_ЭлектрОб',
            'Проч' => 'Прочее_ЭлектрОб'
        ];
        
        if (isset($shortToFullMap[$shortActivityKey])) {
            $activityType = $shortToFullMap[$shortActivityKey];
            echo "Activity type: {$activityType}\n";
            
            // Check if this activity type is in the activityTypes array
            $activityTypes = [
                'РабПоГр_ЭлектрОб',
                'ФормИД_ЭлектрОб',
                'ПодтВех_ЭлектрОб',
                'ДопРаб_ЭлектрОб',
                'ИзмСхПд_ЭлектрОб',
                'МонтКбЛн_ЭлектрОб',
                'РаскКбЛн_ЭлектрОб',
                'Прочее_ЭлектрОб'
            ];
            
            $result = in_array($activityType, $activityTypes);
            echo "In activityTypes: " . ($result ? 'true' : 'false') . "\n";
        } else {
            echo "No mapping found for short activity key: {$shortActivityKey}\n";
        }
    } else {
        echo "Matches pattern '/^ТабЭлОб_(.+)_yes$/': false\n";
    }
} else {
    echo "Contains '_yes': false\n";
}
?>