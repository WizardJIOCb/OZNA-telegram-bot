<?php
$pdo = new PDO('mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4', 'WizardJIOCb', '7qQ#28051961');

$sql = "SELECT message FROM ozna_tabel 
        WHERE message LIKE '%Шеф-монтажные работы%' 
        AND message LIKE '%Электрооборудование%' 
        ORDER BY dt DESC 
        LIMIT 5";

$stmt = $pdo->query($sql);

echo "=== СООБЩЕНИЯ ШМР С ЭЛЕКТРООБОРУДОВАНИЕМ ===\n\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['message'] . "\n";
    echo "---\n";
}
