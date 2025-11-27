<?php

// Test automation "Прочее" callback handling
$testCallback = 'cat_ТабАвтом_Проч';

echo "Testing callback: $testCallback\n";

// Test if it matches canHandle patterns
$patterns = [
    'category_Табель' => str_starts_with($testCallback, 'category_Табель'),
    'cat_ТабПНРаботы' => str_starts_with($testCallback, 'cat_ТабПНРаботы'), 
    'cat_Таб' => str_starts_with($testCallback, 'cat_Таб')
];

echo "Pattern matching results:\n";
foreach ($patterns as $pattern => $matches) {
    echo "  $pattern: " . ($matches ? 'MATCH' : 'NO MATCH') . "\n";
}

// Check if any pattern matches
$canHandle = $patterns['category_Табель'] || $patterns['cat_ТабПНРаботы'] || $patterns['cat_Таб'];
echo "\nOverall canHandle result: " . ($canHandle ? 'TRUE' : 'FALSE') . "\n";

// Test the callback data length
echo "\nCallback length: " . strlen($testCallback) . " bytes (limit: 64)\n";

// Test all automation additional works callbacks
$automationCallbacks = [
    'cat_ТабАвтом_ИзмСх',
    'cat_ТабАвтом_МонтКб', 
    'cat_ТабАвтом_РаскКб',
    'cat_ТабАвтом_МонтАСУ',
    'cat_ТабАвтом_УдалПО',
    'cat_ТабАвтом_Проч'
];

echo "\nTesting all automation callbacks:\n";
foreach ($automationCallbacks as $callback) {
    $matches = str_starts_with($callback, 'cat_Таб');
    echo "  $callback: " . ($matches ? 'MATCH' : 'NO MATCH') . " (" . strlen($callback) . " bytes)\n";
}