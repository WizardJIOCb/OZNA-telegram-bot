<?php

// Test the idle reason logic with the new format

function testGetIdleReasonFromCallback($category) {
    echo "Testing category: '$category'\n";
    
    // Parse the callback to build a readable reason
    $parts = explode('_', str_replace('category_Табель_', '', $category));
    echo "Parts: " . json_encode($parts) . "\n";
    
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
        // Add more mappings as needed...
    ];
    
    $reason = '';
    
    // Skip supervision prefix if present
    $startIndex = ($parts[0] === 'ШМ' && $parts[1] === 'Простой') ? 2 : 1;
    
    // Extract the relevant parts
    $categoryNum = isset($parts[$startIndex]) ? $parts[$startIndex] : null;
    $subcategoryKey = isset($parts[$startIndex + 1]) ? $parts[$startIndex + 1] : null;
    $subSubcategoryKey = isset($parts[$startIndex + 2]) ? $parts[$startIndex + 2] : null;
    
    echo "categoryNum='$categoryNum', subcategoryKey='$subcategoryKey', subSubcategoryKey='$subSubcategoryKey'\n";
    
    // Special case: If categoryNum looks like a subcategory key, treat it as such
    if ($categoryNum && !isset($categories[$categoryNum]) && isset($subcategoryToParent[$categoryNum])) {
        // This is actually a subcategory, not a category number
        $subSubcategoryKey = $subcategoryKey; // shift the parts
        $subcategoryKey = $categoryNum;
        $categoryNum = null;
        echo "Detected direct subcategory access - subcategoryKey='$subcategoryKey', subSubcategoryKey='$subSubcategoryKey'\n";
    }
    
    // If we have a category number, use it
    if ($categoryNum && isset($categories[$categoryNum])) {
        $reason = $categories[$categoryNum];
        echo "Found main category: '$reason'\n";
    }
    // If no category number but we have a subcategory, look up its parent
    elseif ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
        $parentCategoryNum = $subcategoryToParent[$subcategoryKey]['parent'];
        if ($parentCategoryNum && isset($categories[$parentCategoryNum])) {
            $reason = $categories[$parentCategoryNum];
            echo "Found parent category via subcategory lookup: '$reason'\n";
        }
    }
    
    // Add subcategory using new logic: Parent (Subcategory)
    if ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
        $subcategoryText = $subcategoryToParent[$subcategoryKey]['text'];
        if (!empty($reason)) {
            // Always use parentheses format for subcategory
            $reason .= ' (' . $subcategoryText . ')';
        } else {
            $reason = $subcategoryText;
        }
        echo "Added subcategory: '$subcategoryText', reason now: '$reason'\n";
    }
    
    $finalReason = $reason ?: 'Простой';
    echo "Final idle reason: '$finalReason'\n\n";
    
    return $finalReason;
}

// Test cases
echo "=== TESTING IDLE REASON LOGIC ===\n\n";

echo "Test 1: Direct subcategory (what user reports)\n";
testGetIdleReasonFromCallback('category_Табель_Простой_1_def');

echo "Test 2: Expected result\n";
echo "Expected: 'Ожидание допоставки (Дефицит по спецификации)'\n\n";

echo "Test 3: Another example\n";
testGetIdleReasonFromCallback('category_Табель_Простой_1_ost');
