<?php
require_once __DIR__ . '/vendor/autoload.php';
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();
$projectCode = 'ОИ321';

$stmt = $conn->prepare("
    SELECT ot.message
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND (ot.message LIKE '%💰 Сдача авансового отчета%' 
         OR ot.message LIKE '%📋 Разработка графика работ%'
         OR ot.message LIKE '%📋 Разработка программы работ%'
         OR ot.message LIKE '%Подготовка к служебной поездке%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

echo "PNR Project Activity entries:\n\n";
$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    if (preg_match('/<b>(.*?)<\/b>/', $row['message'], $matches)) {
        echo "#{$count}: " . strip_tags($matches[1]) . "\n";
    }
}
