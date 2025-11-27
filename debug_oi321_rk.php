<?php
// Debug script to check RK comments for OI321

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ321';

echo "=== Записи табеля для проекта {$projectCode} ===\n\n";

// Get all entries
$stmt = $conn->prepare("
    SELECT id, message 
    FROM ozna_tabel 
    WHERE message LIKE CONCAT('%', ?, '%')
    ORDER BY id DESC
    LIMIT 20
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

echo "Последние 20 записей:\n\n";
$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    echo "#{$count} ID: {$row['id']}\n";
    echo substr($row['message'], 0, 300) . "...\n";
    echo "---\n\n";
}

// Check for RK mentions
echo "\n=== Поиск по ключевым словам ===\n\n";

$searches = [
    'Устранение замечаний РК',
    'Устранения замечаний РК', 
    'УЗ РК',
    'замечаний РК',
    'замечани',
    'РК'
];

foreach ($searches as $keyword) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as cnt 
        FROM ozna_tabel 
        WHERE message LIKE CONCAT('%', ?, '%', ?, '%')
    ");
    $stmt->bind_param('ss', $projectCode, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo "'{$keyword}': {$row['cnt']} записей\n";
}
