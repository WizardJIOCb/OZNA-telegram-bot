<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

echo "=== ТЕСТ: ПОЛУЧЕНИЕ ШИФРОВ ПРОЕКТОВ ===\n\n";

// Получаем сотрудников на объекте из отдела ШМР
$employees = $personnelService->getOnsiteEmployees('Шеф-монтаж');

echo "Найдено сотрудников на объекте: " . count($employees) . "\n\n";

if (empty($employees)) {
    echo "❌ Нет сотрудников на объекте\n";
    exit;
}

// Показываем первых 5 для примера
$count = min(5, count($employees));

for ($i = 0; $i < $count; $i++) {
    $emp = $employees[$i];
    $daysWord = $personnelService->getDaysWord($emp['days']);
    
    echo ($i + 1) . ". {$emp['icon']} {$emp['fio']}\n";
    echo "   На объекте: {$emp['days']} {$daysWord}\n";
    echo "   Дата начала: {$emp['start_date']}\n";
    echo "   Проект: " . ($emp['project_code'] ?: '(не найден)') . "\n\n";
}

echo str_repeat("=", 70) . "\n";
echo "Пример форматированного вывода:\n\n";

foreach (array_slice($employees, 0, 3) as $emp) {
    $daysWord = $personnelService->getDaysWord($emp['days']);
    echo "{$emp['icon']} {$emp['fio']} - {$emp['days']} {$daysWord}\n";
    echo "   📅 На объекте с: {$emp['start_date']}\n";
    
    if (!empty($emp['project_code'])) {
        echo "   💼 Проект: {$emp['project_code']}\n";
    }
    
    echo "\n";
}
