<?php

/**
 * Test script to debug idle reason generation for the exact user case
 */

// Test the getIdleReasonFromCallback method directly
function testGetIdleReasonFromCallback(string $category): string
{
    error_log("TEST: getIdleReasonFromCallback called with: '{$category}'");
    
    // Parse the callback to build a readable reason
    $parts = explode('_', str_replace('category_Табель_', '', $category));
    error_log("TEST: callback parts after parsing: " . json_encode($parts));
    
    $categories = [
        '1' => 'Допоставка',
        '2' => 'Отсутствие обратной связи', 
        '3' => 'Готовность Заказчика',
        '4' => 'Транспорт',
        '5' => 'Погодные условия',
        '6' => 'Прочее'
    ];
    
    // Mapping subcategories to their parent categories for reverse lookup
    $subcategoryToParent = [
        // Категория 1: Допоставка
        'def' => ['parent' => '1', 'text' => 'Дефицит по спецификации'],
        'ost' => ['parent' => '1', 'text' => 'Отсутствие в поставке'],
        'izm' => ['parent' => '1', 'text' => 'Изменение ТР'],
        // Категория 2: Отсутствие обратной связи
        'teh' => ['parent' => '2', 'text' => 'Техническое решение'],
        'sog' => ['parent' => '2', 'text' => 'Согласование плана работ'],
        // Категория 3: Готовность Заказчика
        'ene' => ['parent' => '3', 'text' => 'Отсутствие энергоресурсов'],
        'rab' => ['parent' => '3', 'text' => 'Отсутствие рабочих сред'],
        'per' => ['parent' => '3', 'text' => 'Перенос предъявления работ'],
        'str' => ['parent' => '3', 'text' => 'Отсутствие строительной готовности'],
        // Категория 4: Транспорт
        'pol' => ['parent' => '4', 'text' => 'Поломка'],
        'mes' => ['parent' => '4', 'text' => 'Отсутствие свободных мест'],
        // Категория 5: Погодные условия
        'bur' => ['parent' => '5', 'text' => 'Буран/снегопад'],
        'mor' => ['parent' => '5', 'text' => 'Мороз'],
        'ras' => ['parent' => '5', 'text' => 'Распутица'],
        // Категория 6: Прочее
        'po' => ['parent' => '6', 'text' => 'Разработка ПО'],
        'dop' => ['parent' => '6', 'text' => 'Отсутствие допуска к работам'],
        'svy' => ['parent' => '6', 'text' => 'Отсутствие связи'],
        'kom' => ['parent' => '6', 'text' => 'Свой комментарий'],
        // Подподкатегории (разделы)
        'es' => ['parent' => null, 'text' => 'Раздел ЭС'],
        'ath' => ['parent' => null, 'text' => 'Раздел АТХ'],
        'ov' => ['parent' => null, 'text' => 'Раздел ОВ'],
        'ps' => ['parent' => null, 'text' => 'Раздел ПС/ОС'],
        'th' => ['parent' => null, 'text' => 'Раздел ТХ'],
        'pki' => ['parent' => null, 'text' => 'Поставщик ПКИ']
    ];
    
    $reason = '';
    
    // Skip supervision prefix if present
    $startIndex = ($parts[0] === 'ШМ' && $parts[1] === 'Простой') ? 2 : 1;
    
    // Extract the relevant parts
    $categoryNum = isset($parts[$startIndex]) ? $parts[$startIndex] : null;
    $subcategoryKey = isset($parts[$startIndex + 1]) ? $parts[$startIndex + 1] : null;
    $subSubcategoryKey = isset($parts[$startIndex + 2]) ? $parts[$startIndex + 2] : null;
    
    error_log("TEST: categoryNum='{$categoryNum}', subcategoryKey='{$subcategoryKey}', subSubcategoryKey='{$subSubcategoryKey}'");
    
    // Special case: If categoryNum looks like a subcategory key, treat it as such
    if ($categoryNum && !isset($categories[$categoryNum]) && isset($subcategoryToParent[$categoryNum])) {
        // This is actually a subcategory, not a category number
        $subSubcategoryKey = $subcategoryKey; // shift the parts
        $subcategoryKey = $categoryNum;
        $categoryNum = null;
        error_log("TEST: Detected direct subcategory access - subcategoryKey='{$subcategoryKey}', subSubcategoryKey='{$subSubcategoryKey}'");
    }
    
    // If we have a category number, use it
    if ($categoryNum && isset($categories[$categoryNum])) {
        $reason = $categories[$categoryNum];
        error_log("TEST: Found main category: '{$reason}'");
    }
    // If no category number but we have a subcategory, look up its parent
    elseif ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
        $parentCategoryNum = $subcategoryToParent[$subcategoryKey]['parent'];
        if ($parentCategoryNum && isset($categories[$parentCategoryNum])) {
            $reason = $categories[$parentCategoryNum];
            error_log("TEST: Found parent category via subcategory lookup: '{$reason}'");
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
        error_log("TEST: Added subcategory: '{$subcategoryText}', reason now: '{$reason}'");
    }
    
    // Add sub-subcategory (always with arrow since it's third level)
    if ($subSubcategoryKey && isset($subcategoryToParent[$subSubcategoryKey])) {
        $subSubcategoryText = $subcategoryToParent[$subSubcategoryKey]['text'];
        if (!empty($reason)) {
            $reason .= ' → ' . $subSubcategoryText;
        } else {
            $reason = $subSubcategoryText;
        }
        error_log("TEST: Added sub-subcategory: '{$subSubcategoryText}', reason now: '{$reason}'");
    }
    
    $finalReason = $reason ?: 'Простой';
    error_log("TEST: Final idle reason: '{$finalReason}'");
    
    return $finalReason;
}

echo "🧪 Testing idle reason generation for the exact user case\n";
echo "=====================================================\n\n";

// Test case: The exact callback that should generate "Допоставка: Дефицит по спецификации"
echo "Testing callback: 'category_Табель_Простой_1_def'\n";
echo "Expected: 'Допоставка: Дефицит по спецификации'\n";
echo "Actual: '" . testGetIdleReasonFromCallback('category_Табель_Простой_1_def') . "'\n\n";

// Test case: The callback with subcategory that shows up in final selection
echo "Testing callback: 'category_Табель_Простой_def'\n";
echo "Expected: 'Допоставка: Дефицит по спецификации' (via reverse lookup)\n";
echo "Actual: '" . testGetIdleReasonFromCallback('category_Табель_Простой_def') . "'\n\n";

// Test a few more cases to verify the logic
echo "Testing callback: 'category_Табель_Простой_1_ost'\n";
echo "Expected: 'Допоставка: Отсутствие в поставке'\n";
echo "Actual: '" . testGetIdleReasonFromCallback('category_Табель_Простой_1_ost') . "'\n\n";

echo "Testing callback: 'category_Табель_Простой_ost'\n";
echo "Expected: 'Допоставка: Отсутствие в поставке' (via reverse lookup)\n";
echo "Actual: '" . testGetIdleReasonFromCallback('category_Табель_Простой_ost') . "'\n\n";

echo "✅ Test completed! Check the logs above to see the detailed parsing.\n";