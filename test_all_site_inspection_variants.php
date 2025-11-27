<?php

echo "=== ТЕСТИРОВАНИЕ ВСЕХ ВОЗМОЖНЫХ ВАРИАНТОВ ПРОВЕРКИ МОНТАЖНОЙ ПЛОЩАДКИ ===\n\n";

// Включаем необходимые файлы
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
    
    // Тестируем все возможные варианты активности
    $testCases = [
        // Правильный тип
        'ПроверкаМонтажнойПлощадки',
        // Возможные искаженные варианты
        'АналФунд',
        'ШМР_АналФунд',
        'АнализИнформацииПоФундаменту',
        'ШМР_АнализИнформацииПоФундаменту',
        'АнализИнформацииПоФундаменту_ПодтВех',
        'ШМР_АнализИнформацииПоФундаменту_ПодтВех',
        'АнализИнформацииПоФундаменту_РабПоГр',
        'ШМР_АнализИнформацииПоФундаменту_РабПоГр',
        // Возможные проблемные варианты
        'Анализ фундамента',
        'анализ',
        'фундамент',
        'площадка',
        'монтаж',
        'site_inspection',
        // Последний вариант - что-то совершенно неизвестное
        'НеизвестнаяАктивность'
    ];
    
    $fio = "Тестовый Пользователь";
    $date = "04.10.2025";
    $projectCode = "ХХХ";
    
    foreach ($testCases as $activityType) {
        echo "Тестируем: '$activityType'\n";
        
        $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
        
        // Проверяем формат
        if (strpos($message, "✅04.10.2025, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки") !== false) {
            echo "   ✅ ПРАВИЛЬНЫЙ ФОРМАТ (Проверка монтажной площадки)\n";
        } elseif (strpos($message, "✅04.10.2025, Проект") !== false && strpos($message, "Добавлена запись в табель:") !== false) {
            echo "   ⚠️  ПРАВИЛЬНЫЙ ФОРМАТ (другой тип активности)\n";
        } elseif (strpos($message, "✅ Запись в табель от") !== false) {
            echo "   ❌ СТАРЫЙ НЕПРАВИЛЬНЫЙ ФОРМАТ!\n";
        } else {
            echo "   ⚠️  НЕИЗВЕСТНЫЙ ФОРМАТ\n";
        }
        
        // Показываем первые 80 символов результата
        echo "   Результат: " . substr($message, 0, 80) . "...\n\n";
    }
    
    echo "=== ЗАКЛЮЧЕНИЕ ===\n";
    echo "Исправление должно ловить все варианты, содержащие ключевые слова:\n";
    echo "- 'АналФунд', 'Фунд', 'анал', 'площадк', 'монтаж'\n";
    echo "- 'site', 'inspection'\n";
    echo "- 'о добавлена' (из старого неправильного формата)\n\n";
    
    echo "Любая активность с этими словами будет использовать правильный формат!\n";

} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
}