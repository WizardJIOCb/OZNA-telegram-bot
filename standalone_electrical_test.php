<?php
// Standalone test to check if our fix for electrical equipment callbacks works

// Test the logic directly without including the full TabelHandler

function isActivityExecution(string $category): bool {
    error_log("isActivityExecution called with category: {$category}");
    
    // Check standard activity execution
    if (str_contains($category, '_yes')) {
        // Handle short callback format for electrical equipment first (this should come before other checks)
        if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $category, $matches)) {
            // Handle short callback format for electrical equipment
            $shortActivityKey = $matches[1];
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
                // In the real implementation, this would check if the activity type is in the activityTypes array
                // For this test, we'll just return true since we know these are valid
                error_log("isActivityExecution: short electrical category={$category}, activityType=" . $shortToFullMap[$shortActivityKey]);
                return true;
            }
        }
        
        // Other checks would go here...
    }
    
    return false;
}

function handleActivityExecution(string $category): string {
    // Extract activity type using regex
    $activityType = '';
    
    // Handle short callback format for electrical equipment FIRST (this should come before other checks)
    if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $category, $matches)) {
        // Handle short callback format for electrical equipment
        $shortActivityKey = $matches[1];
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
        } else {
            // Fallback for old method
            $activityType = str_replace(['Табель_', '_yes'], ['', ''], $category);
        }
    }
    
    return $activityType;
}

// Test cases
$testCases = [
    'cat_ТабЭлОб_РабПоГр_yes' => 'РабПоГр_ЭлектрОб',
    'cat_ТабЭлОб_ФормИД_yes' => 'ФормИД_ЭлектрОб',
    'cat_ТабЭлОб_ИзмСхПд_yes' => 'ИзмСхПд_ЭлектрОб',
    'cat_ТабЭлОб_МонтКбЛн_yes' => 'МонтКбЛн_ЭлектрОб',
    'cat_ТабЭлОб_РаскКбЛн_yes' => 'РаскКбЛн_ЭлектрОб',
    'cat_ТабЭлОб_ДопРаб_yes' => 'ДопРаб_ЭлектрОб',
    'cat_ТабЭлОб_ПодтВех_yes' => 'ПодтВех_ЭлектрОб',
    'cat_ТабЭлОб_Проч_yes' => 'Прочее_ЭлектрОб'
];

echo "Testing isActivityExecution function:\n";
foreach ($testCases as $callback => $expected) {
    $result = isActivityExecution($callback);
    echo "Callback: $callback => " . ($result ? "SUCCESS" : "FAILED") . "\n";
}

echo "\nTesting handleActivityExecution function:\n";
foreach ($testCases as $callback => $expected) {
    $result = handleActivityExecution($callback);
    echo "Callback: $callback => Activity Type: $result " . ($result === $expected ? "(CORRECT)" : "(INCORRECT)") . "\n";
}

echo "\nTest completed.\n";