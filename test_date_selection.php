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
    $isDateSelection = $projectSelector->isDateSelection($testCase);
    $extractedDate = $projectSelector->extractDate($testCase);
    
    echo "Callback: {$testCase}\n";
    echo "Is date selection: " . ($isDateSelection ? 'YES' : 'NO') . "\n";
    echo "Extracted date: " . ($extractedDate ?? 'NULL') . "\n";
    echo "---\n";
}