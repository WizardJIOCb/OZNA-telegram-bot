<?php
// Test to verify that automation equipment confirmation callbacks are correctly formed

$automationActivities = [
    'РабПоГр_Автоматизация',
    'ФормИД_Автоматизация',
    'ПодтВех_Автоматизация',
    'ДопРаб_Автоматизация',
    'ИзмСх_Автоматизация',
    'МонтКб_Автоматизация',
    'РаскКб_Автоматизация',
    'МонтАСУ_Автоматизация',
    'УдалПО_Автоматизация',
    'Прочее_Автоматизация'
];

echo "Testing automation equipment confirmation callbacks:\n";
echo "=============================================\n";

foreach ($automationActivities as $activity) {
    $callback = "category_Табель_{$activity}_yes";
    $length = strlen($callback);
    $status = $length <= 64 ? "OK" : "TOO LONG";
    echo sprintf("%-50s | Length: %2d | %s\n", $callback, $length, $status);
}

echo "\nAll callbacks should be OK (within 64 bytes limit).\n";