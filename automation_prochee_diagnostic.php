<?php

// Comprehensive diagnostic for automation "Прочее" callback flow
$testCallback = 'cat_ТабАвтом_Проч';

echo "=== AUTOMATION ПРОЧЕЕ CALLBACK DIAGNOSTIC ===\n\n";

// 1. Test TabelHandler canHandle
echo "1. TabelHandler canHandle test:\n";
$canHandle = str_starts_with($testCallback, 'category_Табель') || 
             str_starts_with($testCallback, 'cat_ТабПНРаботы') ||
             str_starts_with($testCallback, 'cat_Таб') ||
             $testCallback === 'новое_прочее_yes' ||
             $testCallback === 'cat_ТабЭлОб_Проч_yes';
echo "  Callback: $testCallback\n";
echo "  canHandle result: " . ($canHandle ? 'TRUE' : 'FALSE') . "\n\n";

// 2. Test callback mapping (from extractCategoryFromCallback method)
echo "2. Callback mapping test:\n";
$shortToLongMap = [
    'cat_ТабАвтом_ИзмСх' => 'category_Табель_ИзмСх_Автоматизация',
    'cat_ТабАвтом_МонтКб' => 'category_Табель_МонтКб_Автоматизация',
    'cat_ТабАвтом_РаскКб' => 'category_Табель_РаскКб_Автоматизация',
    'cat_ТабАвтом_МонтАСУ' => 'category_Табель_МонтАСУ_Автоматизация',
    'cat_ТабАвтом_УдалПО' => 'category_Табель_УдалПО_Автоматизация',
    'cat_ТабАвтом_Проч' => 'Табель_Прочее_Автоматизация'
];

$mappedCategory = isset($shortToLongMap[$testCallback]) ? $shortToLongMap[$testCallback] : $testCallback;
echo "  Original callback: $testCallback\n";
echo "  Mapped category: $mappedCategory\n\n";

// 3. Test handler condition matching
echo "3. Handler condition matching:\n";

// Check automation "Прочее" handler conditions
$handlerConditions = [
    '$category === "Табель_Прочее_Автоматизация"' => ($mappedCategory === 'Табель_Прочее_Автоматизация'),
    '$category === "cat_ТабАвтом_Проч"' => ($mappedCategory === 'cat_ТабАвтом_Проч')
];

foreach ($handlerConditions as $condition => $matches) {
    echo "  $condition: " . ($matches ? 'MATCH' : 'NO MATCH') . "\n";
}

$anyMatch = array_reduce($handlerConditions, function($carry, $item) { return $carry || $item; }, false);
echo "  Overall handler match: " . ($anyMatch ? 'MATCH' : 'NO MATCH') . "\n\n";

// 4. Test if there are competing handlers that might intercept the callback
echo "4. Potential competing handlers:\n";

// Check if it might match electrical equipment handlers
$electricalMatch = ($mappedCategory === 'cat_ТабЭлОб_Проч' || 
                   $mappedCategory === 'Табель_Прочее_ЭлектрОб' ||
                   $mappedCategory === 'ТабЭлОб_Проч' ||
                   $mappedCategory === 'Прочее_ЭлектрОб');
echo "  Electrical equipment handler: " . ($electricalMatch ? 'MATCH (INTERFERENCE!)' : 'NO MATCH') . "\n";

// Check if it might match fire alarm handlers
$fireAlarmMatch = ($mappedCategory === 'ТабПожСигн_Проч' ||
                  $mappedCategory === 'cat_ТабПожСигн_Проч' ||
                  $mappedCategory === 'Табель_Прочее_ПожарнаяСигнализация' ||
                  $mappedCategory === 'Табель_Прочее_ПожСигн');
echo "  Fire alarm equipment handler: " . ($fireAlarmMatch ? 'MATCH (INTERFERENCE!)' : 'NO MATCH') . "\n\n";

// 5. Show expected action
echo "5. Expected action:\n";
if ($anyMatch) {
    echo "  Should prompt for custom input\n";
    echo "  Should set conversation state:\n";
    echo "    - awaiting_custom_input = true\n";
    echo "    - custom_input_activity_type = 'Прочее_Автом'\n";
    echo "  Should send message: 'Пожалуйста, введите описание выполняемой работы:'\n";
} else {
    echo "  ERROR: No handler would match this callback!\n";
}

echo "\n=== END DIAGNOSTIC ===\n";