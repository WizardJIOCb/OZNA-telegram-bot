<?php
/**
 * Тестовый скрипт для проверки счетчика заявок на доступ
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== Проверка счетчика заявок на доступ ===\n\n";

try {
    // Создаем экземпляр DatabaseService
    $databaseService = new DatabaseService();
    
    echo "1. Подключение к базе данных: ✅\n\n";
    
    // Получаем pending заявки
    $pendingRequests = $databaseService->getPendingAccessRequests();
    $count = count($pendingRequests);
    
    echo "2. Количество pending заявок: {$count}\n\n";
    
    if ($count > 0) {
        echo "3. Список заявок:\n";
        echo str_repeat('-', 60) . "\n";
        
        foreach ($pendingRequests as $index => $request) {
            $num = $index + 1;
            $userId = $request['user_id'] ?? 'N/A';
            $userName = $request['user_name'] ?? 'N/A';
            $timestamp = $request['timestamp'] ?? 0;
            $date = $timestamp ? date('d.m.Y H:i:s', $timestamp) : 'N/A';
            
            echo "  #{$num}:\n";
            echo "    User ID: {$userId}\n";
            echo "    User Name: {$userName}\n";
            echo "    Date: {$date}\n";
            echo "\n";
        }
    } else {
        echo "3. Нет pending заявок\n";
    }
    
    echo str_repeat('=', 60) . "\n";
    echo "РЕЗУЛЬТАТ:\n";
    echo "Кнопка будет отображаться как: \"👥 Заявки на доступ";
    if ($count > 0) {
        echo " ({$count})";
    }
    echo "\"\n";
    echo str_repeat('=', 60) . "\n";
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
