<?php
require_once __DIR__ . '/vendor/autoload.php';

$conn = (new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService())->getConnection();

$project = '%815-01%';

// Get ALL PNR entries like card does
$stmt = $conn->prepare("
    SELECT ot.id, ot.message
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE ?
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
    AND ot.message NOT LIKE '%Этап наладки завершен%'
    AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
    ORDER BY ot.id
");
$stmt->bind_param('s', $project);
$stmt->execute();
$result = $stmt->get_result();

echo "Total PNR entries (card logic): " . $result->num_rows . "\n\n";

// Now apply export filter (milestone check)
$cardEntries = [];
$excludedByExport = [];

$result->data_seek(0); // Reset pointer
while ($row = $result->fetch_assoc()) {
    // Check if this would be excluded by export's isMilestoneEntry
    if (preg_match('/Этап шеф-монтажа завершен|Этап наладки завершен|Подтвердить достижение вехи/', $row['message'])) {
        $excludedByExport[] = $row;
    } else {
        $cardEntries[] = $row;
    }
}

echo "After export milestone filter: " . count($cardEntries) . "\n";
echo "Excluded by export (but NOT by card): " . count($excludedByExport) . "\n\n";

if (!empty($excludedByExport)) {
    echo "=== RECORDS EXCLUDED BY EXPORT BUT NOT BY CARD ===\n\n";
    foreach ($excludedByExport as $idx => $entry) {
        echo "Record #" . ($idx + 1) . ":\n";
        echo "ID: {$entry['id']}\n";
        echo "Message: {$entry['message']}\n\n";
    }
}
