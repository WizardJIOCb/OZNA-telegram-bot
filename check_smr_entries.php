<?php
// Find SMR entry for OI321

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ321';

echo "=== SMR entries for {$projectCode} ===\n\n";

$stmt = $conn->prepare("
    SELECT ot.message
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж' 
         OR JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада')
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    echo "=== Entry #{$count} ===\n";
    echo $row['message'] . "\n\n";
}

echo "Total SMR entries: {$count}\n";
