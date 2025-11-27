<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

echo "=== ТЕСТ НОВОЙ ЛОГИКИ ===\n\n";
echo "Проверяем классификацию активностей:\n";
echo str_repeat("-", 70) . "\n";

$reflection = new ReflectionClass($personnelService);
$isWorkMethod = $reflection->getMethod('isWorkActivity');
$isWorkMethod->setAccessible(true);

$testActivities = [
    '🚗 Дорога' => 'ОЖИДАЕТСЯ: РАБОЧАЯ ✅',
    '📋 Инструктаж' => 'ОЖИДАЕТСЯ: РАБОЧАЯ ✅',
    '💰 Сдача авансового отчета' => 'ОЖИДАЕТСЯ: РАБОЧАЯ ✅',
    '🏢 Участие в ЗПИ' => 'ОЖИДАЕТСЯ: НЕРАБОЧАЯ ❌',
    '🏢 Участие в ОПИ' => 'ОЖИДАЕТСЯ: НЕРАБОЧАЯ ❌',
    '📊 Текущая деятельность' => 'ОЖИДАЕТСЯ: НЕРАБОЧАЯ ❌',
    '📝 Разработка программы работ' => 'ОЖИДАЕТСЯ: НЕРАБОЧАЯ ❌',
    '📅 Разработка графика работ' => 'ОЖИДАЕТСЯ: НЕРАБОЧАЯ ❌',
    '⏸️ Простои' => 'ОЖИДАЕТСЯ: РАБОЧАЯ ✅',
    '🔧 Слесарные работы' => 'ОЖИДАЕТСЯ: РАБОЧАЯ ✅',
    '📝 Прочее' => 'ОЖИДАЕТСЯ: РАБОЧАЯ ✅',
];

$allCorrect = true;

foreach ($testActivities as $activity => $expected) {
    $message = "<b>Тестовый пользователь</b>\n✅01.01.2025, Проект <b>TEST</b>: Добавлена запись в табель: <b>$activity</b>";
    $isWork = $isWorkMethod->invoke($personnelService, $message);
    
    $result = $isWork ? 'РАБОЧАЯ ✅' : 'НЕРАБОЧАЯ ❌';
    $status = ($isWork && strpos($expected, 'РАБОЧАЯ') !== false) || (!$isWork && strpos($expected, 'НЕРАБОЧАЯ') !== false);
    
    if ($status) {
        echo "✅ $activity → $result\n";
    } else {
        echo "❌ $activity → $result (Ожидалось: $expected)\n";
        $allCorrect = false;
    }
}

echo "\n" . str_repeat("-", 70) . "\n";

if ($allCorrect) {
    echo "✅ ВСЕ ТЕСТЫ ПРОЙДЕНЫ!\n\n";
    
    echo "📋 ИТОГОВАЯ ЛОГИКА:\n";
    echo str_repeat("=", 70) . "\n";
    echo "РАБОЧИЕ АКТИВНОСТИ (начинают/продолжают вахту):\n";
    echo "  • Дорога - выезд на объект\n";
    echo "  • Инструктаж - начало работ\n";
    echo "  • Сдача авансового отчета - завершение вахты\n";
    echo "  • Все производственные активности (слесарные, монтаж, наладка и т.д.)\n";
    echo "  • Простои (считаются как присутствие на объекте)\n\n";
    
    echo "НЕРАБОЧИЕ АКТИВНОСТИ (офисная работа, не начинают вахту):\n";
    echo "  • Текущая деятельность\n";
    echo "  • Участие в ЗПИ/ОПИ\n";
    echo "  • Разработка программы/графика работ\n\n";
    
    echo "ПРАВИЛО ВАХТЫ:\n";
    echo "  • НА ОБЪЕКТЕ: последняя рабочая активность ≤3 дней назад\n";
    echo "  • НА СВОБОДНОЙ ВАХТЕ: последняя рабочая активность >3 дней назад\n";
    echo "  • Свободная вахта начинается: следующий день после последней работы\n";
    echo "  • Нерабочие активности НЕ прерывают свободную вахту\n";
} else {
    echo "❌ ЕСТЬ ОШИБКИ В КЛАССИФИКАЦИИ!\n";
}
