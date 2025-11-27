<?php
// Debug script to check RK comments entries

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ815-01';

echo "=== Проверка записей 'Устранение замечаний РК' для проекта {$projectCode} ===\n\n";

// Check different variations
$keywords = [
    'Устранение замечаний РК',
    'Устранения замечаний РК',
    'Устранени',
    'замечан',
];

foreach ($keywords as $keyword) {
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

echo "\n=== Примеры сообщений с 'РК' ===\n\n";

$stmt = $conn->prepare("
    SELECT id, message 
    FROM ozna_tabel 
    WHERE message LIKE CONCAT('%', ?, '%РК%')
    LIMIT 10
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    echo "#{$count}\n";
    echo "ID: {$row['id']}\n";
    echo "Message: {$row['message']}\n";
    echo "---\n\n";
}

if ($count === 0) {
    echo "Нет записей с 'РК'\n";
}
