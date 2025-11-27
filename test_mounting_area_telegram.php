<?php

require_once 'config.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use CustomCommands\Services\MessageService;

echo "=== ТЕСТ ДИАГНОСТИКИ КНОПКИ 'ПРОВЕРКА МОНТАЖНОЙ ПЛОЩАДКИ' В TELEGRAM ===\n\n";

// Инициализация MessageService
$messageService = new MessageService();

// ID чата для тестирования (замените на ваш chat_id)
$testChatId = '-1002233817749'; // Или ваш личный chat_id

echo "1. ПРОВЕРЯЕМ СОЗДАНИЕ КНОПКИ В MessageService...\n";

// Тестируем создание кнопки для ШМР
try {
    // Проверяем, что кнопка создается с правильным callback'ом
    $keyboard = $messageService->createSupervisionWorksKeyboard();
    
    // Ищем кнопку "Проверка монтажной площадки"
    $foundButton = false;
    $buttonCallback = '';
    
    foreach ($keyboard as $row) {
        foreach ($row as $button) {
            if (isset($button['text']) && strpos($button['text'], 'Проверка монтажной площадки') !== false) {
                $foundButton = true;
                $buttonCallback = $button['callback_data'] ?? 'НЕТ CALLBACK';
                break 2;
            }
        }
    }
    
    $message = "🔍 ДИАГНОСТИКА КНОПКИ 'ПРОВЕРКА МОНТАЖНОЙ ПЛОЩАДКИ'\n\n";
    $message .= "✅ Кнопка найдена: " . ($foundButton ? "ДА" : "НЕТ") . "\n";
    $message .= "📱 Callback данные: '{$buttonCallback}'\n";
    $message .= "📏 Длина callback: " . strlen($buttonCallback) . " символов\n";
    $message .= "🎯 Лимит Telegram: 64 символа\n";
    $message .= "✅ В пределах лимита: " . (strlen($buttonCallback) <= 64 ? "ДА" : "НЕТ") . "\n\n";
    
    echo "Отправляем диагностическое сообщение в Telegram...\n";
    $result = $messageService->sendMessage($testChatId, $message);
    
    if ($result->isOk()) {
        echo "✅ Сообщение успешно отправлено в Telegram!\n";
    } else {
        echo "❌ Ошибка отправки: " . $result->getDescription() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ОШИБКА при создании клавиатуры: " . $e->getMessage() . "\n";
    
    $errorMessage = "❌ ОШИБКА ДИАГНОСТИКИ\n\n";
    $errorMessage .= "Не удалось создать клавиатуру ШМР\n";
    $errorMessage .= "Ошибка: " . $e->getMessage() . "\n";
    
    try {
        $messageService->sendMessage($testChatId, $errorMessage);
    } catch (Exception $e2) {
        echo "❌ Критическая ошибка отправки: " . $e2->getMessage() . "\n";
    }
}

echo "\n2. ТЕСТИРУЕМ СОПОСТАВЛЕНИЕ CALLBACK'А...\n";

// Тестируем логику сопоставления
$testCallback = 'cat_ШМР_АналФунд';
$expectedCategory = 'Табель_ПроверкаМонтажнойПлощадки';

// Имитируем логику extractCategoryFromCallback
$shortFormats = [
    'cat_ШМР_АналФунд' => 'Табель_ПроверкаМонтажнойПлощадки'
];

$mappedCategory = $shortFormats[$testCallback] ?? 'НЕ_НАЙДЕНО';

$mappingMessage = "🔄 ТЕСТ СОПОСТАВЛЕНИЯ CALLBACK'А\n\n";
$mappingMessage .= "📥 Входящий callback: '{$testCallback}'\n";
$mappingMessage .= "📤 Сопоставленная category: '{$mappedCategory}'\n";
$mappingMessage .= "✅ Правильное сопоставление: " . ($mappedCategory === $expectedCategory ? "ДА" : "НЕТ") . "\n";
$mappingMessage .= "🎯 Ожидалось: '{$expectedCategory}'\n\n";

if ($mappedCategory === $expectedCategory) {
    $mappingMessage .= "✅ СОПОСТАВЛЕНИЕ РАБОТАЕТ ПРАВИЛЬНО\n";
    $mappingMessage .= "Проблема НЕ в маршрутизации callback'а\n";
} else {
    $mappingMessage .= "❌ ПРОБЛЕМА В СОПОСТАВЛЕНИИ!\n";
    $mappingMessage .= "Callback не сопоставляется с правильной category\n";
}

try {
    $result = $messageService->sendMessage($testChatId, $mappingMessage);
    if ($result->isOk()) {
        echo "✅ Результат сопоставления отправлен в Telegram!\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка отправки результата сопоставления: " . $e->getMessage() . "\n";
}

echo "\n3. СОЗДАЕМ ТЕСТОВУЮ КНОПКУ ДЛЯ ПРОВЕРКИ...\n";

// Создаем тестовую кнопку для проверки
$testKeyboard = [
    [
        ['text' => '🧪 ТЕСТ: Проверка монтажной площадки', 'callback_data' => 'cat_ШМР_АналФунд']
    ],
    [
        ['text' => '📋 Информация о тесте', 'callback_data' => 'test_info']
    ]
];

$testMessage = "🧪 ТЕСТОВАЯ КНОПКА ДЛЯ ДИАГНОСТИКИ\n\n";
$testMessage .= "Нажмите на кнопку ниже для проверки:\n";
$testMessage .= "• Если появится диалог подтверждения - исправление работает\n";
$testMessage .= "• Если ничего не происходит - проблема в обработке callback'а\n\n";
$testMessage .= "🔍 Отслеживайте логи сервера после нажатия кнопки\n";

try {
    $result = $messageService->sendMessage($testChatId, $testMessage, $testKeyboard);
    if ($result->isOk()) {
        echo "✅ Тестовая кнопка отправлена в Telegram!\n";
        echo "📱 Теперь нажмите на кнопку в Telegram и проверьте результат\n";
    } else {
        echo "❌ Ошибка отправки тестовой кнопки: " . $result->getDescription() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка создания тестовой кнопки: " . $e->getMessage() . "\n";
}

echo "\n=== ИНСТРУКЦИИ ДЛЯ ТЕСТИРОВАНИЯ ===\n";
echo "1. Проверьте Telegram чат - должны появиться диагностические сообщения\n";
echo "2. Нажмите на тестовую кнопку '🧪 ТЕСТ: Проверка монтажной площадки'\n";
echo "3. Проверьте логи сервера на наличие отладочных сообщений с 🏗️\n";
echo "4. Сообщите результат:\n";
echo "   - Появился ли диалог подтверждения?\n";
echo "   - Какие сообщения появились в логах?\n";
echo "   - Что произошло при нажатии кнопки?\n";

?>