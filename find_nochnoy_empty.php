<?php
require_once __DIR__ . '/vendor/autoload.php';

$conn = (new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService())->getConnection();

$userId = 5499115173; // Nochnoy
$project = '%815-01%';

// Find ALL records from Nochnoy for this project
$stmt = $conn->prepare("
    SELECT ot.id, ot.message, LENGTH(ot.message) as msg_len
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.user_id = ?
    AND ot.message LIKE ?
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
    ORDER BY LENGTH(ot.message), ot.id
");
$stmt->bind_param('is', $userId, $project);
$stmt->execute();
$result = $stmt->get_result();

echo "All Nochnoy's PNR records for 815-01 (sorted by message length):\n\n";

$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    echo "#{$count} - ID: {$row['id']}, Length: {$row['msg_len']} chars\n";
    echo "Message: " . mb_substr($row['message'], 0, 200) . "\n\n";
    
    // Show first 2 (shortest ones - likely the empty ones)
    if ($count == 2) {
        echo "=== These are likely the 2 empty/problematic records ===\n\n";
    }
}

echo "Total: {$count} records\n";
