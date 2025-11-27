<?php

/**
 * Comprehensive test to trace the exact user interaction that produces
 * "Дефицит по спецификации" instead of "Ожидание допоставки (Дефицит по спецификации)"
 */

echo "=== COMPREHENSIVE IDLE REASON DEBUG ===\n\n";

// Let me test the EXACT scenario from user's feedback
echo "User reported this result:\n";
echo "Input: User clicked 'Дефицит по спецификации'\n";
echo "Expected: 'Причина простоя: Ожидание допоставки (Дефицит по спецификации)'\n";
echo "Actual: 'Причина простоя: Дефицит по спецификации'\n\n";

// The key insight: this suggests the callback is MISSING the category number
// Let me test different callback scenarios to reproduce this exact behavior

function testIdleReasonExtraction($callbackData, $description) {
    echo "=== Testing: {$description} ===\n";
    echo "Callback: {$callbackData}\n";
    
    // Simulate the exact parsing logic from TabelHandler.php
    $category = str_replace('category_', '', $callbackData);
    echo "Category after removing 'category_': {$category}\n";
    
    $parts = explode('_', str_replace('category_Табель_', '', $callbackData));
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
    $startIndex = ($parts[0] === 'ШМ' && $parts[1] === 'Простой') ? 2 : 1;
    
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
    echo "FINAL RESULT: '{$finalReason}'\n";
    
    return $finalReason;
}

// Test various callback scenarios
echo "1. CORRECT callback (with category number):\n";
$result1 = testIdleReasonExtraction('category_Табель_Простой_1_def', 'Expected format');
echo "\n";

echo "2. MISSING category number (direct subcategory):\n";
$result2 = testIdleReasonExtraction('category_Табель_Простой_def', 'Missing category number');
echo "\n";

echo "3. COMPLETELY WRONG format (only text):\n";
$result3 = testIdleReasonExtraction('category_Табель_ДефицитПоСпецификации', 'Wrong format');
echo "\n";

echo "4. DIFFERENT prefix structure:\n";
$result4 = testIdleReasonExtraction('category_def', 'Minimal format');
echo "\n";

// Analysis
echo "=== ANALYSIS ===\n";
echo "Expected result: 'Ожидание допоставки (Дефицит по спецификации)'\n";
echo "User's actual result: 'Дефицит по спецификации'\n\n";

$matches = [
    $result1 => 'CORRECT callback',
    $result2 => 'MISSING category number',
    $result3 => 'WRONG format',
    $result4 => 'MINIMAL format'
];

foreach ($matches as $result => $test) {
    if ($result === 'Дефицит по спецификации') {
        echo "🎯 MATCH FOUND: {$test} produces the user's result!\n";
    } elseif ($result === 'Ожидание допоставки (Дефицит по спецификации)') {
        echo "✅ {$test} produces the expected result\n";
    } else {
        echo "❓ {$test} produces: '{$result}'\n";
    }
}

echo "\n=== HYPOTHESIS ===\n";
echo "The most likely cause is:\n";
echo "1. The idle menu is generating callbacks WITHOUT the category number\n";
echo "2. Instead of 'category_Табель_Простой_1_def' it's generating 'category_Табель_Простой_def'\n";
echo "3. The parsing logic fails to find the parent category because it expects the number\n\n";

echo "=== NEXT STEPS ===\n";
echo "1. Check the actual idle menu callback generation in TabelHandler.php line ~3228\n";
echo "2. Verify if the menu structure includes category numbers in the callback\n";
echo "3. Check if there's a mismatch between menu generation and parsing logic\n";

// Test if there's actually a simpler explanation
echo "\n=== ALTERNATIVE HYPOTHESIS ===\n";
echo "Maybe the issue is that the conversation notes are being overwritten somewhere?\n";
echo "Or maybe the idle reason is being set incorrectly in a different code path?\n";

// Let me check if there's a simple text assignment somewhere
function findDirectTextAssignment() {
    echo "Checking for direct text assignments...\n";
    
    // Simulate a conversation notes scenario
    $conversationNotes = [];
    
    // What if idle_reason is being set directly to the subcategory text?
    $conversationNotes['idle_reason'] = 'Дефицит по спецификации'; // Direct assignment
    
    echo "If idle_reason is set directly: '" . $conversationNotes['idle_reason'] . "'\n";
    echo "This would explain the user's result!\n";
    
    return $conversationNotes['idle_reason'];
}

$directResult = findDirectTextAssignment();
echo "Direct assignment result: '{$directResult}'\n";

if ($directResult === 'Дефицит по спецификации') {
    echo "🎯 POTENTIAL ROOT CAUSE: idle_reason might be set directly to subcategory text\n";
    echo "instead of going through the proper parsing logic!\n";
}