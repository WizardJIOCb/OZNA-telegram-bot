<?php

echo "=== ТЕСТ ИСПРАВЛЕНИЯ ЭМОДЖИ ДЛЯ ШЕФ-НАЛАДОЧНЫХ РАБОТ ===\n\n";

// Подключим класс для тестирования
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

// Создаем mock объекты для зависимостей
class MockDatabaseService {
    public function hasRecentTabelEntry($userId, $isSuperAdmin = false) {
        return null; // No recent entries
    }
}

class MockMessageService {
    // Mock implementation
}

// Создаем экземпляр TabelEntry
$mockDatabase = new MockDatabaseService();
$mockMessage = new MockMessageService();
$tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($mockDatabase, $mockMessage);

echo "🔍 ТЕСТИРОВАНИЕ СООБЩЕНИЯ ОТЧЕТА:\n";
echo "=================================\n\n";

$fio = "Иванов И.И.";
$date = "12:30 15.03.2024";
$projectCode = "ОИ834";
$activityType = "ШефНаладочныеРаботы";

echo "Параметры теста:\n";
echo "- ФИО: $fio\n";
echo "- Дата: $date\n";
echo "- Проект: $projectCode\n";
echo "- Тип активности: $activityType\n\n";

try {
    $reportMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
    
    echo "РЕЗУЛЬТАТ ОТЧЕТА:\n";
    echo "-----------------\n";
    echo "\"$reportMessage\"\n\n";
    
    // Проверяем наличие эмоджи
    if (strpos($reportMessage, '🔧') !== false) {
        echo "✅ ЭМОДЖИ НАЙДЕН: 🔧\n";
    } else {
        echo "❌ ЭМОДЖИ НЕ НАЙДЕН\n";
    }
    
    // Проверяем наличие полного названия
    if (strpos($reportMessage, 'Шеф-наладочные работы') !== false) {
        echo "✅ ПОЛНОЕ НАЗВАНИЕ НАЙДЕНО: 'Шеф-наладочные работы'\n";
    } else {
        echo "❌ ПОЛНОЕ НАЗВАНИЕ НЕ НАЙДЕНО\n";
    }
    
    // Проверяем отсутствие внутреннего кода
    if (strpos($reportMessage, 'ШефНаладочныеРаботы') === false) {
        echo "✅ ВНУТРЕННИЙ КОД НЕ НАЙДЕН (это хорошо)\n";
    } else {
        echo "❌ ВНУТРЕННИЙ КОД НАЙДЕН: 'ШефНаладочныеРаботы'\n";
    }
    
    echo "\n🔄 СРАВНЕНИЕ С ЭТАЛОНОМ:\n";
    echo "========================\n\n";
    
    // Тестируем УчастиеВОПИ для сравнения
    $referenceMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, "УчастиеВОПИ");
    
    echo "ЭТАЛОН (УчастиеВОПИ):\n";
    echo "\"$referenceMessage\"\n\n";
    
    echo "ИСПРАВЛЕННОЕ (ШефНаладочныеРаботы):\n";
    echo "\"$reportMessage\"\n\n";
    
    // Проверяем структурное сходство
    $referencePattern = preg_replace('/<b>[^<]+<\/b>/', '<b>%s</b>', $referenceMessage);
    $testPattern = preg_replace('/<b>[^<]+<\/b>/', '<b>%s</b>', $reportMessage);
    
    if ($referencePattern === $testPattern) {
        echo "✅ СТРУКТУРА СООБЩЕНИЙ ИДЕНТИЧНА\n";
    } else {
        echo "❌ СТРУКТУРА СООБЩЕНИЙ РАЗЛИЧАЕТСЯ\n";
        echo "   Эталон: $referencePattern\n";
        echo "   Тест: $testPattern\n";
    }
    
} catch (Exception $e) {
    echo "❌ ОШИБКА ПРИ ТЕСТИРОВАНИИ: " . $e->getMessage() . "\n";
}

echo "\n🎯 ЗАКЛЮЧЕНИЕ:\n";
echo "==============\n";
echo "Если все проверки показывают ✅, то проблема исправлена:\n";
echo "1. Эмоджи добавлен в отчет\n";
echo "2. Используется полное описательное название\n";
echo "3. Внутренний код не отображается пользователю\n";
echo "4. Формат соответствует эталону\n";

?>