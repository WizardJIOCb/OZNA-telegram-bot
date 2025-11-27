<?php
// Test the extractCategoryFromCallback logic directly

function extractCategoryFromCallback($callbackData) {
    echo "Testing callbackData: '$callbackData'\n";
    
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        $extracted = substr($callbackData, strlen('category_'));
        echo "Matched category_ prefix, extracted: '$extracted'\n";
        return $extracted;
    }
    
    // Handle direct callback names (like back buttons)
    $directCallbacks = [
        'Табель_ШМ_Back',
        'Табель_Пусконаладка_Back',
        'Табель_Автоматизация_Back',
        'Табель_ПожарнаяСигнализация_Back',
        'Табель_ОхраннаяСигнализация_Back',
        'Табель_ОтоплениеИВентиляция_Back',
        'Табель_ТехнологическоеОборудование_Back',
        'Табель_Метрология_Back',
        'Табель_ПроектнаяДеятельность_Back'
    ];
    
    echo "Checking if callbackData is in directCallbacks...\n";
    echo "directCallbacks array: " . json_encode($directCallbacks) . "\n";
    
    if (in_array($callbackData, $directCallbacks)) {
        echo "Found match in directCallbacks, returning: '$callbackData'\n";
        return $callbackData;
    } else {
        echo "No match found in directCallbacks\n";
    }
    
    // Handle short category format for commissioning equipment
    if (strpos($callbackData, 'cat_Таб') === 0 || strpos($callbackData, 'cat_Гарантии_') === 0 || strpos($callbackData, 'cat_ШМР_') === 0) {
        echo "Matched short format prefix\n";
        // Map short formats to full category names
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование'
            // ... (other mappings would be here)
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    echo "No matching condition found, returning empty string\n";
    return '';
}

// Test the specific case
$testCallback = 'Табель_ШМ_Back';
$result = extractCategoryFromCallback($testCallback);
echo "\nFinal result: '$result'\n";

if ($result === $testCallback) {
    echo "✅ SUCCESS: The callback was properly extracted!\n";
} else {
    echo "❌ FAILURE: The callback was not properly extracted.\n";
}
?>