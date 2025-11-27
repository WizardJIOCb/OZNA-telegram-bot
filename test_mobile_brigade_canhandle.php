<?php

// Тест для проверки canHandle для мобильной бригады

$callbackData = 'cat_МобБриг_Электр';

echo "Testing callback: {$callbackData}\n\n";

// Проверки из canHandle
$checks = [
    "str_starts_with 'category_Табель'" => str_starts_with($callbackData, 'category_Табель'),
    "str_starts_with 'cat_ТабПНРаботы'" => str_starts_with($callbackData, 'cat_ТабПНРаботы'),
    "str_starts_with 'cat_Таб'" => str_starts_with($callbackData, 'cat_Таб'),
    "str_starts_with 'cat_МобБриг'" => str_starts_with($callbackData, 'cat_МобБриг'),
    "str_starts_with 'cat_Гарантии_'" => str_starts_with($callbackData, 'cat_Гарантии_'),
    "str_starts_with 'cat_ШМР_'" => str_starts_with($callbackData, 'cat_ШМР_'),
];

foreach ($checks as $check => $result) {
    $status = $result ? '✅ TRUE' : '❌ FALSE';
    echo "{$check}: {$status}\n";
}

echo "\nFinal canHandle result: ";
$canHandle = str_starts_with($callbackData, 'category_Табель') || 
             str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
             str_starts_with($callbackData, 'cat_Таб') ||
             str_starts_with($callbackData, 'cat_МобБриг') ||
             str_starts_with($callbackData, 'cat_Гарантии_') ||
             str_starts_with($callbackData, 'cat_ШМР_');

echo ($canHandle ? '✅ TRUE - БУДЕТ ОБРАБОТАН' : '❌ FALSE - НЕ БУДЕТ ОБРАБОТАН');
echo "\n";
