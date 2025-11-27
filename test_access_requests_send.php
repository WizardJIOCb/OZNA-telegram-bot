<?php
// Тестовый скрипт для проверки отправки сообщения с заявками на доступ

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Начинаем тест отправки сообщения с заявками на доступ...\n";

try {
    // Создаем экземпляры сервисов
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Получаем все заявки
    $requests = $adminService->getAllAccessRequests();
    
    echo "Количество заявок: " . count($requests) . "\n";
    
    if (!empty($requests)) {
        foreach ($requests as $index => $request) {
            echo "Заявка #" . ($index + 1) . ":\n";
            echo "  User ID: " . ($request['user_id'] ?? 'N/A') . "\n";
            echo "  User Name: " . ($request['user_name'] ?? 'N/A') . "\n";
            echo "  FIO: " . ($request['user_fio'] ?? 'N/A') . "\n";
            echo "  Telegram Name: " . ($request['user_telegram_name'] ?? 'N/A') . "\n";
            echo "  Chat ID: " . ($request['chat_id'] ?? 'N/A') . "\n";
            echo "  Timestamp: " . ($request['timestamp'] ?? 'N/A') . "\n\n";
        }
    } else {
        echo "Заявок нет.\n";
    }
    
    // Тестируем формирование сообщения
    $text = "👥 <b>Заявки на доступ к админке</b>\n\n";
    $text .= "Всего заявок: " . count($requests) . "\n\n";
    
    if (empty($requests)) {
        $text .= "Нет новых заявок на доступ.";
        echo "Формируем сообщение для пустого списка заявок:\n";
        echo $text . "\n";
    } else {
        $text .= "Выберите заявку для просмотра:\n\n";
        echo "Формируем сообщение для непустого списка заявок:\n";
        echo $text . "\n";
    }
    
    echo "Тест завершен успешно!\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>