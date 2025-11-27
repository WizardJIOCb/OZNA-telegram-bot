<?php

/**
 * Test to determine exactly which version of getIdleReasonFromCallback is being used
 */

echo "=== TESTING ACTUAL IDLE REASON LOGIC ===\n\n";

echo "This test will determine if the issue is with:\n";
echo "1. OLD logic (simple subcategories array) - would return 'Дефицит по спецификации'\n";
echo "2. NEW logic (subcategoryToParent array) - should return 'Ожидание допоставки (Дефицит по спецификации)'\n\n";

// Simulate both versions to understand the difference
echo "TESTING BOTH LOGIC VERSIONS DIRECTLY:\n\n";
    
    // Test with the exact logic from the main file
    echo "Testing the logic directly...\n";
    
    // Test with the exact logic from the main file
    $category = 'category_Табель_Простой_1_def';
    $parts = explode('_', str_replace('category_Табель_', '', $category));
    
    $categories = [
        '1' => 'Ожидание допоставки',
        '2' => 'Отсутствие обратной связи', 
        '3' => 'Готовность Заказчика',
        '4' => 'Транспорт',
        '5' => 'Погодные условия',
        '6' => 'Прочее'
    ];
    
    $subcategoryToParent = [
        'def' => ['parent' => '1', 'text' => 'Дефицит по спецификации'],
    ];
    
    $reason = '';
    $startIndex = 1;
    $categoryNum = isset($parts[$startIndex]) ? $parts[$startIndex] : null;
    $subcategoryKey = isset($parts[$startIndex + 1]) ? $parts[$startIndex + 1] : null;
    
    if ($categoryNum && isset($categories[$categoryNum])) {
        $reason = $categories[$categoryNum];
    } elseif ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
        $parentCategoryNum = $subcategoryToParent[$subcategoryKey]['parent'];
        if ($parentCategoryNum && isset($categories[$parentCategoryNum])) {
            $reason = $categories[$parentCategoryNum];
        }
    }
    
    if ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
        $subcategoryText = $subcategoryToParent[$subcategoryKey]['text'];
        if (!empty($reason)) {
            $reason .= ' (' . $subcategoryText . ')';
        } else {
            $reason = $subcategoryText;
        }
    }
    
    $fallbackResult = $reason ?: 'Простой';
    echo "Fallback result: '{$fallbackResult}'\n";
    
    if ($fallbackResult === 'Ожидание допоставки (Дефицит по спецификации)') {
        echo "✅ The NEW logic should work correctly!\n";
        echo "The issue is likely that an older version of the file is being used,\n";
        echo "or there's a deployment/caching issue.\n";
    }

echo "\n=== RECOMMENDATION ===\n";
echo "Check which TabelHandler.php file is actually being used by the server.\n";
echo "The user's result suggests the old simple logic is running instead of the new advanced logic.\n";