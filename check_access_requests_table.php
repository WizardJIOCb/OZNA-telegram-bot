<?php
/**
 * Проверка таблицы access_requests в базе данных
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== Проверка таблицы access_requests ===\n\n";

try {
    $databaseService = new DatabaseService();
    
    // Используем публичный метод getPendingAccessRequests
    $pendingRequests = $databaseService->getPendingAccessRequests();
    $pendingCount = count($pendingRequests);
    
    echo "1. Количество PENDING заявок: {$pendingCount}\n\n";
    
    if ($pendingCount > 0) {
        echo "2. Список PENDING заявок:\n";
        echo str_repeat('-', 80) . "\n";
        
        foreach ($pendingRequests as $index => $request) {
            $num = $index + 1;
            $date = date('d.m.Y H:i:s', $request['timestamp']);
            
            echo "  #{$num}:\n";
            echo "    ID: {$request['id']}\n";
            echo "    User ID: {$request['user_id']}\n";
            echo "    User Name: {$request['user_name']}\n";
            echo "    Status: {$request['status']}\n";
            echo "    Date: {$date}\n";
            echo "\n";
        }
    } else {
        echo "2. Нет PENDING заявок\n";
        echo "   (Все заявки либо обработаны, либо их нет)\n";
    }
    
    echo "\n";
    echo str_repeat('=', 80) . "\n";
    echo "РЕЗУЛЬТАТ:\n";
    echo "Кнопка в админке будет отображаться как: \"👥 Заявки на доступ";
    if ($pendingCount > 0) {
        echo " ({$pendingCount})";
    }
    echo "\"\n";
    echo str_repeat('=', 80) . "\n";
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
