<?php
// Тест для str_starts_with

echo "Testing str_starts_with\n";
echo "=====================\n";

$callbackData = 'category_Профиль_Просмотр';
$prefix = 'category_Профиль_ФН';

$result = str_starts_with($callbackData, $prefix);
echo "str_starts_with('$callbackData', '$prefix'): " . ($result ? 'true' : 'false') . "\n";

// Проверим другие комбинации
$testCases = [
    ['category_Профиль_Просмотр', 'category_Профиль'],
    ['category_Профиль_Просмотр', 'category_Профиль_Просмотр'],
    ['category_Профиль_ФН_МобильнаяБригада', 'category_Профиль_ФН'],
    ['category_Профиль_ФН', 'category_Профиль_ФН'],
    ['category_Профиль_ФИО', 'category_Профиль_ФН'],
];

foreach ($testCases as [$data, $prefix]) {
    $result = str_starts_with($data, $prefix);
    echo "str_starts_with('$data', '$prefix'): " . ($result ? 'true' : 'false') . "\n";
}

echo "\nTest completed.\n";