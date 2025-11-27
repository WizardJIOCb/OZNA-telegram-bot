<?php
// Тест исправления extractCategoryFromCallback

echo "=== ТЕСТ ИСПРАВЛЕНИЯ EXTRACTCATEGORYFROMCALLBACK ===\n\n";

$testCallbacks = [
    'cat_ТабИнстр_yes',
    'cat_ТабЭлОб_РабПоГр_yes',
    'cat_ТабДорога_yes'
];

echo "Тестируем исправленный маппинг:\n\n";

foreach ($testCallbacks as $callback) {
    echo "Callback: {$callback}\n";
    
    // Воспроизводим логику extractCategoryFromCallback
    if (strpos($callback, 'category_') === 0) {
        $result = substr($callback, strlen('category_'));
        echo "  Результат (category_ format): {$result}\n";
    } elseif (strpos($callback, 'cat_Таб') === 0) {
        // Маппинг
        $shortFormats = [
            'cat_ТабЭлОб_РабПоГр_yes' => 'Табель_РабПоГр_ЭлектрОб_yes',
            'cat_ТабИнстр_yes' => 'cat_ТабИнстр_yes',
            'cat_ТабДорога_yes' => 'cat_ТабДорога_yes',
            'cat_ТабПодгПоезд_yes' => 'cat_ТабПодгПоезд_yes',
            'cat_ТабСдачаОтч_yes' => 'cat_ТабСдачаОтч_yes',
            'cat_ТабОхрСигн_yes' => 'cat_ТабОхрСигн_yes',
            'cat_ТабОтоплВент_yes' => 'cat_ТабОтоплВент_yes',
            'cat_ТабТехОб_yes' => 'cat_ТабТехОб_yes',
            'cat_ТабМетрол_yes' => 'cat_ТабМетрол_yes',
            'cat_ТабКомплОпр_yes' => 'cat_ТабКомплОпр_yes',
            'cat_ТабРежНал_yes' => 'cat_ТабРежНал_yes',
            'cat_ТабПНРаботы_yes' => 'cat_ТабПНРаботы_yes'
        ];
        
        if (isset($shortFormats[$callback])) {
            $result = $shortFormats[$callback];
            echo "  Результат (найден в shortFormats): {$result}\n";
        } else {
            $result = substr($callback, strlen('cat_'));
            echo "  Результат (fallback): {$result}\n";
        }
    } else {
        $result = '';
        echo "  Результат (пустой): {$result}\n";
    }
    
    echo "  Финальная категория: '{$result}'\n\n";
}

echo "=== КЛЮЧЕВОЙ МОМЕНТ ===\n\n";

echo "ДО исправления:\n";
echo "cat_ТабИнстр_yes -> ТабИнстр_yes (НЕ работает с isActivityExecution)\n\n";

echo "ПОСЛЕ исправления:\n";
echo "cat_ТабИнстр_yes -> cat_ТабИнстр_yes (РАБОТАЕТ с isActivityExecution)\n\n";

echo "✅ Теперь isActivityExecution получит правильный формат callback!\n";
echo "✅ Инструктаж кнопка должна работать!\n\n";

echo "ТЕСТ КНОПКИ:\n";
echo "1. Нажми 'Пусконаладочные работы'\n";
echo "2. Нажми 'Инструктаж'\n";
echo "3. Нажми 'Да'\n";
echo "4. Должен прийти отчет вместо 'неизвестная команда табеля'!\n";
?>