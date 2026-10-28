<?php
require_once __DIR__ . '/vendor/autoload.php';

$conn = (new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService())->getConnection();

$project = '%815-01%';

// Find ALL entries that match export's milestone filter
$stmt = $conn->prepare("
    SELECT ot.id, ot.message
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE ?
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
");
$stmt->bind_param('s', $project);
$stmt->execute();
$result = $stmt->get_result();

$milestones = [];

while ($row = $result->fetch_assoc()) {
    // Check if this is a milestone using export logic
    if (preg_match('/Этап шеф-монтажа завершен|Этап наладки завершен|Подтвердить достижение вехи/', $row['message'])) {
        $milestones[] = $row;
    }
}

echo "Found " . count($milestones) . " milestone confirmation entries:\n\n";

foreach ($milestones as $idx => $milestone) {
    echo "=== Milestone #" . ($idx + 1) . " ===\n";
    echo "ID: {$milestone['id']}\n";
    echo "Message: {$milestone['message']}\n\n";
}
