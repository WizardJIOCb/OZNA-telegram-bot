<?php

/**
 * Test the updated idle reason generation
 */

function testUpdatedIdleReason(string $category): string
{
    // Parse the callback to build a readable reason
    $parts = explode('_', str_replace('category_Табель_', '', $category));
    
    $categories = [
        '1' => 'Ожидание допоставки',
        '2' => 'Отсутствие обратной связи', 
        '3' => 'Готовность Заказчика',
        '4' => 'Транспорт',
        '5' => 'Погодные условия',
        '6' => 'Прочее'
    ];
    
    // Mapping subcategories to their parent categories for reverse lookup
    $subcategoryToParent = [
        // Категория 1: Ожидание допоставки
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
    $subSubcategoryKey = isset($parts[$startIndex + 2]) ? $parts[$startIndex + 2] : null;
    
    // Special case: If categoryNum looks like a subcategory key, treat it as such
    if ($categoryNum && !isset($categories[$categoryNum]) && isset($subcategoryToParent[$categoryNum])) {
        // This is actually a subcategory, not a category number
        $subSubcategoryKey = $subcategoryKey; // shift the parts
        $subcategoryKey = $categoryNum;
        $categoryNum = null;
    }
    
    // If we have a category number, use it
    if ($categoryNum && isset($categories[$categoryNum])) {
        $reason = $categories[$categoryNum];
    }
    // If no category number but we have a subcategory, look up its parent
    elseif ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
        $parentCategoryNum = $subcategoryToParent[$subcategoryKey]['parent'];
        if ($parentCategoryNum && isset($categories[$parentCategoryNum])) {
            $reason = $categories[$parentCategoryNum];
        }
    }
    
    // Add subcategory
    if ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
        $subcategoryText = $subcategoryToParent[$subcategoryKey]['text'];
        if (!empty($reason)) {
            // Check if there's a sub-subcategory to determine separator
            if ($subSubcategoryKey && isset($subcategoryToParent[$subSubcategoryKey])) {
                // Multiple levels - use arrow
                $reason .= ' → ' . $subcategoryText;
            } else {
                // Single subcategory - use colon
                $reason .= ': ' . $subcategoryText;
            }
        } else {
            $reason = $subcategoryText;
        }
    }
    
    return $reason ?: 'Простой';
}

echo "🧪 Testing updated idle reason generation\n";
echo "=======================================\n\n";

// Test the exact case from user's feedback
echo "Testing callback: 'category_Табель_Простой_def'\n";
echo "Expected: 'Ожидание допоставки: Дефицит по спецификации'\n";
echo "Actual: '" . testUpdatedIdleReason('category_Табель_Простой_def') . "'\n\n";

// Test the full format case
echo "Testing callback: 'category_Табель_Простой_1_def'\n";
echo "Expected: 'Ожидание допоставки: Дефицит по спецификации'\n";
echo "Actual: '" . testUpdatedIdleReason('category_Табель_Простой_1_def') . "'\n\n";

echo "✅ Test completed!\n";