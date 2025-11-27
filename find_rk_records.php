<?php
$pdo = new PDO('mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4', 'WizardJIOCb', '7qQ#28051961');

echo "=== ПОИСК 'Устранение замечаний РК' ===\n\n";
$stmt = $pdo->query("SELECT message FROM ozna_tabel WHERE message LIKE '%Устранение%' OR message LIKE '%замечани%' LIMIT 10");
$count = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $count++;
    echo "Запись #{$count}:\n";
    echo $row['message'] . PHP_EOL;
    echo "----------------------------------------\n\n";
}

if ($count === 0) {
    echo "❌ Записей не найдено!\n";
}
