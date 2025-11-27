<?php

// Test the fixed idle reason formatting
echo "=== TESTING IDLE REASON FORMATTING FIX ===\n\n";

// Test the exact callback that should be generated
$testCategory = 'Табель_Простой_1_def';
echo "Testing callback: '{$testCategory}'\n\n";

// Simulate the fixed parsing logic
function testGetCommissioningIdleReason(string $category): string
{
    error_log("Testing getCommissioningIdleReason with: '{$category}'");
    
    // Parse the callback to build a readable reason with Parent (Subcategory) format
    // Handle both 'category_Табель_' and 'Табель_' prefixes
    $cleanCategory = $category;
    if (str_starts_with($category, 'category_Табель_')) {
        $cleanCategory = substr($category, strlen('category_Табель_'));
    } elseif (str_starts_with($category, 'Табель_')) {
        $cleanCategory = substr($category, strlen('Табель_'));
    }
    
    $parts = explode('_', $cleanCategory);
    echo "Parts after cleaning: " . json_encode($parts) . "\n";
    
    $parentCategories = [
        '1' => 'Ожидание допоставки',
        '2' => 'Отсутствие обратной связи', 
        '3' => 'Готовность Заказчика',
        '4' => 'Транспорт',
        '5' => 'Погодные условия',
        '6' => 'Прочее'
    ];
    
    // Enhanced mapping for commissioning works with parent-child relationship
    $commissioningSubcategories = [
        // Категория 1: Ожидание допоставки -> Commissioning specific format
        'def' => ['parent' => '1', 'text' => 'Дефицит по спецификации'],
        'ost' => ['parent' => '1', 'text' => 'Отсутствие в поставке'],
        'izm' => ['parent' => '1', 'text' => 'Изменение ТР'],
        // Категория 2: Отсутствие обратной связи
        'teh' => ['parent' => '2', 'text' => 'Техническое решение'],
        'sog' => ['parent' => '2', 'text' => 'Согласование плана работ'],
        // Add other mappings as needed...
    ];
    
    // Skip supervision prefix if present and extract relevant parts
    // Expected format after cleaning: "Простой_1_def" or "ШМ_Простой_1_def"
    $startIndex = 0;
    if ($parts[0] === 'ШМ' && isset($parts[1]) && $parts[1] === 'Простой') {
        $startIndex = 2; // Skip 'ШМ_Простой_'
    } elseif ($parts[0] === 'Простой') {
        $startIndex = 1; // Skip 'Простой_'
    }
    
    // Extract the relevant parts
    $categoryNum = isset($parts[$startIndex]) ? $parts[$startIndex] : null;
    $subcategoryKey = isset($parts[$startIndex + 1]) ? $parts[$startIndex + 1] : null;
    
    echo "After parsing - categoryNum: '{$categoryNum}', subcategoryKey: '{$subcategoryKey}'\n";
    
    $reason = '';
    
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
    echo "FINAL IDLE REASON: '{$finalReason}'\n";
    
    return $finalReason;
}

// Test the fix
$result = testGetCommissioningIdleReason($testCategory);

echo "\n=== EXPECTED RESULT ===\n";
echo "Expected: 'Ожидание допоставки (Дефицит по спецификации)'\n";
echo "Actual:   '{$result}'\n";
echo "Match:    " . ($result === 'Ожидание допоставки (Дефицит по спецификации)' ? 'YES ✅' : 'NO ❌') . "\n\n";

echo "=== TEST COMPLETE ===\n";
echo "If the test shows 'YES ✅', the fix should work correctly!\n";
echo "Now test in Telegram: ПНР → Простой → Допоставка → Дефицит по спецификации\n";

?>