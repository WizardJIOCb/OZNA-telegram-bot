<?php
/**
 * Проверка размера клавиатуры для списка пользователей
 */

echo "=== ПРОВЕРКА РАЗМЕРА КЛАВИАТУРЫ ===\n\n";

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

try {
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "Получение списка пользователей...\n";
    $usersWithAccess = $adminService->getAllUsersWithAccess();
    echo "Найдено пользователей: " . count($usersWithAccess) . "\n\n";
    
    // Создаем клавиатуру как в коде
    $keyboard = [];
    $row = [];
    $totalButtons = 0;
    $problemButtons = [];
    
    foreach ($usersWithAccess as $index => $user) {
        $userId = $user['user_id'];
        $userData = $user['data'] ?? [];
        $fio = $userData['fio'] ?? 'Без ФИО';
        $accessLevel = $userData['access_level'] ?? 0;
        $levelName = $adminService->getAccessLevelName($accessLevel);
        
        $buttonText = "{$fio} ({$levelName})";
        
        // Ограничиваем длину текста кнопки
        if (mb_strlen($buttonText) > 30) {
            $buttonText = mb_substr($buttonText, 0, 27) . '...';
        }
        
        $callbackData = "acc_lvl_user_details_{$userId}";
        
        // Проверяем длину callback_data
        $callbackLength = strlen($callbackData);
        if ($callbackLength > 64) {
            $problemButtons[] = [
                'user' => $fio,
                'callback' => $callbackData,
                'length' => $callbackLength
            ];
        }
        
        $row[] = ['text' => $buttonText, 'callback_data' => $callbackData];
        $totalButtons++;
        
        // Каждые 2 кнопки создаем новый ряд
        if (count($row) == 2 || $index == count($usersWithAccess) - 1) {
            $keyboard[] = $row;
            $row = [];
        }
    }
    
    // Добавляем кнопку "Назад"
    $keyboard[] = [
        ['text' => '⬅️ Вернуться в меню', 'callback_data' => 'acc_lvl_users']
    ];
    $totalButtons++;
    
    echo "=== РЕЗУЛЬТАТЫ ПРОВЕРКИ ===\n\n";
    echo "Всего кнопок: {$totalButtons}\n";
    echo "Количество рядов: " . count($keyboard) . "\n\n";
    
    // Проверяем лимиты Telegram
    if ($totalButtons > 100) {
        echo "❌ ПРОБЛЕМА: Слишком много кнопок!\n";
        echo "   Telegram ограничивает до 100 кнопок\n";
        echo "   У вас: {$totalButtons} кнопок\n";
        echo "   Превышение: " . ($totalButtons - 100) . " кнопок\n\n";
    } else {
        echo "✅ Количество кнопок в норме ({$totalButtons} <= 100)\n\n";
    }
    
    if (!empty($problemButtons)) {
        echo "❌ ПРОБЛЕМА: Найдены кнопки с callback_data > 64 байт:\n\n";
        foreach ($problemButtons as $pb) {
            echo "   Пользователь: {$pb['user']}\n";
            echo "   Callback: {$pb['callback']}\n";
            echo "   Длина: {$pb['length']} байт (лимит: 64)\n";
            echo "   Превышение: " . ($pb['length'] - 64) . " байт\n\n";
        }
    } else {
        echo "✅ Все callback_data в пределах лимита (64 байта)\n\n";
    }
    
    // Проверяем общий размер JSON
    $keyboardJson = json_encode($keyboard, JSON_UNESCAPED_UNICODE);
    $keyboardSize = strlen($keyboardJson);
    echo "Размер клавиатуры (JSON): " . number_format($keyboardSize) . " байт\n";
    
    if ($keyboardSize > 10000) {
        echo "⚠️ ПРЕДУПРЕЖДЕНИЕ: Клавиатура очень большая!\n";
        echo "   Это может вызывать проблемы с отправкой\n\n";
    }
    
    echo "\n=== ВЫВОД ===\n\n";
    
    if ($totalButtons > 100 || !empty($problemButtons)) {
        echo "❌ НАЙДЕНА ПРОБЛЕМА!\n\n";
        echo "Список пользователей НЕ МОЖЕТ быть отображен из-за:\n";
        if ($totalButtons > 100) {
            echo "- Слишком много кнопок (превышение лимита Telegram)\n";
        }
        if (!empty($problemButtons)) {
            echo "- Некоторые callback_data превышают 64 байта\n";
        }
        echo "\n";
        echo "РЕШЕНИЕ:\n";
        echo "Нужно добавить ПАГИНАЦИЮ (разбить список на страницы)\n";
        echo "Например: показывать по 20-30 пользователей на странице\n";
    } else {
        echo "✅ Клавиатура должна работать!\n";
        echo "Проблема не в размере клавиатуры.\n";
    }
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
}
