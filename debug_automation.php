<?php
// Check Automation entries for OI321

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ321';

echo "=== Automation entries for {$projectCode} ===\n\n";

// Current query (what card uses)
echo "1. Current query (card shows 20):\n";
$stmt = $conn->prepare("
    SELECT COUNT(*) as cnt
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%', 'Автоматизация', '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
echo "   Result: {$data['cnt']} entries\n\n";

// Show actual messages
echo "2. All Automation entries:\n";
$stmt = $conn->prepare("
    SELECT ot.message
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE '%Автоматизация%'
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ORDER BY ot.id DESC
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

$count = 0;
$schedule_work = 0;
$other_work = 0;

while ($row = $result->fetch_assoc()) {
    $count++;
    
    // Extract activity text
    if (preg_match('/<b>(.*?Автоматизация.*?)<\/b>/', $row['message'], $matches)) {
        $activity = strip_tags($matches[1]);
        echo "   #{$count}: {$activity}\n";
        
        if (strpos($activity, 'Работы по графику') !== false) {
            $schedule_work++;
        } else {
            $other_work++;
        }
    }
}

echo "\n";
echo "Total: {$count} entries\n";
echo "  - Работы по графику (Автоматизация): {$schedule_work}\n";
echo "  - Другие работы (Автоматизация): {$other_work}\n";
