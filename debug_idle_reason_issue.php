<?php

/**
 * Debug script to reproduce the exact idle reason issue
 * User clicks "Дефицит по спецификации" but gets only the subcategory, not parent category
 */

echo "=== DEBUGGING IDLE REASON ISSUE ===\n\n";

// Test what callback would be generated when user clicks "Дефицит по спецификации"
echo "1. TESTING CALLBACK GENERATION:\n";

// This is what should happen when showing the "Ожидание допоставки" submenu
$categoryNumber = '1'; // Ожидание допоставки
$subcategory = 'def'; // Дефицит по спецификации
$prefix = 'Табель_Простой';

$expectedCallback = "category_{$prefix}_{$categoryNumber}_{$subcategory}";
echo "Expected callback when clicking 'Дефицит по спецификации': {$expectedCallback}\n";

// But maybe the callback is being generated without category number?
$possibleBadCallback = "category_{$prefix}_{$subcategory}";
echo "Possible bad callback (without category): {$possibleBadCallback}\n\n";

// Test the parsing logic with both scenarios
echo "2. TESTING PARSING LOGIC:\n";

function getIdleReasonFromCallback(string $category): string
{
    echo "   Input category: '{$category}'\n";
    
    // Parse the callback to build a readable reason
    $parts = explode('_', str_replace('category_Табель_', '', $category));
    echo "   Parts after parsing: " . json_encode($parts) . "\n";
    
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
        // Категория 2: Отсутствие обратной связи
        'teh' => ['parent' => '2', 'text' => 'Техническое решение'],
        'sog' => ['parent' => '2', 'text' => 'Согласование плана работ'],
        // ... (other categories)
    ];
    
    $reason = '';
    
    // Skip supervision prefix if present
    $startIndex = ($parts[0] === 'ШМ' && $parts[1] === 'Простой') ? 2 : 1;
    
    // Extract the relevant parts
    $categoryNum = isset($parts[$startIndex]) ? $parts[$startIndex] : null;
    $subcategoryKey = isset($parts[$startIndex + 1]) ? $parts[$startIndex + 1] : null;
    $subSubcategoryKey = isset($parts[$startIndex + 2]) ? $parts[$startIndex + 2] : null;
    
    echo "   categoryNum='{$categoryNum}', subcategoryKey='{$subcategoryKey}', subSubcategoryKey='{$subSubcategoryKey}'\n";
    
    // Special case: If categoryNum looks like a subcategory key, treat it as such
    if ($categoryNum && !isset($categories[$categoryNum]) && isset($subcategoryToParent[$categoryNum])) {
        // This is actually a subcategory, not a category number
        $subSubcategoryKey = $subcategoryKey; // shift the parts
        $subcategoryKey = $categoryNum;
        $categoryNum = null;
        echo "   Detected direct subcategory access - subcategoryKey='{$subcategoryKey}', subSubcategoryKey='{$subSubcategoryKey}'\n";
    }
    
    // If we have a category number, use it
    if ($categoryNum && isset($categories[$categoryNum])) {
        $reason = $categories[$categoryNum];
        echo "   Found main category: '{$reason}'\n";
    }
    // If no category number but we have a subcategory, look up its parent
    elseif ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
        $parentCategoryNum = $subcategoryToParent[$subcategoryKey]['parent'];
        if ($parentCategoryNum && isset($categories[$parentCategoryNum])) {
            $reason = $categories[$parentCategoryNum];
            echo "   Found parent category via subcategory lookup: '{$reason}'\n";
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
                // Single subcategory - use parentheses format
                $reason .= ' (' . $subcategoryText . ')';
            }
        } else {
            $reason = $subcategoryText;
        }
        echo "   Added subcategory: '{$subcategoryText}', reason now: '{$reason}'\n";
    }
    
    $finalReason = $reason ?: 'Простой';
    echo "   Final idle reason: '{$finalReason}'\n";
    
    return $finalReason;
}

echo "Testing with EXPECTED callback (with category number):\n";
$result1 = getIdleReasonFromCallback($expectedCallback);
echo "Result: '{$result1}'\n\n";

echo "Testing with POSSIBLE BAD callback (without category number):\n";
$result2 = getIdleReasonFromCallback($possibleBadCallback);
echo "Result: '{$result2}'\n\n";

echo "3. ANALYSIS:\n";
if ($result1 === 'Ожидание допоставки (Дефицит по спецификации)') {
    echo "✅ Expected callback produces CORRECT result\n";
} else {
    echo "❌ Expected callback produces WRONG result: '{$result1}'\n";
}

if ($result2 === 'Дефицит по спецификации') {
    echo "✅ Bad callback produces OBSERVED result (no parent category)\n";
    echo "🔍 This suggests the callback is being generated WITHOUT the category number!\n";
} else {
    echo "❌ Bad callback produces unexpected result: '{$result2}'\n";
}

echo "\n4. CONCLUSION:\n";
echo "If the test shows that the bad callback produces the observed result,\n";
echo "then the issue is in the CALLBACK GENERATION, not in the parsing logic.\n";
echo "We need to check where idle menu buttons are created and ensure\n";
echo "they include the category number in the callback data.\n";