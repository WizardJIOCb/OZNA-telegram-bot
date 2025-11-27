<?php

echo "=== СИМУЛЯЦИЯ РЕАЛЬНОГО НАЖАТИЯ КНОПКИ 'ДА' ===\n";

// Load required classes
require_once __DIR__ . '/vendor/autoload.php';

echo "1. Тестируем различные callback'и кнопки 'Да':\n\n";

// Test various "Yes" button callbacks that should work
$testCallbacks = [
    'cat_ТабЭлОб_РабПоГр_yes' => 'Электрооборудование - Работы по графику',
    'cat_ТабАвтом_РабПоГр_yes' => 'Автоматизация - Работы по графику', 
    'cat_ТабПожСигн_РабПоГр_yes' => 'Пожарная сигнализация - Работы по графику',
    'cat_ТабВентОтопл_РабПоГр_yes' => 'Вентиляция и отопление - Работы по графику',
    'cat_ТабТехОб_РабПоГр_yes' => 'Технологическое оборудование - Работы по графику',
    'cat_ТабМетрол_РабПоГр_yes' => 'Метрология - Работы по графику',
    'cat_Гарантии_ЭлектрОб_yes' => 'Гарантийные работы - Электрооборудование',
    'cat_ШМР_РабПоГр_yes' => 'Шеф-монтаж - Работы по графику'
];

// Since we can't instantiate the full TabelHandler without database connections,
// let's manually implement the key isActivityExecution logic to test it
function testIsActivityExecution($category) {
    echo "  Тестируем: '$category'\n";
    
    // The same logic as in TabelHandler.isActivityExecution()
    if (!str_contains($category, '_yes')) {
        echo "    ❌ НЕ содержит '_yes'\n";
        return false;
    }
    
    // Handle short callback format for electrical equipment
    if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $category, $matches)) {
        echo "    ✅ Электрооборудование: {$matches[1]}\n";
        return true;
    }
    
    // Handle short callback format for automation equipment
    if (preg_match('/^cat_ТабАвтом_(.+)_yes$/u', $category, $matches)) {
        echo "    ✅ Автоматизация: {$matches[1]}\n";
        return true;
    }
    
    // Handle short callback format for fire alarm equipment
    if (preg_match('/^cat_ТабПожСигн_(.+)_yes$/u', $category, $matches)) {
        echo "    ✅ Пожарная сигнализация: {$matches[1]}\n";
        return true;
    }
    
    // Handle short callback format for heating and ventilation equipment
    if (preg_match('/^cat_ТабВентОтопл_(.+)_yes$/u', $category, $matches)) {
        echo "    ✅ Вентиляция и отопление: {$matches[1]}\n";
        return true;
    }
    
    // Handle short callback format for technological equipment
    if (preg_match('/^cat_ТабТехОб_(.+)_yes$/u', $category, $matches)) {
        echo "    ✅ Технологическое оборудование: {$matches[1]}\n";
        return true;
    }
    
    // Handle short callback format for metrology equipment
    if (preg_match('/^cat_ТабМетрол_(.+)_yes$/u', $category, $matches)) {
        echo "    ✅ Метрология: {$matches[1]}\n";
        return true;
    }
    
    // Handle short callback format for warranty equipment
    if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $category, $matches)) {
        echo "    ✅ Гарантийное оборудование: {$matches[1]}\n";
        return true;
    }
    
    // Handle short callback format for supervision installation works
    if (preg_match('/^cat_ШМР_(.+)_yes$/u', $category, $matches)) {
        echo "    ✅ Шеф-монтажные работы: {$matches[1]}\n";
        return true;
    }
    
    echo "    ❌ НЕ совпадает ни с одним паттерном\n";
    return false;
}

foreach ($testCallbacks as $callback => $description) {
    echo "\n$description:\n";
    $result = testIsActivityExecution($callback);
    echo "  Результат: " . ($result ? 'УСПЕХ ✅' : 'ОШИБКА ❌') . "\n";
}

echo "\n2. Проверяем потенциальные проблемы с encoding/UTF-8:\n\n";

// Test potential encoding issues
$testEncodingCallbacks = [
    'cat_ТабЭлОб_РабПоГр_yes',
    'cat_ТабАвтом_РабПоГр_yes'
];

foreach ($testEncodingCallbacks as $callback) {
    echo "Callback: '$callback'\n";
    echo "  Длина: " . strlen($callback) . " байт\n";
    echo "  UTF-8 валидный: " . (mb_check_encoding($callback, 'UTF-8') ? 'ДА' : 'НЕТ') . "\n";
    echo "  Hex представление: " . bin2hex($callback) . "\n";
    echo "  Содержит '_yes': " . (str_contains($callback, '_yes') ? 'ДА' : 'НЕТ') . "\n";
    echo "\n";
}

echo "3. ЗАКЛЮЧЕНИЕ:\n";
echo "Если все тесты проходят успешно, но кнопки 'Да' все равно не работают,\n";
echo "то проблема может быть в:\n";
echo "- Ошибках в handleActivityExecution метоѕde\n";
echo "- Проблемах с базой данных в TabelEntry.addEntry\n";
echo "- Проблемах с форматированием сообщений в TabelEntry.createTabelMessage\n";
echo "- Проблемах с отправкой уведомлений\n";

?>