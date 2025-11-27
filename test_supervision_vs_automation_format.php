<?php
/**
 * ТЕСТ: Сравнение форматов отчетов Шеф-монтажных работ и Автоматизации
 */

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

echo "🔍 ТЕСТ: СРАВНЕНИЕ ФОРМАТОВ ОТЧЕТОВ ШМР И АВТОМАТИЗАЦИИ\n";
echo "=======================================================\n\n";

// Создаем экземпляры сервисов для тестирования
try {
    $databaseService = new \CustomCommands\Services\DatabaseService();
    $messageService = new \CustomCommands\Services\MessageService();
    $tabelEntry = new \CustomCommands\Services\TabelEntry($databaseService, $messageService);

    echo "✅ Сервисы успешно созданы\n\n";

    // Тестовые данные
    $testData = [
        'fio' => 'Иванов Иван Иванович',
        'date' => '27.09.2025',
        'projectCode' => 'ОИ837',
        'idleReason' => ''
    ];

    echo "📋 ТЕСТОВЫЕ ДАННЫЕ:\n";
    echo "==================\n";
    echo "ФИО: {$testData['fio']}\n";
    echo "Дата: {$testData['date']}\n";
    echo "Проект: {$testData['projectCode']}\n\n";

    echo "🧪 ТЕСТ 1: ОТЧЕТ ДЛЯ АВТОМАТИЗАЦИИ (ЭТАЛОН)\n";
    echo "============================================\n";
    $automationReport = $tabelEntry->createTabelMessage(
        $testData['fio'],
        $testData['date'],
        $testData['projectCode'],
        'РабПоГр_Автом',
        $testData['idleReason']
    );
    echo "Активность: РабПоГр_Автом (Работы по графику - Автоматизация)\n";
    echo "Отчет:\n{$automationReport}\n\n";

    echo "🧪 ТЕСТ 2: ОТЧЕТ ДЛЯ ШЕФ-МОНТАЖНЫХ РАБОТ\n";
    echo "=========================================\n";
    $supervisionReport = $tabelEntry->createTabelMessage(
        $testData['fio'],
        $testData['date'],
        $testData['projectCode'],
        'ШМ_РабПоГрафику',
        $testData['idleReason']
    );
    echo "Активность: ШМ_РабПоГрафику (Работы по графику - Шеф-монтаж)\n";
    echo "Отчет:\n{$supervisionReport}\n\n";

    echo "📊 СРАВНЕНИЕ ФОРМАТОВ:\n";
    echo "======================\n";
    
    // Анализируем формат отчетов
    if ($automationReport === $supervisionReport) {
        echo "✅ ФОРМАТЫ ИДЕНТИЧНЫ!\n";
        echo "Оба отчета используют одинаковую структуру.\n\n";
    } else {
        echo "❌ ФОРМАТЫ ОТЛИЧАЮТСЯ!\n\n";
        
        echo "🔍 ДЕТАЛЬНОЕ СРАВНЕНИЕ:\n";
        echo "-----------------------\n";
        echo "Автоматизация:\n'{$automationReport}'\n\n";
        echo "Шеф-монтаж:\n'{$supervisionReport}'\n\n";
        
        // Проверяем длину
        echo "Длина отчета Автоматизации: " . strlen($automationReport) . " символов\n";
        echo "Длина отчета Шеф-монтажа: " . strlen($supervisionReport) . " символов\n\n";
        
        // Проверяем наличие ключевых элементов
        $hasProject = strpos($supervisionReport, 'Проект') !== false;
        $hasCorrectFormat = strpos($supervisionReport, 'Добавлена запись в табель:') !== false;
        
        echo "Отчет ШМР содержит 'Проект': " . ($hasProject ? "✅ ДА" : "❌ НЕТ") . "\n";
        echo "Отчет ШМР содержит правильный формат: " . ($hasCorrectFormat ? "✅ ДА" : "❌ НЕТ") . "\n\n";
    }

    echo "🧪 ТЕСТ 3: ДРУГИЕ АКТИВНОСТИ ШМР\n";
    echo "=================================\n";
    
    $supervisionActivities = [
        'ШМ_ПодготовкаКПоездке' => 'Подготовка к служебной поездке',
        'ШМ_Дорога' => 'Дорога',
        'ШМ_Инструктаж' => 'Инструктаж',
        'ШМ_СдачаАвансовогоОтчета' => 'Сдача авансового отчета'
    ];

    foreach ($supervisionActivities as $activityType => $description) {
        echo "Тестируем: {$activityType} ({$description})\n";
        $report = $tabelEntry->createTabelMessage(
            $testData['fio'],
            $testData['date'],
            $testData['projectCode'],
            $activityType,
            $testData['idleReason']
        );
        
        $hasCorrectFormat = strpos($report, 'Проект <b>' . $testData['projectCode'] . '</b>: Добавлена запись в табель:') !== false;
        echo "Формат корректен: " . ($hasCorrectFormat ? "✅ ДА" : "❌ НЕТ") . "\n";
        if (!$hasCorrectFormat) {
            echo "Отчет: {$report}\n";
        }
        echo "\n";
    }

    echo "💡 ЗАКЛЮЧЕНИЕ:\n";
    echo "==============\n";
    echo "Если все тесты показывают ✅, то логика форматирования работает правильно.\n";
    echo "Проблема может быть в:\n";
    echo "1. Неправильном типе активности, передаваемом в createTabelMessage()\n";
    echo "2. Логике обработки коллбэков в TabelHandler\n";
    echo "3. Маппинге коротких коллбэков в полные типы активностей\n\n";

} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Детали: " . $e->getTraceAsString() . "\n";
}

?>