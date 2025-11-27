<?php

echo "=== DEBUGGING USER'S EXACT SCENARIO ===\n\n";

// Test the exact callback that should be generated when user clicks "Дефицит по спецификации"
$testCallback = 'category_Табель_Простой_1_def';
echo "Testing callback: $testCallback\n\n";

// Exact logic from TabelHandler.php
function getIdleReasonFromCallback($category) {
    echo "Input: $category\n";
    
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
    
    $subcategoryToParent = [
        'def' => ['parent' => '1', 'text' => 'Дефицит по спецификации'],
        'ost' => ['parent' => '1', 'text' => 'Отсутствие в поставке'],
        'izm' => ['parent' => '1', 'text' => 'Изменение ТР'],
    ];
    
    $reason = '';
    $startIndex = 1;
    $categoryNum = isset($parts[$startIndex]) ? $parts[$startIndex] : null;
    $subcategoryKey = isset($parts[$startIndex + 1]) ? $parts[$startIndex + 1] : null;
    
    echo "categoryNum: '$categoryNum', subcategoryKey: '$subcategoryKey'\n";
    
    if ($categoryNum && isset($categories[$categoryNum])) {
        $reason = $categories[$categoryNum];
        echo "Found parent category: '$reason'\n";
    }
    
    if ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
        $subcategoryText = $subcategoryToParent[$subcategoryKey]['text'];
        if (!empty($reason)) {
            $reason .= ' (' . $subcategoryText . ')';
        } else {
            $reason = $subcategoryText;
        }
        echo "Added subcategory: '$subcategoryText', result: '$reason'\n";
    }
    
    return $reason ?: 'Простой';
}

$result = getIdleReasonFromCallback($testCallback);
echo "\nFINAL RESULT: '$result'\n";

if ($result === 'Ожидание допоставки (Дефицит по спецификации)') {
    echo "✅ CORRECT - Logic works as expected\n";
} elseif ($result === 'Дефицит по спецификации') {
    echo "❌ INCORRECT - Only subcategory, missing parent\n";
} else {
    echo "❓ UNEXPECTED RESULT\n";
}

echo "\n=== POSSIBLE ALTERNATIVE SCENARIOS ===\n";

$testCases = [
    'category_Табель_Простой_def' => 'Missing category number',
    'category_Табель_def' => 'Missing Простой and category',
    'def' => 'Only subcategory key'
];

foreach ($testCases as $callback => $description) {
    echo "\nTesting: $callback ($description)\n";
    $result = getIdleReasonFromCallback($callback);
    echo "Result: '$result'\n";
    
    if ($result === 'Дефицит по спецификации') {
        echo "🎯 This could explain the user's result!\n";
    }
}