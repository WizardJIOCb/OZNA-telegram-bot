<?php

// Простой тест для отправки диагностического сообщения в Telegram

echo "=== ДИАГНОСТИКА ПРОБЛЕМЫ С КНОПКОЙ 'ПРОВЕРКА МОНТАЖНОЙ ПЛОЩАДКИ' ===\n\n";

// Настройки бота (замените на ваши данные)
$botToken = 'YOUR_BOT_TOKEN'; // Замените на токен вашего бота
$chatId = '-1002233817749'; // Замените на ID вашего чата

// Функция для отправки сообщения в Telegram
function sendTelegramMessage($botToken, $chatId, $message, $keyboard = null) {
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    if ($keyboard) {
        $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard]);
    }
    
    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return json_decode($result, true);
}

// Если токен не установлен, покажем инструкции
if ($botToken === 'YOUR_BOT_TOKEN') {
    echo "❌ НУЖНО НАСТРОИТЬ ТОКЕН БОТА!\n\n";
    echo "1. Откройте файл: c:\\ozna_bot\\simple_telegram_test.php\n";
    echo "2. Замените 'YOUR_BOT_TOKEN' на токен вашего бота\n";
    echo "3. Замените chat_id на ID вашего чата\n";
    echo "4. Запустите скрипт снова\n";
    exit();
}

echo "1. ОТПРАВЛЯЕМ ДИАГНОСТИЧЕСКОЕ СООБЩЕНИЕ...\n";

// Создаем диагностическое сообщение
$message = "🔍 <b>ДИАГНОСТИКА: Проверка монтажной площадки</b>\n\n";
$message .= "📋 <b>АНАЛИЗ ПРОБЛЕМЫ:</b>\n";
$message .= "• Кнопка не показывает диалог подтверждения\n";
$message .= "• Нужно проверить callback обработку\n\n";
$message .= "🧪 <b>ТЕСТОВЫЕ ДАННЫЕ:</b>\n";
$message .= "• Callback: cat_ШМР_АналФунд\n";
$message .= "• Ожидаемая category: Табель_ПроверкаМонтажнойПлощадки\n";
$message .= "• Должно появиться: диалог подтверждения\n\n";
$message .= "⏰ Время теста: " . date('H:i:s d.m.Y') . "\n";

// Создаем тестовую кнопку
$keyboard = [
    [
        ['text' => '🧪 ТЕСТ: Проверка монтажной площадки', 'callback_data' => 'cat_ШМР_АналФунд']
    ],
    [
        ['text' => '📊 Статус теста', 'callback_data' => 'test_status']
    ]
];

try {
    echo "Отправляем сообщение в Telegram...\n";
    $result = sendTelegramMessage($botToken, $chatId, $message, $keyboard);
    
    if (isset($result['ok']) && $result['ok']) {
        echo "✅ Диагностическое сообщение успешно отправлено!\n";
        echo "📱 Проверьте Telegram чат\n";
        echo "🎯 Нажмите на тестовую кнопку и проверьте результат\n\n";
        
        echo "=== ИНСТРУКЦИИ ДЛЯ ДИАГНОСТИКИ ===\n";
        echo "1. Откройте Telegram чат\n";
        echo "2. Найдите сообщение с диагностикой\n";
        echo "3. Нажмите '🧪 ТЕСТ: Проверка монтажной площадки'\n";
        echo "4. РЕЗУЛЬТАТЫ:\n";
        echo "   ✅ Если появился диалог подтверждения - исправление работает\n";
        echo "   ❌ Если ничего не происходит - проблема в callback обработке\n";
        echo "   ⚠️  Если появилась ошибка - проблема в маршрутизации\n\n";
        
        echo "5. ПРОВЕРЬТЕ ЛОГИ СЕРВЕРА:\n";
        echo "   Ищите сообщения с эмодзи 🏗️ в логах\n";
        echo "   Это покажет, где именно происходит сбой\n\n";
        
    } else {
        echo "❌ Ошибка отправки сообщения:\n";
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Критическая ошибка: " . $e->getMessage() . "\n";
}

echo "\n=== ДОПОЛНИТЕЛЬНАЯ ДИАГНОСТИКА ===\n";
echo "Если тестовая кнопка не работает, проблема может быть в:\n";
echo "1. 🔄 Неправильном сопоставлении callback'а в routeTabelCallback\n";
echo "2. 🚫 Отсутствии обработки в handleOtherWorkTypes\n";
echo "3. 💾 Отсутствии project_code в conversation\n";
echo "4. 🛠️ Ошибке в createConfirmationKeyboard\n";
echo "5. 🔧 Проблеме с MessageService\n\n";

echo "📞 Сообщите результаты тестирования для дальнейшей диагностики!\n";

?>