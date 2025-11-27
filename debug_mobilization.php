<?php
// Check mobilization entries for OI321

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ321';

echo "=== Mobilization entries for {$projectCode} ===\n\n";

$stmt = $conn->prepare("
    SELECT ot.id, ot.message, od.data
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    AND (ot.message LIKE '%🚗 Дорога%' OR ot.message LIKE '%📋 Инструктаж%' OR ot.message LIKE '%Подготовка%')
    ORDER BY ot.id DESC
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    $userData = json_decode($row['data'], true);
    $fn = $userData['functional_direction'] ?? 'Unknown';
    
    echo "#{$count}\n";
    echo "ID: {$row['id']}\n";
    echo "FN: {$fn}\n";
    echo "Message:\n";
    echo substr($row['message'], 0, 400) . "\n";
    echo "---\n\n";
}

echo "Total: {$count} entries\n";
