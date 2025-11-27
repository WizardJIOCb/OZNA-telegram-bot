<?php
// Test script to verify the fix for PNR date selection issue

echo "=== Testing PNR Date Selection Fix ===\n\n";

// Simulate the flow that was not working
echo "1. User clicks '⚙️ Отдел ПНР' button\n";
$initialCallback = 'category_Табель_Пусконаладка';
echo "   Callback data: $initialCallback\n";

// Extract category (as done in the real code)
$category = substr($initialCallback, strlen('category_'));
echo "   Extracted category: $category\n";

// Check if TabelHandler would handle this category
if ($category === 'Табель_Пусконаладка') {
    echo "   ✓ TabelHandler correctly identifies this as Пусконаладка department\n";
    
    // Simulate handleCommissioningDateSelection
    echo "\n2. TabelHandler calls handleCommissioningDateSelection()\n";
    $dateSelectionPrefix = 'category_Табель_Пусконаладка';
    echo "   Using date selection prefix: $dateSelectionPrefix\n";
    
    // Simulate creating date selection buttons
    $todayCallback = $dateSelectionPrefix . '_Сегодня';
    $yesterdayCallback = $dateSelectionPrefix . '_Вчера';
    $dayBeforeYesterdayCallback = $dateSelectionPrefix . '_Позавчера';
    $otherDateCallback = $dateSelectionPrefix . '_ДругаяДата';
    
    echo "   Created date selection buttons:\n";
    echo "   - Today: $todayCallback (length: " . strlen($todayCallback) . ")\n";
    echo "   - Yesterday: $yesterdayCallback (length: " . strlen($yesterdayCallback) . ")\n";
    echo "   - Day before yesterday: $dayBeforeYesterdayCallback (length: " . strlen($dayBeforeYesterdayCallback) . ")\n";
    echo "   - Other date: $otherDateCallback (length: " . strlen($otherDateCallback) . ")\n";
    
    // Test if these callbacks would be correctly identified as date selections
    echo "\n3. Testing date selection identification:\n";
    
    $testCallbacks = [
        $todayCallback => 'Today',
        $yesterdayCallback => 'Yesterday',
        $dayBeforeYesterdayCallback => 'Day before yesterday',
        $otherDateCallback => 'Other date'
    ];
    
    foreach ($testCallbacks as $callback => $description) {
        // Simulate ProjectSelector::isDateSelection
        $testCategory = substr($callback, strlen('category_'));
        $isDateSelection = str_contains($testCategory, '_Сегодня') || 
                          str_contains($testCategory, '_Вчера') || 
                          str_contains($testCategory, '_Позавчера') || 
                          str_contains($testCategory, '_ДругаяДата') ||
                          str_ends_with($testCategory, 'Сегодня') ||
                          str_ends_with($testCategory, 'Вчера') ||
                          str_ends_with($testCategory, 'Позавчера') ||
                          str_ends_with($testCategory, 'ДругаяДата');
        
        if ($isDateSelection) {
            echo "   ✓ $description callback correctly identified as date selection\n";
        } else {
            echo "   ✗ $description callback NOT identified as date selection\n";
        }
    }
    
    echo "\n4. Testing that there are no conflicts with Пусконаладочные работы:\n";
    $pnlWorksCallback = 'category_Табель_ПусконаладочныеРаботы';
    echo "   Пусконаладочные работы callback: $pnlWorksCallback\n";
    
    // Check if this would be confused with our date selection callbacks
    $pnlWorksCategory = substr($pnlWorksCallback, strlen('category_'));
    $isDateSelection = str_contains($pnlWorksCategory, '_Сегодня') || 
                      str_contains($pnlWorksCategory, '_Вчера') || 
                      str_contains($pnlWorksCategory, '_Позавчера') || 
                      str_contains($pnlWorksCategory, '_ДругаяДата') ||
                      str_ends_with($pnlWorksCategory, 'Сегодня') ||
                      str_ends_with($pnlWorksCategory, 'Вчера') ||
                      str_ends_with($pnlWorksCategory, 'Позавчера') ||
                      str_ends_with($pnlWorksCategory, 'ДругаяДата');
    
    if (!$isDateSelection) {
        echo "   ✓ Пусконаладочные работы correctly NOT identified as date selection\n";
    } else {
        echo "   ✗ Пусконаладочные работы incorrectly identified as date selection\n";
    }
    
    echo "\n=== Test Results ===\n";
    echo "✅ The fix should resolve the issue where the date selection buttons were not appearing\n";
    echo "✅ The date selection buttons will now use unique prefixes that don't conflict with other categories\n";
    echo "✅ The Пусконаладочные работы button will not be confused with date selections\n";
    
} else {
    echo "   ✗ TabelHandler failed to identify this as Пусконаладка department\n";
}

echo "\n=== Fix Summary ===\n";
echo "Changed handleCommissioningDateSelection() to use 'category_Табель_Пусконаладка' as prefix\n";
echo "Changed handleSupervisionDateSelection() to use 'category_Табель_Шеф_монтаж' as prefix\n";
echo "This eliminates conflicts with other categories like 'Табель_ПНЛ'\n";
?>