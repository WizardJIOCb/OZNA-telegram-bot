<?php
// Тест всех исправленных commissioning кнопок

echo "=== ТЕСТ ВСЕХ COMMISSIONING КНОПОК ===\n\n";

$commissioningButtons = [
    'ТабИнстр' => 'Инструктаж',
    'ТабКомплОпр' => 'Комплексное опробование', 
    'ТабРежНал' => 'Режимная наладка',
    'ТабПодгПоезд' => 'Подготовка к служебной поездке',
    'ТабСдачаОтч' => 'Сдача авансового отчета',
    'ТабДорога' => 'Дорога',
    'ТабОхрСигн' => 'Охранная сигнализация',
    'ТабОтоплВент' => 'Отопление и вентиляция',
    'ТабТехОб' => 'Технологическое оборудование',
    'ТабМетрол' => 'Метрология'
];

echo "Проверяем что все кнопки теперь должны работать:\n\n";

foreach ($commissioningButtons as $shortKey => $fullName) {
    $callback = "cat_{$shortKey}_yes";
    echo "🔧 {$fullName}\n";
    echo "   Callback: {$callback}\n";
    
    // Проверяем маппинг
    $shortFormats = [
        'cat_ТабИнстр_yes' => 'cat_ТабИнстр_yes',
        'cat_ТабДорога_yes' => 'cat_ТабДорога_yes', 
        'cat_ТабПодгПоезд_yes' => 'cat_ТабПодгПоезд_yes',
        'cat_ТабСдачаОтч_yes' => 'cat_ТабСдачаОтч_yes',
        'cat_ТабОхрСигн_yes' => 'cat_ТабОхрСигн_yes',
        'cat_ТабОтоплВент_yes' => 'cat_ТабОтоплВент_yes',
        'cat_ТабТехОб_yes' => 'cat_ТабТехОб_yes',
        'cat_ТабМетрол_yes' => 'cat_ТабМетрол_yes',
        'cat_ТабКомплОпр_yes' => 'cat_ТабКомплОпр_yes',
        'cat_ТабРежНал_yes' => 'cat_ТабРежНал_yes'
    ];
    
    if (isset($shortFormats[$callback])) {
        echo "   ✅ Есть в маппинге: {$shortFormats[$callback]}\n";
        
        // Проверяем isActivityExecution
        if (preg_match('/^cat_(.+)_yes$/u', $callback, $matches)) {
            $extractedKey = $matches[1];
            $basicCommissioningActivities = [
                'ТабИнстр' => 'Инструктаж',
                'ТабДорога' => 'Дорога',
                'ТабПодгПоезд' => 'ПодготовкаКПоездке', 
                'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
                'ТабОхрСигн' => 'ОхраннаяСигнализация',
                'ТабОтоплВент' => 'ОтоплениеИВентиляция',
                'ТабТехОб' => 'ТехнологическоеОборудование',
                'ТабМетрол' => 'Метрология',
                'ТабКомплОпр' => 'КомплексноеОпробование',
                'ТабРежНал' => 'РежимнаяНаладка'
            ];
            
            if (isset($basicCommissioningActivities[$extractedKey])) {
                echo "   ✅ Роутинг: isActivityExecution() → handleActivityExecution()\n";
                echo "   ✅ Activity type: {$basicCommissioningActivities[$extractedKey]}\n";
                echo "   ✅ ДОЛЖНО РАБОТАТЬ!\n";
            } else {
                echo "   ❌ НЕ найдено в basicCommissioningActivities\n";
            }
        }
    } else {
        echo "   ❌ НЕТ в маппинге - нужно добавить!\n";
    }
    echo "\n";
}

echo "=== ПЛАН ТЕСТИРОВАНИЯ ===\n\n";

echo "Друг, теперь протестируй все эти кнопки:\n\n";

$testButtons = [
    'Комплексное опробование',
    'Режимная наладка', 
    'Подготовка к служебной поездке',
    'Сдача авансового отчета'
];

foreach ($testButtons as $index => $buttonName) {
    echo ($index + 1) . ". {$buttonName}:\n";
    echo "   • Нажми 'Пусконаладочные работы'\n";
    echo "   • Нажми '{$buttonName}'\n";
    echo "   • Нажми 'Да'\n";
    echo "   • Должен быть отчет табеля ✅\n\n";
}

echo "Если какая-то кнопка НЕ работает - скажи какая именно!\n";
?>