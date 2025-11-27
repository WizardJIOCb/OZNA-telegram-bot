<?php

echo "=== ПРОВЕРКА ИСПРАВЛЕНИЯ ПРОВЕРКИ МОНТАЖНОЙ ПЛОЩАДКИ ===\n";
echo "Время: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Проверяем файл TabelEntry.php
echo "1. ПРОВЕРКА ФАЙЛА TabelEntry.php:\n";
$tabelEntryFile = 'CustomCommands/Services/TabelEntry.php';
if (file_exists($tabelEntryFile)) {
    $content = file_get_contents($tabelEntryFile);
    
    // Проверяем наличие специального случая для проверки монтажной площадки
    if (strpos($content, 'ПроверкаМонтажнойПлощадки') !== false) {
        echo "   ✅ Найден код для ПроверкаМонтажнойПлощадки\n";
        
        // Ищем специальную проверку
        if (strpos($content, 'CRITICAL FIX: Special check for site inspection') !== false) {
            echo "   ✅ Найдена специальная проверка для проверки монтажной площадки\n";
        } else {
            echo "   ❌ НЕ найдена специальная проверка\n";
        }
        
        // Проверяем порядок проверок
        $siteInspectionPos = strpos($content, 'ПроверкаМонтажнойПлощадки');
        $commissioningPos = strpos($content, 'in_array($activityType, $this->commissioningActivities)');
        
        if ($siteInspectionPos !== false && $commissioningPos !== false) {
            if ($siteInspectionPos < $commissioningPos) {
                echo "   ✅ Специальная проверка идет ПЕРЕД общей проверкой пусконаладочных работ\n";
            } else {
                echo "   ❌ Специальная проверка идет ПОСЛЕ общей проверки (проблема!)\n";
            }
        }
    } else {
        echo "   ❌ НЕ найден код для ПроверкаМонтажнойПлощадки\n";
    }
} else {
    echo "   ❌ Файл TabelEntry.php не найден\n";
}

echo "\n2. ПРОВЕРКА ФАЙЛА TabelHandler.php:\n";
$tabelHandlerFile = 'CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerFile)) {
    $content = file_get_contents($tabelHandlerFile);
    
    // Проверяем исправление кодировки
    if (strpos($content, 'cat_РЁРњР _РџРѕРґРіР Р°Р±') !== false) {
        echo "   ✅ Найдено исправление кодировки\n";
    } else {
        echo "   ❌ НЕ найдено исправление кодировки\n";
    }
    
    // Проверяем маппинг подтверждения
    if (strpos($content, 'cat_ШМР_АналФунд_yes') !== false) {
        echo "   ✅ Найден маппинг подтверждения\n";
    } else {
        echo "   ❌ НЕ найден маппинг подтверждения\n";
    }
} else {
    echo "   ❌ Файл TabelHandler.php не найден\n";
}

echo "\n3. СОЗДАНИЕ ТЕСТОВОГО СКРИПТА ДЛЯ ОТЛАДКИ:\n";
echo "   Создаю скрипт для тестирования в реальном времени...\n";

// Создаем скрипт для тестирования
$testScript = '<?php

// Включаем все необходимые файлы
require_once "vendor/autoload.php";
require_once "CustomCommands/Services/DatabaseService.php";
require_once "CustomCommands/Services/TabelEntry.php";

use Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\DatabaseService;
use Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\TabelEntry;

echo "=== ТЕСТИРОВАНИЕ ПРОВЕРКИ МОНТАЖНОЙ ПЛОЩАДКИ ===\\n";

try {
    $dbService = new DatabaseService();
    $tabelEntry = new TabelEntry($dbService);
    
    // Тестируем создание сообщения для проверки монтажной площадки
    $fio = "Тестовый Пользователь";
    $date = "05.10.2025";
    $projectCode = "ХХХ";
    $activityType = "ПроверкаМонтажнойПлощадки";
    
    echo "Параметры теста:\\n";
    echo "- ФИО: $fio\\n";
    echo "- Дата: $date\\n";
    echo "- Проект: $projectCode\\n";
    echo "- Тип активности: $activityType\\n\\n";
    
    $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
    
    echo "РЕЗУЛЬТАТ:\\n";
    echo "$message\\n\\n";
    
    // Проверяем правильность формата
    if (strpos($message, "✅$date, Проект") !== false && strpos($message, "🏗️ Проверка монтажной площадки") !== false) {
        echo "✅ ФОРМАТ ПРАВИЛЬНЫЙ!\\n";
    } else {
        echo "❌ ФОРМАТ НЕПРАВИЛЬНЫЙ!\\n";
        echo "Ожидаемый формат: ✅05.10.2025, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки\\n";
    }
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\\n";
}
';

file_put_contents('test_site_inspection_runtime.php', $testScript);
echo "   ✅ Создан файл test_site_inspection_runtime.php\n";

echo "\n4. ЗАПУСК ТЕСТА:\n";
echo "   Выполняю тест...\n\n";