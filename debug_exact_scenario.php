<?php

/**
 * Test the actual scenario that produces ONLY "Дефицит по спецификации"
 */

echo "=== TESTING REAL SCENARIO ===\n\n";

function getIdleReasonFromCallback(string $category): string
{
    echo "Input category: '{$category}'\n";
    
    // Parse the callback to build a readable reason
    $parts = explode('_', str_replace('category_Табель_', '', $category));
    echo "Parts after parsing: " . json_encode($parts) . "\n";
    
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
    
    echo "categoryNum='{$categoryNum}', subcategoryKey='{$subcategoryKey}', subSubcategoryKey='{$subSubcategoryKey}'\n";
    
    // Special case: If categoryNum looks like a subcategory key, treat it as such
    if ($categoryNum && !isset($categories[$categoryNum]) && isset($subcategoryToParent[$categoryNum])) {
        $subSubcategoryKey = $subcategoryKey;
        $subcategoryKey = $categoryNum;
        $categoryNum = null;
        echo "Detected direct subcategory access - subcategoryKey='{$subcategoryKey}', subSubcategoryKey='{$subSubcategoryKey}'\n";
    }
    
    // If we have a category number, use it
    if ($categoryNum && isset($categories[$categoryNum])) {
        $reason = $categories[$categoryNum];
        echo "Found main category: '{$reason}'\n";
    }
    // If no category number but we have a subcategory, look up its parent
    elseif ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
        $parentCategoryNum = $subcategoryToParent[$subcategoryKey]['parent'];
        if ($parentCategoryNum && isset($categories[$parentCategoryNum])) {
            $reason = $categories[$parentCategoryNum];
            echo "Found parent category via subcategory lookup: '{$reason}'\n";
        }
    }
    
    // Add subcategory
    if ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
        $subcategoryText = $subcategoryToParent[$subcategoryKey]['text'];
        if (!empty($reason)) {
            if ($subSubcategoryKey && isset($subcategoryToParent[$subSubcategoryKey])) {
                $reason .= ' → ' . $subcategoryText;
            } else {
                $reason .= ' (' . $subcategoryText . ')';
            }
        } else {
            $reason = $subcategoryText;
        }
        echo "Added subcategory: '{$subcategoryText}', reason now: '{$reason}'\n";
    }
    
    $finalReason = $reason ?: 'Простой';
    echo "Final idle reason: '{$finalReason}'\n";
    
    return $finalReason;
}

// Test scenarios that could produce only "Дефицит по спецификации"

echo "1. Testing callback with ONLY subcategory (no prefix):\n";
$result1 = getIdleReasonFromCallback('category_Табель_def');
echo "Result: '{$result1}'\n\n";

echo "2. Testing if subcategory mapping is incomplete:\n";
function testWithIncompleteMapping(string $category): string
{
    echo "Input category: '{$category}'\n";
    
    $parts = explode('_', str_replace('category_Табель_', '', $category));
    echo "Parts after parsing: " . json_encode($parts) . "\n";
    
    // Simulate incomplete subcategory mapping (missing parent info)
    $subcategoryToParent = [
        'def' => ['text' => 'Дефицит по спецификации'], // NO parent field!
    ];
    
    $reason = '';
    $startIndex = 1;
    
    $categoryNum = isset($parts[$startIndex]) ? $parts[$startIndex] : null;
    $subcategoryKey = isset($parts[$startIndex + 1]) ? $parts[$startIndex + 1] : null;
    
    echo "categoryNum='{$categoryNum}', subcategoryKey='{$subcategoryKey}'\n";
    
    // This would skip parent lookup if parent is missing
    if ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
        $subcategoryText = $subcategoryToParent[$subcategoryKey]['text'];
        $reason = $subcategoryText; // Would only get subcategory text
        echo "Added subcategory without parent: '{$reason}'\n";
    }
    
    return $reason;
}

echo "Testing with incomplete mapping:\n";
$result2 = testWithIncompleteMapping('category_Табель_Простой_def');
echo "Result: '{$result2}'\n\n";

echo "3. Testing if the callback is coming from a different flow:\n";
// Maybe the callback is being generated differently in some flow?
echo "Could the callback be 'category_Табель_ДефицитПоСпецификации' (direct activity)?\n";
$result3 = getIdleReasonFromCallback('category_Табель_ДефицитПоСпецификации');
echo "Result: '{$result3}'\n\n";

echo "4. CONCLUSION:\n";
echo "Based on tests:\n";
echo "- Normal parsing works correctly\n";
echo "- If result is only 'Дефицит по спецификации', it suggests:\n";
echo "  a) The callback doesn't include parent category info, OR\n";
echo "  b) The subcategory mapping is missing parent info, OR\n";
echo "  c) The parsing logic is being bypassed somehow\n";
echo "\nNext step: Check the actual callback being received in the bot\n";