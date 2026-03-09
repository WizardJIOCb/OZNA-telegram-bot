<?php
require_once __DIR__ . '/vendor/autoload.php';

$conn = (new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService())->getConnection();

$project = '%815-01%';

// Count milestone confirmations for PNR
$stmt = $conn->prepare("
    SELECT COUNT(*) as cnt
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE ?
    AND (ot.message LIKE '%Этап наладки завершен%' 
         OR ot.message LIKE '%Этап шеф-монтажа завершен%' 
         OR ot.message LIKE '%Подтвердить достижение вехи%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
");
$stmt->bind_param('s', $project);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

echo "Milestone confirmations for 815-01 (PNR): " . $row['cnt'] . " records\n";
