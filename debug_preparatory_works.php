<?php

// Debug script to test Preparatory Works button flow
echo "=== DEBUGGING PREPARATORY WORKS BUTTON FLOW ===\n\n";

// Simulate the exact callback flow
$originalCallback = 'cat_ШМР_ПодгРаб';
echo "Original callback from button click: '{$originalCallback}'\n";
echo "Callback length: " . strlen($originalCallback) . " bytes\n";
echo "Callback hex: " . bin2hex($originalCallback) . "\n\n";

// Test the callback mapping
function extractCategoryFromCallback($callbackData) {
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format for commissioning equipment
    if (strpos($callbackData, 'cat_Таб') === 0 || strpos($callbackData, 'cat_Гарантии_') === 0 || strpos($callbackData, 'cat_ШМР_') === 0) {
        // Map short formats to full category names
        $shortFormats = [
            // NEW SUPERVISION WORKS HIERARCHICAL STRUCTURE MAPPINGS
            'cat_ШМР_ПодгРаб' => 'Табель_ПодготовительныеРаботы',
            'cat_ШМР_МонтБлок' => 'Табель_МонтажБлоков',
            'cat_ШМР_СборЗдан' => 'Табель_СборкаЗдания',
            'cat_ШМР_ВнутрМонт' => 'Табель_ВнутриблочныйМонтаж',
            'cat_ШМР_ПодпАкт' => 'Табель_ПодписаниеАктаШМР',
            'cat_ШМР_Back' => 'Табель_ШМ_Back',
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

$mappedCategory = extractCategoryFromCallback($originalCallback);
echo "Mapped category: '{$mappedCategory}'\n\n";

// Test the TabelHandler canHandle method
function canHandle($callbackData) {
    return str_starts_with($callbackData, 'category_Табель') || 
           str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
           str_starts_with($callbackData, 'cat_Таб') ||
           str_starts_with($callbackData, 'cat_Гарантии_') ||
           str_starts_with($callbackData, 'cat_ШМР_') ||  // New supervision works callbacks
           $callbackData === 'новое_прочее_yes' ||
           $callbackData === 'cat_ТабЭлОб_Проч_yes' ||
           $callbackData === 'cat_ТабВентОтопл' ||
           $callbackData === 'cat_ТабОтоплВент' ||
           str_starts_with($callbackData, 'cat_ТабВентОтопл_') ||
           str_starts_with($callbackData, 'cat_ТабОтоплВент_') ||
           $callbackData === 'cat_ТабГарантии';
}

$canHandleResult = canHandle($originalCallback);
echo "TabelHandler->canHandle('{$originalCallback}'): " . ($canHandleResult ? 'TRUE' : 'FALSE') . "\n\n";

// Test the handleOtherWorkTypes routing
function testHandleOtherWorkTypes($category) {
    echo "Testing handleOtherWorkTypes with category: '{$category}'\n";
    
    // Check if it matches the supervision works condition
    if ($category === 'Табель_ПодготовительныеРаботы') {
        echo "✅ MATCH: Preparatory works condition matched!\n";
        echo "Action: Would show preparatory works submenu\n";
        return true;
    } else {
        echo "❌ NO MATCH: Category doesn't match 'Табель_ПодготовительныеРаботы'\n";
        return false;
    }
}

$routingResult = testHandleOtherWorkTypes($mappedCategory);

echo "\n=== SUMMARY ===\n";
echo "1. Button callback: '{$originalCallback}' (" . strlen($originalCallback) . " bytes)\n";
echo "2. Callback mapping: " . ($mappedCategory ? "✅ SUCCESS" : "❌ FAILED") . " -> '{$mappedCategory}'\n";
echo "3. TabelHandler canHandle: " . ($canHandleResult ? "✅ TRUE" : "❌ FALSE") . "\n";
echo "4. handleOtherWorkTypes routing: " . ($routingResult ? "✅ SUCCESS" : "❌ FAILED") . "\n\n";

if ($mappedCategory && $canHandleResult && $routingResult) {
    echo "🎯 CONCLUSION: The flow should work correctly!\n";
    echo "📋 NEXT STEP: Check actual bot logs to see where the issue occurs\n";
} else {
    echo "⚠️  CONCLUSION: There is an issue in the flow!\n";
    if (!$mappedCategory) echo "❌ Issue: Callback mapping failed\n";
    if (!$canHandleResult) echo "❌ Issue: TabelHandler->canHandle() returned false\n";
    if (!$routingResult) echo "❌ Issue: handleOtherWorkTypes routing failed\n";
}

?>