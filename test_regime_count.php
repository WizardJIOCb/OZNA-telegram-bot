<?php
// Test regime setup counting

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ321';
$milestoneName = 'Режимная наладка';

echo "=== Testing '{$milestoneName}' for {$projectCode} ===\n\n";

// Current query (what card uses)
echo "1. Current query:\n";
$stmt = $conn->prepare("
    SELECT COUNT(*) as days_count
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE '%Работы по графику%'
    AND ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
");
$stmt->bind_param('ss', $projectCode, $milestoneName);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
echo "   Result: {$row['days_count']} days\n\n";

// Show actual messages
echo "2. All '{$milestoneName}' entries:\n";
$stmt = $conn->prepare("
    SELECT ot.message
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
");
$stmt->bind_param('ss', $projectCode, $milestoneName);
$stmt->execute();
$result = $stmt->get_result();

$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    if (preg_match('/<b>(.*?)<\/b>/', $row['message'], $matches)) {
        $activity = strip_tags($matches[1]);
        echo "   #{$count}: {$activity}\n";
    }
}

echo "\nTotal: {$count} entries\n";
