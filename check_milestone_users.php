<?php
require_once __DIR__ . '/vendor/autoload.php';

$conn = (new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService())->getConnection();

// Check which user_id created these 2 milestones
$ids = [3046, 7424];

foreach ($ids as $id) {
    $stmt = $conn->prepare("
        SELECT ot.user_id, od.data
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $data = json_decode($row['data'], true);
    $fn = $data['functional_direction'] ?? 'NOT SET';
    $fio = $data['fio'] ?? 'NOT SET';
    
    echo "ID {$id}: user_id={$row['user_id']}, FIO={$fio}, FN={$fn}\n";
}
