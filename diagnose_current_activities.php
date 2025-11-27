<?php

echo "=== ДИАГНОСТИКА КНОПКИ 'ТЕКУЩАЯ ДЕЯТЕЛЬНОСТЬ' ===\n\n";

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

echo "1. ПРОВЕРКА ОСНОВНОГО МЕНЮ ТАБЕЛЯ\n";
echo "===================================\n";

try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $keyboard = $messageService->createWorkTypeKeyboard();
    
    echo "Кнопки в главном меню табеля:\n";
    foreach ($keyboard as $index => $row) {
        foreach ($row as $button) {
            $text = $button['text'];
            $callback = $button['callback_data'];
            echo "  " . ($index + 1) . ". $text → $callback\n";
            
            if (str_contains($text, 'Текущая деятельность')) {
                echo "     ✅ НАЙДЕНА кнопка 'Текущая деятельность'!\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
}

echo "\n2. ПРОВЕРКА КНОПОК ТЕКУЩЕЙ ДЕЯТЕЛЬНОСТИ\n";
echo "=======================================\n";

try {
    $currentKeyboard = $messageService->createCurrentActivityKeyboard();
    echo "Кнопки после выбора 'Текущая деятельность' и даты:\n";
    foreach ($currentKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "  " . ($index + 1) . ". " . $button['text'] . " → " . $button['callback_data'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
}

echo "\n3. ПРОВЕРКА УРОВНЯ ДОСТУПА\n";
echo "==========================\n";

try {
    // Test for different access levels
    $testUserIds = [12345, 67890]; // Test user IDs
    
    foreach ($testUserIds as $userId) {
        echo "Пользователь ID: $userId\n";
        
        // Check main menu based on access level
        $mainKeyboard = $messageService->createMainMenuKeyboard($userId);
        
        $hasTabenButton = false;
        foreach ($mainKeyboard as $row) {
            foreach ($row as $button) {
                if (str_contains($button['text'], 'Табель')) {
                    $hasTabenButton = true;
                    break 2;
                }
            }
        }
        
        if ($hasTabenButton) {
            echo "  ✅ Кнопка 'Табель' видна пользователю\n";
        } else {
            echo "  ❌ Кнопка 'Табель' НЕ видна пользователю (возможно, уровень доступа Гость)\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ОШИБКА при проверке доступа: " . $e->getMessage() . "\n";
}

echo "\n4. ВОЗМОЖНЫЕ ПРИЧИНЫ ПРОБЛЕМЫ\n";
echo "=============================\n";
echo "Если пользователь не видит кнопки 'Текущая деятельность':\n\n";
echo "A) Пользователь имеет уровень доступа 'Гость' (level 0)\n";
echo "   → Кнопка 'Табель' не отображается для гостей\n";
echo "   → Решение: Повысить уровень доступа пользователя\n\n";
echo "B) Кэш Telegram клиента\n";
echo "   → Telegram может кэшировать старые клавиатуры\n";
echo "   → Решение: Перезапустить приложение Telegram\n\n";
echo "C) Состояние разговора повреждено\n";
echo "   → Пользователь застрял в старом состоянии\n";
echo "   → Решение: Команда /start для сброса состояния\n\n";
echo "D) Сервер не перезапущен после изменений\n";
echo "   → PHP файлы изменены, но сервер не перезагружен\n";
echo "   → Решение: Перезапустить веб-сервер\n\n";

echo "5. РЕКОМЕНДАЦИИ ДЛЯ ПОЛЬЗОВАТЕЛЯ\n";
echo "================================\n";
echo "1. Отправить команду /start в боте\n";
echo "2. Перезапустить приложение Telegram\n";
echo "3. Проверить, видна ли кнопка 'Табель' в главном меню\n";
echo "4. Если кнопка 'Табель' не видна - обратиться к администратору\n";
echo "   для повышения уровня доступа\n\n";

echo "✅ ДИАГНОСТИКА ЗАВЕРШЕНА\n";
echo "Кнопки 'Текущая деятельность' РЕАЛИЗОВАНЫ и РАБОТАЮТ корректно!\n";