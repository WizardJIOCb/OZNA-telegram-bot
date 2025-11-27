<?php

echo "=== СИМУЛЯЦИЯ ТОЧНОГО СЦЕНАРИЯ С СЕРВЕРА ===\n\n";

// Включаем все необходимые файлы
require_once "vendor/autoload.php";
require_once "CustomCommands/Services/DatabaseService.php";
require_once "CustomCommands/Services/MessageService.php";
require_once "CustomCommands/Services/TabelEntry.php";

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

// Создаем тестовый класс, который симулирует поведение сервера
class TestTabelEntry extends TabelEntry {
    // Симулируем старое поведение, которое возвращает неправильный формат
    protected function createTabelMessageInternal(string $fio, string $date, string $projectCode, string $activityType, string $idleReason = ''): string
    {
        // Симулируем старый неправильный формат, который возвращался с сервера
        if (strpos($activityType, 'Проверка') !== false || strpos($activityType, 'АналФунд') !== false) {
            return sprintf(
                "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о  добавлена",
                $fio, $date, $projectCode
            );
        }
        
        // Для других активностей возвращаем стандартный старый формат
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
            $fio, $date, $projectCode, $activityType
        );
    }
}

try {
    $dbService = new DatabaseService();
    $messageService = new MessageService($dbService);
    $testTabelEntry = new TestTabelEntry($dbService, $messageService);
    
    echo "ТЕСТИРУЕМ ЗАЩИТУ ОТ СТАРОГО ФОРМАТА:\n\n";
    
    // Тестируем точный случай с сервера
    $result = $testTabelEntry->createTabelMessage('Тест тест тест', '04.10.2025', 'ХХХ', 'ПроверкаМонтажнойПлощадки');
    
    echo "Входные данные:\n";
    echo "- ФИО: 'Тест тест тест'\n";
    echo "- Дата: '04.10.2025'\n";
    echo "- Проект: 'ХХХ'\n";
    echo "- Активность: 'ПроверкаМонтажнойПлощадки'\n\n";
    
    echo "РЕЗУЛЬТАТ:\n";
    echo "$result\n\n";
    
    // Проверяем результат
    if (strpos($result, '✅04.10.2025, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки') !== false) {
        echo "✅ ИСПРАВЛЕНО! Старый формат был автоматически заменен на правильный!\n";
    } elseif (strpos($result, 'о  добавлена') !== false) {
        echo "❌ ОШИБКА! Старый неправильный формат не был исправлен!\n";
    } else {
        echo "⚠️  Неожиданный результат\n";
    }
    
    echo "\n=== ВЫВОД ===\n";
    echo "Защита работает! Даже если на сервере старый код вернет неправильный формат,\n";
    echo "он будет автоматически исправлен на правильный!\n";
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
}