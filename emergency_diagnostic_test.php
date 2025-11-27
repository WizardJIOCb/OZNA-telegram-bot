<?php

echo "=== ЭКСТРЕННАЯ ДИАГНОСТИКА ПРОВЕРКИ МОНТАЖНОЙ ПЛОЩАДКИ ===\n\n";

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
    
    echo "1. ПРОВЕРЯЕМ ВЕРСИЮ ФАЙЛА TabelEntry.php:\n";
    
    // Проверяем, есть ли наше исправление в файле
    $fileContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');
    
    if (strpos($fileContent, '🚨🚨🚨 EMERGENCY OVERRIDE') !== false) {
        echo "   ✅ EMERGENCY OVERRIDE найден в файле!\n";
    } else {
        echo "   ❌ EMERGENCY OVERRIDE НЕ НАЙДЕН! Исправление не применено!\n";
    }
    
    if (strpos($fileContent, '🔥🔥🔥 ULTIMATE SITE INSPECTION FIX') !== false) {
        echo "   ✅ ULTIMATE SITE INSPECTION FIX найден в файле!\n";
    } else {
        echo "   ❌ ULTIMATE SITE INSPECTION FIX НЕ НАЙДЕН!\n";
    }
    
    if (strpos($fileContent, '🔥🔥🔥 FINAL FAILSAFE') !== false) {
        echo "   ✅ FINAL FAILSAFE найден в файле!\n";
    } else {
        echo "   ❌ FINAL FAILSAFE НЕ НАЙДЕН!\n";
    }
    
    echo "\n2. ТЕСТИРУЕМ ТОЧНЫЙ СЦЕНАРИЙ С СЕРВЕРА:\n";
    
    // Тестируем с точными данными как на сервере
    $testData = [
        'fio' => 'Тест тест тест',
        'date' => '04.10.2025',
        'projectCode' => 'ХХХ',
        'activityType' => 'ПроверкаМонтажнойПлощадки'
    ];
    
    echo "   Входные данные:\n";
    echo "   - ФИО: '{$testData['fio']}'\n";
    echo "   - Дата: '{$testData['date']}'\n";
    echo "   - Проект: '{$testData['projectCode']}'\n";
    echo "   - Тип активности: '{$testData['activityType']}'\n\n";
    
    $result = $tabelEntry->createTabelMessage(
        $testData['fio'],
        $testData['date'],
        $testData['projectCode'],
        $testData['activityType']
    );
    
    echo "   РЕЗУЛЬТАТ:\n";
    echo "   {$result}\n\n";
    
    // Проверяем формат
    if (strpos($result, '✅04.10.2025, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки') !== false) {
        echo "   ✅ ПРАВИЛЬНЫЙ ФОРМАТ!\n";
    } elseif (strpos($result, '✅ Запись в табель от') !== false) {
        echo "   ❌ СТАРЫЙ НЕПРАВИЛЬНЫЙ ФОРМАТ! Исправление НЕ РАБОТАЕТ!\n";
    } else {
        echo "   ⚠️  НЕИЗВЕСТНЫЙ ФОРМАТ\n";
    }
    
    echo "\n3. ТЕСТИРУЕМ РАЗЛИЧНЫЕ ВОЗМОЖНЫЕ ТИПЫ АКТИВНОСТИ:\n";
    
    $possibleActivityTypes = [
        'ПроверкаМонтажнойПлощадки',
        'АналФунд', 
        'ШМР_АналФунд',
        'АнализИнформацииПоФундаменту',
        'о добавлена' // Из старого формата
    ];
    
    foreach ($possibleActivityTypes as $activityType) {
        echo "   Тестируем: '$activityType' -> ";
        
        $result = $tabelEntry->createTabelMessage('Тест', '04.10.2025', 'ХХХ', $activityType);
        
        if (strpos($result, '🏗️ Проверка монтажной площадки') !== false) {
            echo "ПРАВИЛЬНЫЙ ✅\n";
        } else {
            echo "НЕПРАВИЛЬНЫЙ ❌\n";
        }
    }
    
    echo "\n4. ПРОВЕРЯЕМ СИСТЕМНУЮ ИНФОРМАЦИЮ:\n";
    echo "   PHP версия: " . PHP_VERSION . "\n";
    echo "   Текущая директория: " . __DIR__ . "\n";
    echo "   Файл TabelEntry.php существует: " . (file_exists(__DIR__ . '/CustomCommands/Services/TabelEntry.php') ? 'ДА' : 'НЕТ') . "\n";
    
    if (file_exists(__DIR__ . '/CustomCommands/Services/TabelEntry.php')) {
        $fileModTime = filemtime(__DIR__ . '/CustomCommands/Services/TabelEntry.php');
        echo "   Последнее изменение файла: " . date('Y-m-d H:i:s', $fileModTime) . "\n";
    }
    
    echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}