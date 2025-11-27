<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

echo "=== БЫСТРАЯ ПРОВЕРКА: ПЕТРОВ ===\n\n";

$userId = 4225504; // Петров Алексей Владимирович

$conn = $dbService->getConnection();
$query = "SELECT dt, message FROM ozna_tabel WHERE user_id = ? ORDER BY dt DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

echo "5 последних записей:\n";
$reflection = new ReflectionClass($personnelService);
$isWorkMethod = $reflection->getMethod('isWorkActivity');
$isWorkMethod->setAccessible(true);

while ($row = $result->fetch_assoc()) {
    $isWork = $isWorkMethod->invoke($personnelService, $row['message']);
    $type = $isWork ? "🔧 РАБОЧАЯ" : "🏢 НЕРАБОЧАЯ";
    
    preg_match('/запись в табель:\s*<b>(.+?)<\/b>/', $row['message'], $matches);
    $activity = $matches[1] ?? 'Неизвестно';
    
    echo "  • " . substr($row['dt'], 0, 10) . " [$type] $activity\n";
}

// Проверяем статус
$firstSessionMethod = $reflection->getMethod('findFirstEntryInCurrentSession');
$firstSessionMethod->setAccessible(true);
$sessionStart = $firstSessionMethod->invoke($personnelService, $userId);

$freeTimeMethod = $reflection->getMethod('findLastContinuousWorkPeriod');
$freeTimeMethod->setAccessible(true);
$freeTimeStart = $freeTimeMethod->invoke($personnelService, $userId);

echo "\nТекущий статус:\n";

if ($sessionStart) {
    echo "✅ НА ОБЪЕКТЕ (с " . $sessionStart->format('d.m.Y') . ")\n";
} else if ($freeTimeStart) {
    echo "🏖️  НА СВОБОДНОЙ ВАХТЕ (с " . $freeTimeStart->format('d.m.Y') . ")\n";
} else {
    echo "⚪ БЕЗ ДАННЫХ\n";
}
