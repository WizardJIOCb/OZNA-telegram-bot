<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();
$conn = $dbService->getConnection();

echo "=== УДАЛЕНИЕ ОШИБОЧНОЙ ЗАПИСИ: ГИЛЯЗТДИНОВ ===\n\n";

$userId = 858394806; // Гилязтдинов Динар Радикович

// Находим запись "Простой" от 20.10.2025
$query = "SELECT id, dt, message FROM ozna_tabel 
          WHERE user_id = ? 
          AND DATE(dt) = '2025-10-20'
          AND message LIKE '%Простой%'
          ORDER BY dt DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

echo "Найдено записей 'Простой' от 20.10.2025: " . count($records) . "\n\n";

if (empty($records)) {
    echo "❌ Записи не найдены!\n";
    exit;
}

foreach ($records as $i => $record) {
    echo ($i + 1) . ". ID: {$record['id']}\n";
    echo "   Дата: {$record['dt']}\n";
    
    // Извлекаем краткое описание
    preg_match('/запись в табель:\s*<b>(.+?)<\/b>/', $record['message'], $matches);
    $activity = $matches[1] ?? 'Неизвестно';
    echo "   Активность: $activity\n\n";
}

echo str_repeat("=", 70) . "\n";
echo "УДАЛЯЮ ЗАПИСИ...\n\n";

foreach ($records as $i => $record) {
    $recordId = $record['id'];
    
    echo ($i + 1) . ". Удаление ID $recordId... ";
    
    $query = "DELETE FROM ozna_tabel WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $recordId);
    
    if ($stmt->execute()) {
        echo "✅ Удалено\n";
    } else {
        echo "❌ Ошибка\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "✅ УДАЛЕНИЕ ЗАВЕРШЕНО!\n";
echo "Удалено записей: " . count($records) . "\n\n";

echo "Теперь проверяем статус...\n\n";

// Проверяем новый статус
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$personnelService = new PersonnelService($dbService);

$reflection = new ReflectionClass($personnelService);
$firstSessionMethod = $reflection->getMethod('findFirstEntryInCurrentSession');
$firstSessionMethod->setAccessible(true);
$sessionStart = $firstSessionMethod->invoke($personnelService, $userId);

$freeTimeMethod = $reflection->getMethod('findLastContinuousWorkPeriod');
$freeTimeMethod->setAccessible(true);
$freeTimeStart = $freeTimeMethod->invoke($personnelService, $userId);

echo "📊 НОВЫЙ СТАТУС:\n";
echo str_repeat("-", 70) . "\n";

if ($sessionStart) {
    echo "✅ НА ОБЪЕКТЕ (с " . $sessionStart->format('d.m.Y') . ")\n";
} else if ($freeTimeStart) {
    echo "🏖️  НА СВОБОДНОЙ ВАХТЕ (с " . $freeTimeStart->format('d.m.Y') . ")\n";
} else {
    echo "⚪ БЕЗ ДАННЫХ\n";
}
