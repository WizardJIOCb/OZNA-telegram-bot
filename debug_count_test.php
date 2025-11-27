<?php
// Debug: Test exact count query

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ321';

echo "=== Testing count query for {$projectCode} ===\n\n";

// The exact query from card
$stmt = $conn->prepare("
    SELECT COUNT(*) as entry_count
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();
$tabelData = $result->fetch_assoc();
$entryCount = $tabelData['entry_count'] ?? 0;

echo "Query result: {$entryCount} entries\n\n";

// Also test without FN filter
$stmt2 = $conn->prepare("
    SELECT COUNT(*) as entry_count
    FROM ozna_tabel ot
    WHERE ot.message LIKE CONCAT('%', ?, '%')
");
$stmt2->bind_param('s', $projectCode);
$stmt2->execute();
$result2 = $stmt2->get_result();
$data2 = $result2->fetch_assoc();
echo "Without FN filter: {$data2['entry_count']} entries\n";
