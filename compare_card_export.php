<?php
require_once __DIR__ . '/vendor/autoload.php';

$conn = (new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService())->getConnection();

$project = '%815-01%';

// Query 1: Card logic (current)
echo "=== CARD LOGIC ===\n";
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT ot.id) as pnr_total_count
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE ?
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
    AND ot.message NOT LIKE '%Этап наладки завершен%'
    AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
");
$stmt->bind_param('s', $project);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
echo "Card count: " . $row['pnr_total_count'] . "\n\n";

// Query 2: Export logic (simulated)
echo "=== EXPORT LOGIC (simulated) ===\n";
// First get all entries
$stmt = $conn->prepare("
    SELECT ot.id, ot.message, od.user_id, JSON_EXTRACT(od.data, '$.functional_direction') as fn
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE ?
");
$stmt->bind_param('s', $project);
$stmt->execute();
$result = $stmt->get_result();

$totalCount = 0;
$excludedTester = 0;
$excludedMilestones = 0;

while ($row = $result->fetch_assoc()) {
    $fn = json_decode($row['fn']);
    
    // Exclude Тестировщик
    if ($fn === 'Тестировщик') {
        $excludedTester++;
        continue;
    }
    
    // Exclude milestones
    if (preg_match('/Этап шеф-монтажа завершен|Этап наладки завершен|Подтвердить достижение вехи/', $row['message'])) {
        $excludedMilestones++;
        continue;
    }
    
    $totalCount++;
}

echo "Export count: {$totalCount}\n";
echo "Excluded (Tester): {$excludedTester}\n";
echo "Excluded (Milestones): {$excludedMilestones}\n\n";

echo "=== DIFFERENCE ===\n";
echo "Card - Export = " . ($row['pnr_total_count'] - $totalCount) . " records\n";
