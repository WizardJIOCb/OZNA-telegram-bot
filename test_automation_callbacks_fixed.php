<?php
// Test to verify that automation equipment confirmation callbacks are correctly formed with shortened names

$automationActivities = [
    'РабПоГр_Автом',
    'ФормИД_Автом',
    'ПодтВех_Автом',
    'ДопРаб_Автом',
    'ИзмСх_Автом',
    'МонтКб_Автом',
    'РаскКб_Автом',
    'МонтАСУ_Автом',
    'УдалПО_Автом',
    'Прочее_Автом'
];

echo "Testing automation equipment confirmation callbacks with shortened names:\n";
echo "===============================================================\n";

foreach ($automationActivities as $activity) {
    $callback = "category_Табель_{$activity}_yes";
    $length = strlen($callback);
    $status = $length <= 64 ? "OK" : "TOO LONG";
    echo sprintf("%-50s | Length: %2d | %s\n", $callback, $length, $status);
}

echo "\nAll callbacks should be OK (within 64 bytes limit).\n";