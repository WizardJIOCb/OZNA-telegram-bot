<?php
// Find the missing entry in OI321

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ321';

echo "=== Finding missing entry for {$projectCode} ===\n\n";

// Total count
$stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$total = $data['total'];
echo "Total entries: {$total}\n\n";

// Get all entries grouped by activity type
$stmt = $conn->prepare("
    SELECT ot.message, od.data
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ORDER BY ot.id
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

$categories = [
    'automation_schedule' => 0,
    'regime_setup' => 0,
    'rk_comments' => 0,
    'additional_work' => 0,
    'mobilization' => 0,
    'other' => 0
];

$otherEntries = [];

while ($row = $result->fetch_assoc()) {
    $msg = $row['message'];
    $userData = json_decode($row['data'], true);
    $fn = $userData['functional_direction'] ?? 'Unknown';
    
    // Extract activity
    if (preg_match('/<b>(.*?)<\/b>/', $msg, $matches)) {
        $activity = strip_tags($matches[1]);
        
        // Categorize
        if (strpos($msg, 'Работы по графику') !== false && strpos($msg, 'Автоматизация') !== false) {
            $categories['automation_schedule']++;
        } elseif (strpos($msg, 'Режимная наладка') !== false) {
            $categories['regime_setup']++;
        } elseif (strpos($msg, 'Устранени') !== false && strpos($msg, 'замечаний РК') !== false && $fn === 'Пусконаладка') {
            $categories['rk_comments']++;
        } elseif (strpos($msg, 'Дополнительные работы') !== false && strpos($msg, 'Работы по графику') === false && $fn === 'Пусконаладка') {
            $categories['additional_work']++;
        } elseif ((strpos($msg, 'Дорога') !== false || strpos($msg, 'Инструктаж') !== false || strpos($msg, 'Подготовка') !== false) && $fn === 'Пусконаладка') {
            $categories['mobilization']++;
        } else {
            $categories['other']++;
            $otherEntries[] = [
                'activity' => $activity,
                'fn' => $fn,
                'full_msg' => $msg
            ];
        }
    }
}

echo "Categorization:\n";
echo "  - Автоматизация (Работы по графику): {$categories['automation_schedule']}\n";
echo "  - Режимная наладка: {$categories['regime_setup']}\n";
echo "  - Устранение замечаний РК (ПНР): {$categories['rk_comments']}\n";
echo "  - Дополнительные работы (ПНР этапы): {$categories['additional_work']}\n";
echo "  - Мобилизационные мероприятия (ПНР): {$categories['mobilization']}\n";
echo "  - OTHER (uncategorized): {$categories['other']}\n";

$accounted = $categories['automation_schedule'] + $categories['regime_setup'] + 
             $categories['rk_comments'] + $categories['additional_work'] + 
             $categories['mobilization'];
echo "\nAccounted: {$accounted}\n";
echo "Total: {$total}\n";
echo "Missing: " . ($total - $accounted) . "\n";

if (!empty($otherEntries)) {
    echo "\n=== UNCATEGORIZED ENTRIES ===\n";
    foreach ($otherEntries as $idx => $entry) {
        echo "\n#{" . ($idx + 1) . "}\n";
        echo "FN: {$entry['fn']}\n";
        echo "Activity: {$entry['activity']}\n";
        echo "Full message:\n";
        echo $entry['full_msg'] . "\n";
    }
}
