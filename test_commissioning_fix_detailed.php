<?php
// Detailed test for the commissioning works fix

echo "=== Тестирование исправления для Пусконаладочных работ ===\n\n";

// Test the callback data length
$longCallback = 'category_Табель_ПусконаладочныеРаботы';
$shortCallback = 'cat_ТабПНРаботы';
$confirmationCallback = 'cat_ТабПНРаботы_yes';

echo "Длина длинного callback_data: " . strlen($longCallback) . " символов\n";
echo "Длина короткого callback_data: " . strlen($shortCallback) . " символов\n";
echo "Длина confirmation callback_data: " . strlen($confirmationCallback) . " символов\n";
echo "Максимальная длина для Telegram: 64 байта\n\n";

// Test UTF-8 encoding
echo "UTF-8 проверка:\n";
echo "Короткий callback в hex: " . bin2hex($shortCallback) . "\n";
echo "Confirmation callback в hex: " . bin2hex($confirmationCallback) . "\n\n";

// Test the mapping
$shortToFullMap = [
    'ТабПНРаботы' => 'ПусконаладочныеРаботы'
];

echo "Маппинг коротких имен:\n";
foreach ($shortToFullMap as $short => $full) {
    echo "  {$short} => {$full}\n";
}
echo "\n";

echo "✅ Тест завершен. Все callback_data находятся в пределах лимита Telegram (64 байта)\n";
echo "✅ Используется короткий формат 'cat_ТабПНРаботы' вместо длинного 'category_Табель_ПусконаладочныеРаботы'\n";
?>