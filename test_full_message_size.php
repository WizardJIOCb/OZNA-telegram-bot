<?php
/**
 * Проверка полного размера сообщения которое отправляется в Telegram
 */

echo "=== ПРОВЕРКА ПОЛНОГО РАЗМЕРА СООБЩЕНИЯ ===\n\n";

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
    
    echo "Формирование сообщения как в реальном коде...\n\n";
    
    $usersWithAccess = $adminService->getAllUsersWithAccess();
    
    $message = "👥 <b>Пользователи с доступом</b>\n\n";
    $message .= "Выберите пользователя для просмотра деталей:\n\n";
    
    // Создаем клавиатуру
    $keyboard = [];
    $row = [];
    
    foreach ($usersWithAccess as $index => $user) {
        $userId = $user['user_id'];
        $userData = $user['data'] ?? [];
        $fio = $userData['fio'] ?? 'Без ФИО';
        $accessLevel = $userData['access_level'] ?? 0;
        $levelName = $adminService->getAccessLevelName($accessLevel);
        
        $buttonText = "{$fio} ({$levelName})";
        
        if (mb_strlen($buttonText) > 30) {
            $buttonText = mb_substr($buttonText, 0, 27) . '...';
        }
        
        $row[] = ['text' => $buttonText, 'callback_data' => "acc_lvl_user_details_{$userId}"];
        
        if (count($row) == 2 || $index == count($usersWithAccess) - 1) {
            $keyboard[] = $row;
            $row = [];
        }
    }
    
    $keyboard[] = [
        ['text' => '⬅️ Вернуться в меню', 'callback_data' => 'acc_lvl_users']
    ];
    
    // Формируем полный запрос к Telegram API (как это делает библиотека)
    $data = [
        'chat_id' => 123456789, // Тестовый chat_id
        'text' => $message,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode([
            'inline_keyboard' => $keyboard
        ])
    ];
    
    $fullRequestJson = json_encode($data, JSON_UNESCAPED_UNICODE);
    $fullRequestSize = strlen($fullRequestJson);
    
    echo "=== РЕЗУЛЬТАТЫ ===\n\n";
    echo "Текст сообщения:\n";
    echo "Длина: " . strlen($message) . " байт\n";
    echo "Символов: " . mb_strlen($message) . "\n\n";
    
    echo "Клавиатура:\n";
    echo "Кнопок: " . (count($keyboard) * 2 - 1) . "\n";
    echo "Рядов: " . count($keyboard) . "\n";
    echo "Размер JSON: " . number_format(strlen(json_encode($keyboard, JSON_UNESCAPED_UNICODE))) . " байт\n\n";
    
    echo "Полный запрос к Telegram:\n";
    echo "Размер: " . number_format($fullRequestSize) . " байт\n\n";
    
    // Проверяем известные лимиты
    $warnings = [];
    
    if ($fullRequestSize > 20000) {
        $warnings[] = "❌ КРИТИЧНО: Размер запроса > 20KB - могут быть проблемы!";
    } elseif ($fullRequestSize > 15000) {
        $warnings[] = "⚠️ ПРЕДУПРЕЖДЕНИЕ: Размер запроса > 15KB - на грани!";
    } elseif ($fullRequestSize > 10000) {
        $warnings[] = "⚠️ Размер запроса > 10KB - может быть проблематично";
    }
    
    if (count($keyboard) > 50) {
        $warnings[] = "⚠️ Много рядов кнопок (" . count($keyboard) . ") - длинная прокрутка";
    }
    
    if (!empty($warnings)) {
        echo "ПРЕДУПРЕЖДЕНИЯ:\n";
        foreach ($warnings as $warning) {
            echo "{$warning}\n";
        }
        echo "\n";
    }
    
    // Симулируем что происходит
    echo "=== ЧТО МОЖЕТ ПРОИСХОДИТЬ ===\n\n";
    
    if ($fullRequestSize > 15000) {
        echo "🔴 ВЕРОЯТНАЯ ПРОБЛЕМА:\n";
        echo "Telegram может отклонять запросы такого размера!\n";
        echo "Сообщение не отправляется, но ошибка может не логироваться.\n\n";
        echo "РЕШЕНИЕ:\n";
        echo "Добавить ПАГИНАЦИЮ - показывать по 20-30 пользователей на странице.\n";
    } elseif ($fullRequestSize > 10000) {
        echo "🟡 ВОЗМОЖНАЯ ПРОБЛЕМА:\n";
        echo "Сообщение большое, может быть долгая отправка или таймаут.\n\n";
        echo "РЕКОМЕНДАЦИЯ:\n";
        echo "Добавить пагинацию для удобства пользователей.\n";
    } else {
        echo "🟢 Размер сообщения в норме.\n";
        echo "Проблема не в размере.\n";
    }
    
    echo "\n";
    echo "=== ДОПОЛНИТЕЛЬНАЯ ИНФОРМАЦИЯ ===\n\n";
    echo "Показать первые 5 кнопок:\n";
    for ($i = 0; $i < min(5, count($keyboard)); $i++) {
        foreach ($keyboard[$i] as $button) {
            echo "  [{$button['text']}] -> {$button['callback_data']} (" . strlen($button['callback_data']) . " байт)\n";
        }
    }
    echo "  ...\n";
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
