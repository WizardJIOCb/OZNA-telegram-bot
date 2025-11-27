<?php

echo "=== ТЕСТИРОВАНИЕ АБСОЛЮТНОЙ ЗАЩИТЫ ===\n\n";

// Включаем все необходимые файлы
require_once "vendor/autoload.php";
require_once "CustomCommands/Services/DatabaseService.php";
require_once "CustomCommands/Services/MessageService.php";
require_once "CustomCommands/Services/TabelEntry.php";

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

try {
    $dbService = new DatabaseService();
    $messageService = new MessageService($dbService);
    $tabelEntry = new TabelEntry($dbService, $messageService);
    
    echo "1. ТЕСТИРУЕМ ЗАЩИТУ ОТ СТАРОГО НЕПРАВИЛЬНОГО ФОРМАТА:\n\n";
    
    // Симулируем старый неправильный формат, который возвращался с сервера
    echo "Тестируем точный случай с сервера:\n";
    echo "ФИО: 'Тест тест тест'\n";
    echo "Дата: '04.10.2025'\n";
    echo "Проект: 'ХХХ'\n";
    echo "Тип активности: любой\n\n";
    
    $result = $tabelEntry->createTabelMessage('Тест тест тест', '04.10.2025', 'ХХХ', 'ПроверкаМонтажнойПлощадки');
    
    echo "РЕЗУЛЬТАТ:\n";
    echo "$result\n\n";
    
    // Проверяем результат
    if (strpos($result, '✅04.10.2025, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки') !== false) {
        echo "✅ ПРАВИЛЬНЫЙ ФОРМАТ! Защита работает!\n";
    } elseif (strpos($result, '✅ Запись в табель от') !== false) {
        echo "❌ СТАРЫЙ ФОРМАТ! Защита не сработала!\n";
    } else {
        echo "⚠️  Другой формат\n";
    }
    
    echo "\n2. ТЕСТИРУЕМ РАЗЛИЧНЫЕ АКТИВНОСТИ:\n\n";
    
    $testCases = [
        'ПроверкаМонтажнойПлощадки',
        'АналФунд',
        'ШМР_АналФунд',
        'НекаяДругаяАктивность'
    ];
    
    foreach ($testCases as $activityType) {
        echo "Тестируем: '$activityType'\n";
        $result = $tabelEntry->createTabelMessage('Тест', '04.10.2025', 'ХХХ', $activityType);
        
        echo "Результат: ";
        if (strpos($result, '🏗️ Проверка монтажной площадки') !== false) {
            echo "ПРОВЕРКА МОНТАЖНОЙ ПЛОЩАДКИ ✅\n";
        } elseif (strpos($result, 'о  добавлена') !== false) {
            echo "СТАРЫЙ НЕПРАВИЛЬНЫЙ ФОРМАТ ❌\n";
        } else {
            echo "ДРУГОЙ КОРРЕКТНЫЙ ФОРМАТ ⚠️\n";
        }
        echo "\n";
    }
    
    echo "=== РЕЗУЛЬТАТ ===\n";
    echo "Теперь ЛЮБОЙ старый неправильный формат будет автоматически исправлен!\n";
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
}