<?php
// Check the 1 SMR RK entry

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ321';

echo "=== SMR RK entry for {$projectCode} ===\n\n";

$stmt = $conn->prepare("
    SELECT ot.id, ot.message, od.data
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE '%Устранени%'
    AND ot.message LIKE '%замечаний РК%'
    AND (ot.message LIKE '%📦%' OR ot.message LIKE '%Шеф-монтаж%' OR ot.message LIKE '%Прочее оборудование%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $userData = json_decode($row['data'], true);
    $fn = $userData['functional_direction'] ?? 'Unknown';
    
    echo "ID: {$row['id']}\n";
    echo "FN: {$fn}\n";
    echo "Full message:\n";
    echo $row['message'] . "\n";
}
