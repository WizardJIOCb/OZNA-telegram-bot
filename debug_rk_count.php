<?php
// Check RK comments count for OI321 PNR

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ321';

echo "=== Checking RK comments for {$projectCode} ===\n\n";

// Current query logic (what card shows)
echo "1. Current PNR logic (card shows 16):\n";
$stmt = $conn->prepare("
    SELECT COUNT(*) as cnt
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
    AND (ot.message LIKE '%🤖%' OR ot.message LIKE '%Раздел%' OR ot.message NOT LIKE '%📦%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    AND ot.message LIKE CONCAT('%', ?, '%')
");
$stmt->bind_param('ss', $projectCode, $projectCode);
$stmt->execute();
$result = $stmt->get_result();
$current = $result->fetch_assoc();
echo "   Result: {$current['cnt']} entries\n\n";

// All RK entries (no emoji filter)
echo "2. All 'Устранени' + 'замечаний РК' entries:\n";
$stmt = $conn->prepare("
    SELECT COUNT(*) as cnt
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE '%Устранени%'
    AND ot.message LIKE '%замечаний РК%'
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();
$all = $result->fetch_assoc();
echo "   Result: {$all['cnt']} entries\n\n";

// Show actual messages
echo "3. Actual RK messages:\n";
$stmt = $conn->prepare("
    SELECT ot.message, od.data
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE '%Устранени%'
    AND ot.message LIKE '%замечаний РК%'
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ORDER BY ot.id DESC
    LIMIT 20
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    $userData = json_decode($row['data'], true);
    $fn = $userData['functional_direction'] ?? 'Unknown';
    
    echo "   #{$count} FN: {$fn}\n";
    
    // Extract the actual activity text
    if (preg_match('/<b>🤖.*?<\/b>|<b>📦.*?<\/b>/', $row['message'], $matches)) {
        echo "   " . strip_tags($matches[0]) . "\n";
    }
    echo "\n";
}

echo "Total shown: {$count} entries\n";
