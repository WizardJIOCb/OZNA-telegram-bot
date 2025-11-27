<?php

/**
 * Test script for the new commissioning idle reason functionality
 */

echo "=== TESTING COMMISSIONING IDLE REASON LOGIC ===\n\n";

// Test the new getCommissioningIdleReason function logic directly
function testCommissioningIdleReason($category)
{
    echo "Testing category: '{$category}'\n";
    
    // Parse the callback to build a readable reason with Parent (Subcategory) format
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
        'teh' => ['parent' => '2', 'text' => 'Техническое решение'],
        'sog' => ['parent' => '2', 'text' => 'Согласование плана работ'],
    ];
    
    $reason = '';
    
    // Skip supervision prefix if present
    $startIndex = ($parts[0] === 'ШМ' && $parts[1] === 'Простой') ? 2 : 1;
    
    // Extract the relevant parts
    $categoryNum = isset($parts[$startIndex]) ? $parts[$startIndex] : null;
    $subcategoryKey = isset($parts[$startIndex + 1]) ? $parts[$startIndex + 1] : null;
    
    echo "categoryNum='{$categoryNum}', subcategoryKey='{$subcategoryKey}'\n";
    
    // Handle direct subcategory access (when callback is missing category number)
    if ($categoryNum && !isset($parentCategories[$categoryNum]) && isset($commissioningSubcategories[$categoryNum])) {
        $subcategoryKey = $categoryNum;
        $categoryNum = null;
        echo "Detected direct subcategory access - subcategoryKey='{$subcategoryKey}'\n";
    }
    
    // Build the specialized commissioning format: "Parent Category (Subcategory)"
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
    // If we have category number but no subcategory, use parent category only
    elseif ($categoryNum && isset($parentCategories[$categoryNum])) {
        $reason = $parentCategories[$categoryNum];
        echo "Using parent category only: '{$reason}'\n";
    }
    
    $finalReason = $reason ?: 'Простой';
    echo "FINAL RESULT: '{$finalReason}'\n\n";
    
    return $finalReason;
}

// Test scenarios
echo "1. Testing CORRECT callback (with category number):\n";
$result1 = testCommissioningIdleReason('category_Табель_Простой_1_def');
echo "Expected: 'Ожидание допоставки (Дефицит по спецификации)'\n";
echo "Actual: '{$result1}'\n";
echo "✅ " . ($result1 === 'Ожидание допоставки (Дефицит по спецификации)' ? "SUCCESS" : "FAILED") . "\n\n";

echo "2. Testing BAD callback (missing category number - current issue):\n";
$result2 = testCommissioningIdleReason('category_Табель_Простой_def');
echo "Expected: 'Ожидание допоставки (Дефицит по спецификации)'\n";
echo "Actual: '{$result2}'\n";
echo "✅ " . ($result2 === 'Ожидание допоставки (Дефицит по спецификации)' ? "SUCCESS" : "FAILED") . "\n\n";

echo "3. Testing supervision version:\n";
$result3 = testCommissioningIdleReason('category_Табель_ШМ_Простой_1_def');
echo "Expected: 'Ожидание допоставки (Дефицит по спецификации)'\n";
echo "Actual: '{$result3}'\n";
echo "✅ " . ($result3 === 'Ожидание допоставки (Дефицит по спецификации)' ? "SUCCESS" : "FAILED") . "\n\n";

echo "4. Testing other categories:\n";
$testCases = [
    'category_Табель_Простой_1_ost' => 'Ожидание допоставки (Отсутствие в поставке)',
    'category_Табель_Простой_2_teh' => 'Отсутствие обратной связи (Техническое решение)',
    'category_Табель_Простой_ost' => 'Ожидание допоставки (Отсутствие в поставке)', // Direct access
];

foreach ($testCases as $callback => $expected) {
    echo "Testing: {$callback}\n";
    $result = testCommissioningIdleReason($callback);
    echo "Expected: '{$expected}'\n";
    echo "Actual: '{$result}'\n";
    echo "✅ " . ($result === $expected ? "SUCCESS" : "FAILED") . "\n\n";
}

echo "=== COMMISSIONING IDLE REASON TEST COMPLETE ===\n";
echo "This specialized handler should now provide the correct format for commissioning works!\n";
?>