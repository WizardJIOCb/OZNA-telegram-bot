<?php
// Debug: Check OI321 entries breakdown

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ321';

echo "=== Breakdown for {$projectCode} ===\n\n";

// Get all entries excluding testers
$stmt = $conn->prepare("
    SELECT ot.id, ot.message, od.data
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ORDER BY ot.id DESC
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
while ($row = $result->fetch_assoc()) {
    $total++;
    $userData = json_decode($row['data'], true);
    $fn = $userData['functional_direction'] ?? 'Unknown';
    
    echo "#{$total}\n";
    echo "FN: {$fn}\n";
    echo substr($row['message'], 0, 200) . "\n";
    echo "---\n\n";
}

echo "\nTotal entries (excluding testers): {$total}\n";
