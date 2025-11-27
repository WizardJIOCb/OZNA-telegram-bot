<?php

// Debug script to trace what's happening with commissioning idle
echo "=== COMMISSIONING IDLE DEBUG TRACE ===\n\n";

// Add debug logging to see what callback is actually being processed
$debugInfo = [
    'expected_callback' => 'category_Табель_Простой_1_def',
    'user_result' => 'Причина простоя: Простой',
    'expected_result' => 'Причина простоя: Ожидание допоставки (Дефицит по спецификации)'
];

echo "Debug Information:\n";
foreach ($debugInfo as $key => $value) {
    echo "  {$key}: {$value}\n";
}
echo "\n";

// Test the exact function logic that's implemented
function testCommissioningIdleReason($category) {
    echo "Testing getCommissioningIdleReason with: '{$category}'\n";
    
    // Parse the callback exactly as the function does
    $parts = explode('_', str_replace('category_Табель_', '', $category));
    echo "Parts: " . json_encode($parts) . "\n";
    
    $parentCategories = [
        '1' => 'Ожидание допоставки',
        '2' => 'Отсутствие обратной связи', 
        '3' => 'Готовность Заказчика',
        '4' => 'Транспорт',
        '5' => 'Погодные условия',
        '6' => 'Прочее'
    ];
    
    $commissioningSubcategories = [
        'def' => ['parent' => '1', 'text' => 'Дефицит по спецификации'],
        'ost' => ['parent' => '1', 'text' => 'Отсутствие в поставке'],
        'izm' => ['parent' => '1', 'text' => 'Изменение ТР'],
    ];
    
    $reason = '';
    
    // Skip supervision prefix if present
    $startIndex = ($parts[0] === 'ШМ' && $parts[1] === 'Простой') ? 2 : 1;
    
    // Extract the relevant parts
    $categoryNum = isset($parts[$startIndex]) ? $parts[$startIndex] : null;
    $subcategoryKey = isset($parts[$startIndex + 1]) ? $parts[$startIndex + 1] : null;
    
    echo "startIndex: {$startIndex}, categoryNum: '{$categoryNum}', subcategoryKey: '{$subcategoryKey}'\n";
    
    // Handle direct subcategory access
    if ($categoryNum && !isset($parentCategories[$categoryNum]) && isset($commissioningSubcategories[$categoryNum])) {
        $subcategoryKey = $categoryNum;
        $categoryNum = null;
        echo "Detected direct subcategory access - subcategoryKey: '{$subcategoryKey}'\n";
    }
    
    // Build the specialized commissioning format
    if ($subcategoryKey && isset($commissioningSubcategories[$subcategoryKey])) {
        $subcategoryData = $commissioningSubcategories[$subcategoryKey];
        $subcategoryText = $subcategoryData['text'];
        
        // Get parent category name
        if ($subcategoryData['parent'] && isset($parentCategories[$subcategoryData['parent']])) {
            $parentText = $parentCategories[$subcategoryData['parent']];
            $reason = $parentText . ' (' . $subcategoryText . ')';
            echo "Built parent-child format: '{$reason}'\n";
        } else {
            $reason = $subcategoryText;
            echo "Using subcategory only: '{$reason}'\n";
        }
    }
    elseif ($categoryNum && isset($parentCategories[$categoryNum])) {
        $reason = $parentCategories[$categoryNum];
        echo "Using parent category only: '{$reason}'\n";
    }
    
    $finalReason = $reason ?: 'Простой';
    echo "FINAL RESULT: '{$finalReason}'\n\n";
    
    return $finalReason;
}

// Test all possible scenarios that could cause "Простой" to be returned
echo "1. Testing correct callback format:\n";
$result1 = testCommissioningIdleReason('category_Табель_Простой_1_def');

echo "2. Testing missing category number (potential issue):\n";
$result2 = testCommissioningIdleReason('category_Табель_Простой_def');

echo "3. Testing empty callback (worst case):\n";
$result3 = testCommissioningIdleReason('category_Табель_Простой');

echo "4. Testing malformed callback:\n";
$result4 = testCommissioningIdleReason('Простой');

echo "ANALYSIS:\n";
echo "========\n";
if ($result1 === 'Ожидание допоставки (Дефицит по спецификации)') {
    echo "✅ Correct format works: '{$result1}'\n";
} else {
    echo "❌ Correct format failed: '{$result1}'\n";
}

if ($result2 === 'Ожидание допоставки (Дефицит по спецификации)') {
    echo "✅ Missing category number handled: '{$result2}'\n";
} else {
    echo "❌ Missing category number failed: '{$result2}'\n";
}

if ($result3 === 'Простой') {
    echo "✅ Empty callback returns default: '{$result3}'\n";
} else {
    echo "❌ Empty callback unexpected: '{$result3}'\n";
}

if ($result4 === 'Простой') {
    echo "✅ Malformed callback returns default: '{$result4}'\n";
} else {
    echo "❌ Malformed callback unexpected: '{$result4}'\n";
}

echo "\nCONCLUSION:\n";
echo "If user gets 'Простой', the callback received is likely malformed or missing data.\n";
echo "Need to check what callback is actually being generated/received.\n";
?>