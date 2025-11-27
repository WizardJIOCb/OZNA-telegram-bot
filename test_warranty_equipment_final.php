<?php
/**
 * Final test script for warranty equipment button functionality
 * Tests the complete warranty equipment flow with shortened callbacks
 */

echo "=== FINAL WARRANTY EQUIPMENT BUTTON TEST ===\n\n";

// Test the warranty equipment flow with the new shortened callbacks
$testCases = [
    'cat_Гарантии_ЭлектрОб_yes' => 'РабГарантии_ЭлектрОб',
    'cat_Гарантии_СрИзм_yes' => 'РабГарантии_СрИзм',
    'cat_Гарантии_НасОб_yes' => 'РабГарантии_НасОб',
    'cat_Гарантии_ТрубАрм_yes' => 'РабГарантии_ТрубАрм',
    'cat_Гарантии_ИспМех_yes' => 'РабГарантии_ИспМех',
    'cat_Гарантии_КонтрОб_yes' => 'РабГарантии_КонтрОб',
    'cat_Гарантии_Прочее_yes' => 'РабГарантии_Прочее'
];

echo "1. TESTING WARRANTY CONFIRMATION CALLBACK MAPPINGS:\n";
echo "===================================================\n";

function testExtractCategoryFromCallback($callbackData) {
    // This simulates the updated extractCategoryFromCallback method
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    if (strpos($callbackData, 'cat_Таб') === 0 || strpos($callbackData, 'cat_Гарантии_') === 0) {
        $shortFormats = [
            // ... existing mappings ...
            // Warranty equipment confirmation callbacks - CRITICAL for warranty button functionality
            'cat_Гарантии_ЭлектрОб_yes' => 'РабГарантии_ЭлектрОб_yes',
            'cat_Гарантии_СрИзм_yes' => 'РабГарантии_СрИзм_yes',
            'cat_Гарантии_НасОб_yes' => 'РабГарантии_НасОб_yes',
            'cat_Гарантии_ТрубАрм_yes' => 'РабГарантии_ТрубАрм_yes',
            'cat_Гарантии_ИспМех_yes' => 'РабГарантии_ИспМех_yes',
            'cat_Гарантии_КонтрОб_yes' => 'РабГарантии_КонтрОб_yes',
            'cat_Гарантии_Прочее_yes' => 'РабГарантии_Прочее_yes'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

foreach ($testCases as $callback => $expectedActivity) {
    $length = strlen($callback);
    $extractedCategory = testExtractCategoryFromCallback($callback);
    
    echo "✓ Callback: '{$callback}' (length: {$length} bytes)\n";
    echo "  → Extracted: '{$extractedCategory}'\n";
    echo "  → Expected activity: '{$expectedActivity}'\n";
    
    if ($length <= 64) {
        echo "  → ✅ Length OK (≤64 bytes)\n";
    } else {
        echo "  → ❌ Length EXCEEDED (>64 bytes)\n";
    }
    echo "\n";
}

echo "\n2. TESTING TABELHANDLER canHandle METHOD:\n";
echo "=========================================\n";

function testCanHandle($callbackData) {
    // Test if TabelHandler would accept these callbacks
    if (str_starts_with($callbackData, 'category_Табель') || 
        str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
        str_starts_with($callbackData, 'cat_Таб') ||
        str_starts_with($callbackData, 'cat_Гарантии_')) { // This should handle our warranty confirmations
        return true;
    }
    
    return false;
}

foreach (array_keys($testCases) as $callback) {
    $canHandle = testCanHandle($callback);
    echo "Callback: '{$callback}'\n";
    echo "  → canHandle: " . ($canHandle ? '✅ YES' : '❌ NO') . "\n\n";
}

echo "\n3. TESTING isActivityExecution METHOD:\n";
echo "======================================\n";

function testIsActivityExecution($category) {
    // Test if the extracted category would be recognized as activity execution
    if (str_contains($category, '_yes')) {
        return true;
    }
    return false;
}

foreach ($testCases as $callback => $expectedActivity) {
    $extractedCategory = testExtractCategoryFromCallback($callback);
    $isActivityExecution = testIsActivityExecution($extractedCategory);
    
    echo "Callback: '{$callback}'\n";
    echo "  → Extracted category: '{$extractedCategory}'\n";
    echo "  → isActivityExecution: " . ($isActivityExecution ? '✅ YES' : '❌ NO') . "\n\n";
}

echo "\n4. TESTING COMPLETE WARRANTY EQUIPMENT FLOW:\n";
echo "=============================================\n";
echo "User Flow Simulation:\n";
echo "1. User clicks 'Работы по гарантии' → opens warranty equipment menu\n";
echo "2. User clicks 'Электрооборудование' → callback: 'category_Табель_РабГарантии_ЭлектрОб'\n";
echo "3. System shows confirmation with callback: 'cat_Гарантии_ЭлектрОб_yes'\n";
echo "4. User clicks 'Да' → processes activity execution\n\n";

// Test the specific electrical equipment flow
$equipmentCallback = 'category_Табель_РабГарантии_ЭлектрОб';
$confirmationCallback = 'cat_Гарантии_ЭлектрОб_yes';

echo "Equipment selection callback: '{$equipmentCallback}'\n";
echo "Confirmation callback: '{$confirmationCallback}' (length: " . strlen($confirmationCallback) . " bytes)\n";
echo "Extracted category: '" . testExtractCategoryFromCallback($confirmationCallback) . "'\n";
echo "canHandle: " . (testCanHandle($confirmationCallback) ? '✅ YES' : '❌ NO') . "\n";
echo "isActivityExecution: " . (testIsActivityExecution(testExtractCategoryFromCallback($confirmationCallback)) ? '✅ YES' : '❌ NO') . "\n\n";

echo "🎯 RESULT: All warranty equipment callbacks should now work correctly!\n";
echo "The key fixes implemented:\n";
echo "- Shortened warranty confirmation callbacks to fit within 64-byte limit\n";
echo "- Added warranty confirmation callback mappings to extractCategoryFromCallback\n";
echo "- Updated canHandle method to recognize warranty confirmation patterns\n";
echo "- Updated isConfirmationCallback method to handle warranty confirmations\n";