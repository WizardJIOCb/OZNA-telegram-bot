<?php
/**
 * Проверка размера сообщения с пагинацией
 */

echo "=== ПРОВЕРКА ПАГИНАЦИИ ===\n\n";

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
    
    $usersWithAccess = $adminService->getAllUsersWithAccess();
    $totalUsers = count($usersWithAccess);
    
    echo "Всего пользователей: {$totalUsers}\n\n";
    
    // Тестируем пагинацию
    $perPage = 20;
    $totalPages = (int)ceil($totalUsers / $perPage);
    
    echo "Пагинация: {$perPage} польз. на странице\n";
    echo "Всего страниц: {$totalPages}\n\n";
    
    // Тестируем первую страницу
    $page = 1;
    $offset = ($page - 1) * $perPage;
    $usersOnPage = array_slice($usersWithAccess, $offset, $perPage);
    
    echo "=== СТРАНИЦА 1 ===\n";
    echo "Пользователей на странице: " . count($usersOnPage) . "\n\n";
    
    // Создаем клавиатуру как в коде
    $message = "👥 <b>Пользователи с доступом</b>\n\n";
    $message .= "Выберите пользователя для просмотра деталей:\n";
    $message .= "Страница {$page} из {$totalPages} (Всего: {$totalUsers} польз.)\n\n";
    
    $keyboard = [];
    $row = [];
    
    foreach ($usersOnPage as $index => $user) {
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
        
        if (count($row) == 2 || $index == count($usersOnPage) - 1) {
            $keyboard[] = $row;
            $row = [];
        }
    }
    
    // Добавляем навигацию
    if ($totalPages > 1) {
        $navRow = [];
        $navRow[] = ['text' => "📖 {$page}/{$totalPages}", 'callback_data' => 'acc_lvl_manage_users_page_' . $page];
        $navRow[] = ['text' => '➡️ Вперед', 'callback_data' => "acc_lvl_manage_users_page_2"];
        $keyboard[] = $navRow;
    }
    
    $keyboard[] = [
        ['text' => '⬅️ Вернуться в меню', 'callback_data' => 'acc_lvl_users']
    ];
    
    // Проверяем размер
    $data = [
        'chat_id' => 123456789,
        'text' => $message,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode([
            'inline_keyboard' => $keyboard
        ])
    ];
    
    $fullRequestJson = json_encode($data, JSON_UNESCAPED_UNICODE);
    $fullRequestSize = strlen($fullRequestJson);
    
    echo "=== РЕЗУЛЬТАТЫ ===\n\n";
    echo "Кнопок на странице: " . (count($keyboard) - 2) * 2 . "\n";
    echo "Рядов кнопок: " . count($keyboard) . "\n";
    echo "Размер клавиатуры: " . number_format(strlen(json_encode($keyboard, JSON_UNESCAPED_UNICODE))) . " байт\n";
    echo "Размер полного запроса: " . number_format($fullRequestSize) . " байт\n\n";
    
    if ($fullRequestSize > 20000) {
        echo "❌ ПРОБЛЕМА: Размер все еще > 20KB!\n";
    } elseif ($fullRequestSize > 15000) {
        echo "⚠️ Размер > 15KB - на грани\n";
    } elseif ($fullRequestSize > 10000) {
        echo "⚠️ Размер > 10KB - приемлемо\n";
    } else {
        echo "✅ Размер в норме!\n";
    }
    
    echo "\n";
    echo "Сравнение:\n";
    echo "БЕЗ пагинации: ~20,751 байт (83 польз.)\n";
    echo "С пагинацией:  {$fullRequestSize} байт ({$perPage} польз. на стр.)\n";
    echo "Экономия:      " . number_format(20751 - $fullRequestSize) . " байт\n\n";
    
    if ($fullRequestSize < 10000) {
        echo "✅ РЕШЕНИЕ РАБОТАЕТ!\n";
        echo "Кнопка теперь будет работать правильно!\n";
    }
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
}
