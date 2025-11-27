<?php

require_once __DIR__ . '/custom_autoload.php';

// Test the ProjectSelector date selection functionality
$projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();

// Test cases for date selection
$testCases = [
    'category_Табель_Сегодня',
    'category_Табель_Вчера',
    'category_Табель_Позавчера',
    'category_Табель_ДругаяДата',
    'category_Табель_ПНЛ_Сегодня',
    'category_Табель_ШМ_Вчера',
    'category_Табель_Текущая_Позавчера',
    'category_Табель_МобБриг_ДругаяДата'
];

echo "Testing ProjectSelector date selection functionality:\n\n";

foreach ($testCases as $testCase) {
    echo "Testing callback: {$testCase}\n";
    
    // Test isDateSelection method
    $isDateSelection = $projectSelector->isDateSelection($testCase);
    echo "isDateSelection: " . ($isDateSelection ? 'YES' : 'NO') . "\n";
    
    // Test extractDate method
    $extractedDate = $projectSelector->extractDate($testCase);
    echo "extractDate: " . ($extractedDate ?? 'NULL') . "\n";
    
    echo "---\n";
}

// Test some non-date callbacks to make sure we didn't break anything
$nonDateCases = [
    'category_Табель',
    'category_Табель_Мобильная_Бригада',
    'category_Табель_ШифрПроекта_ОИ834'
];

echo "\nTesting non-date callbacks:\n\n";

foreach ($nonDateCases as $testCase) {
    echo "Testing callback: {$testCase}\n";
    
    // Test isDateSelection method
    $isDateSelection = $projectSelector->isDateSelection($testCase);
    echo "isDateSelection: " . ($isDateSelection ? 'YES' : 'NO') . "\n";
    
    echo "---\n";
}