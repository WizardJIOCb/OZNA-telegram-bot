<?php
$pdo = new PDO('mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4', 'WizardJIOCb', '7qQ#28051961');

$sql = "SELECT message, user_id FROM ozna_tabel 
        WHERE message LIKE '%Шеф-монтажные работы%' 
        AND message LIKE '% - %'
        ORDER BY dt DESC 
        LIMIT 5";

$stmt = $pdo->query($sql);

echo "=== СООБЩЕНИЯ С 'ШЕФ-МОНТАЖНЫЕ РАБОТЫ' И РАЗДЕЛИТЕЛЕМ ' - ' ===\n\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Получаем ФИО пользователя
    $userSql = "SELECT JSON_EXTRACT(data, '$.fio') as fio, JSON_EXTRACT(data, '$.functional_direction') as fn 
                FROM ozna_data WHERE user_id = ?";
    $userStmt = $pdo->prepare($userSql);
    $userStmt->execute([$row['user_id']]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    $fio = $userData ? $userData['fio'] : 'Unknown';
    $fn = $userData ? $userData['fn'] : 'Unknown';
    
    echo "User: $fio ($fn)\n";
    echo $row['message'] . "\n";
    echo "---\n";
}
