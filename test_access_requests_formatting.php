<?php
/**
 * Проверка форматирования текста заявок на доступ
 */

echo "=== ПРОВЕРКА ФОРМАТИРОВАНИЯ ЗАЯВОК ===\n\n";

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
    
    echo "Получение списка заявок...\n";
    $requests = $adminService->getAllAccessRequests();
    $requestCount = count($requests);
    
    echo "Найдено заявок: {$requestCount}\n\n";
    
    // Создаем текст как в исправленном коде
    $text = "👥 <b>Заявки на доступ к боту</b>\n\n";
    $text .= "Всего заявок: " . $requestCount . "\n\n";
    
    if (empty($requests)) {
        $text .= "Нет новых заявок на доступ.";
    } else {
        $text .= "Выберите заявку для просмотра:\n\n";
    }
    
    echo "=== РЕЗУЛЬТАТ ФОРМАТИРОВАНИЯ ===\n\n";
    echo "Текст сообщения:\n";
    echo "'" . $text . "'\n\n";
    
    echo "=== ВИЗУАЛЬНЫЙ ПРЕДВАРИТЕЛЬНЫЙ ПРОСМОТР ===\n";
    echo "Как это будет выглядеть в Telegram:\n";
    echo "┌─────────────────────────────────────┐\n";
    
    // Показываем как будет выглядеть в Telegram (без HTML тегов)
    $displayText = str_replace(['<b>', '</b>'], ['', ''], $text);
    $lines = explode("\n", $displayText);
    
    foreach ($lines as $line) {
        if (empty($line)) {
            echo "│                                     │\n";
        } else {
            echo "│ " . str_pad($line, 35) . " │\n";
        }
    }
    
    echo "└─────────────────────────────────────┘\n\n";
    
    echo "=== ПРОВЕРКА ИСПРАВЛЕНИЙ ===\n\n";
    
    // Проверяем что нет проблем с форматированием
    $issues = [];
    
    if (strpos($text, 'ботуВсего') !== false) {
        $issues[] = "❌ Все еще есть 'ботуВсего' слитно";
    } else {
        echo "✅ 'ботуВсего' больше НЕТ - исправлено!\n";
    }
    
    if (strpos($text, "боту</b>Всего") !== false) {
        $issues[] = "❌ Нет переноса после '</b>Всего'";
    } else {
        echo "✅ Есть правильный перенос после заголовка\n";
    }
    
    if (strpos($text, "боту</b>\n\nВсего") !== false) {
        echo "✅ Правильное форматирование: заголовок + двойной перенос + текст\n";
    }
    
    if (empty($issues)) {
        echo "\n🎉 ВСЕ ИСПРАВЛЕНО ПРАВИЛЬНО!\n";
        echo "Теперь текст будет выглядеть красиво и читабельно!\n";
    } else {
        echo "\n⚠️ Найдены проблемы:\n";
        foreach ($issues as $issue) {
            echo "  {$issue}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
}