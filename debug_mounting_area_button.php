<?php

echo "=== ДИАГНОСТИКА КНОПКИ 'ПРОВЕРКА МОНТАЖНОЙ ПЛОЩАДКИ' ===\n\n";

echo "1. ПРОВЕРКА ДЛИНЫ CALLBACK'А:\n";

// Проверяем текущий callback из MessageService
$currentCallback = 'cat_ШМР_АналФунд';
echo "   Текущий callback: '{$currentCallback}'\n";
echo "   Длина callback'а: " . strlen($currentCallback) . " символов\n";

// Telegram ограничение на callback_data
$telegramLimit = 64;
echo "   Лимит Telegram: {$telegramLimit} символов\n";
echo "   Статус: " . (strlen($currentCallback) <= $telegramLimit ? "✅ В пределах лимита" : "❌ ПРЕВЫШЕН ЛИМИТ") . "\n\n";

echo "2. ПРОВЕРКА ОБРАБОТКИ В TabelHandler:\n";

// Проверяем какой category должен обрабатываться
$expectedCategory = 'Табель_ПроверкаМонтажнойПлощадки';
echo "   Ожидаемая category: '{$expectedCategory}'\n";
echo "   Длина category: " . strlen($expectedCategory) . " символов\n\n";

echo "3. ПРОВЕРКА МАППИНГА CALLBACK'А:\n";

// Проверяем сопоставление в routeTabelCallback
$callbackMappings = [
    'cat_ШМР_АналФунд' => 'Табель_ПроверкаМонтажнойПлощадки'
];

if (isset($callbackMappings[$currentCallback])) {
    $mappedCategory = $callbackMappings[$currentCallback];
    echo "   ✅ Callback сопоставлен с: '{$mappedCategory}'\n";
} else {
    echo "   ❌ ПРОБЛЕМА: Callback НЕ сопоставлен!\n";
}

echo "\n4. ПРОВЕРКА CONFIRMATION KEYBOARD:\n";

// Проверяем какой callback используется для подтверждения
$confirmationCallback = 'cat_ШМР_АналФунд_yes';
echo "   Callback подтверждения: '{$confirmationCallback}'\n";
echo "   Длина callback подтверждения: " . strlen($confirmationCallback) . " символов\n";
echo "   В пределах лимита: " . (strlen($confirmationCallback) <= $telegramLimit ? "✅ ДА" : "❌ НЕТ") . "\n\n";

echo "5. РЕКОМЕНДАЦИИ ПО ИСПРАВЛЕНИЮ:\n";

if (strlen($currentCallback) > $telegramLimit || strlen($confirmationCallback) > $telegramLimit) {
    echo "   ❌ ПРОБЛЕМА: Превышен лимит длины callback'а\n";
    echo "   РЕШЕНИЕ: Использовать более короткие callback'ы\n";
    echo "   Предложение: 'cat_МП' вместо 'cat_ШМР_АналФунд'\n";
} else {
    echo "   ✅ Длина callback'ов в норме\n";
    echo "   ВОЗМОЖНЫЕ ПРИЧИНЫ:\n";
    echo "   1. Неправильное сопоставление в routeTabelCallback\n";
    echo "   2. Ошибка в обработке category\n";
    echo "   3. Проблема с createConfirmationKeyboard\n";
}

echo "\n6. ТЕСТ ОБРАБОТКИ CALLBACK'А:\n";

// Имитируем обработку в routeTabelCallback
function testCallbackRouting($callbackData) {
    echo "   Тестируем: '{$callbackData}'\n";
    
    // Проверяем основные маппинги
    $callbackMappings = [
        'cat_ШМР_АналФунд' => 'Табель_ПроверкаМонтажнойПлощадки'
    ];
    
    if (isset($callbackMappings[$callbackData])) {
        $category = $callbackMappings[$callbackData];
        echo "   ✅ Сопоставлен с: '{$category}'\n";
        
        // Проверяем обработку в handleOtherWorkTypes
        if ($category === 'Табель_ПроверкаМонтажнойПлощадки') {
            echo "   ✅ Должен показать диалог подтверждения\n";
            echo "   Confirmation callback: 'cat_ШМР_АналФунд_yes'\n";
        } else {
            echo "   ❌ НЕ будет обработан правильно\n";
        }
    } else {
        echo "   ❌ НЕ найден в маппинге!\n";
    }
}

testCallbackRouting($currentCallback);

echo "\n=== СТАТУС ДИАГНОСТИКИ ===\n";
echo "Проблема скорее всего в:\n";
echo "1. Неправильном сопоставлении callback'а в routeTabelCallback\n";
echo "2. Или в отсутствии обработки новой category\n";
echo "3. Длина callback'ов пока в пределах нормы\n";

?>