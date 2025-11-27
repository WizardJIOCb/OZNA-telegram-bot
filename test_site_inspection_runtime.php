<?php

// Включаем все необходимые файлы
require_once "vendor/autoload.php";
require_once "CustomCommands/Services/DatabaseService.php";
require_once "CustomCommands/Services/MessageService.php";
require_once "CustomCommands/Services/TabelEntry.php";

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "=== ТЕСТИРОВАНИЕ ПРОВЕРКИ МОНТАЖНОЙ ПЛОЩАДКИ ===\n";

try {
    $dbService = new DatabaseService();
    $messageService = new MessageService($dbService);
    $tabelEntry = new TabelEntry($dbService, $messageService);
    
    // Тестируем создание сообщения для проверки монтажной площадки
    $fio = "Тестовый Пользователь";
    $date = "05.10.2025";
    $projectCode = "ХХХ";
    $activityType = "ПроверкаМонтажнойПлощадки";
    
    echo "Параметры теста:\n";
    echo "- ФИО: $fio\n";
    echo "- Дата: $date\n";
    echo "- Проект: $projectCode\n";
    echo "- Тип активности: $activityType\n\n";
    
    $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
    
    echo "РЕЗУЛЬТАТ:\n";
    echo "$message\n\n";
    
    // Проверяем правильность формата
    if (strpos($message, "✅$date, Проект") !== false && strpos($message, "🏗️ Проверка монтажной площадки") !== false) {
        echo "✅ ФОРМАТ ПРАВИЛЬНЫЙ!\n";
    } else {
        echo "❌ ФОРМАТ НЕПРАВИЛЬНЫЙ!\n";
        echo "Ожидаемый формат: ✅05.10.2025, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки\n";
    }
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
}
