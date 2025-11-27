<?php
$pdo = new PDO('mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4', 'WizardJIOCb', '7qQ#28051961');

// Ищем записи с "Монтаж", "Сборка" и т.д. в ШМР
$activities = [
    'Подготовительные работы',
    'Монтаж блоков',
    'Сборка здания',
    'Внутриблочный монтаж',
    'Подписание акта ШМР'
];

$users = $pdo->query("SELECT user_id FROM ozna_data WHERE JSON_EXTRACT(data, '$.functional_direction') = 'Шеф-монтаж'")->fetchAll(PDO::FETCH_COLUMN);

if (empty($users)) {
    echo "Нет пользователей ШМР\n";
    exit;
}

$placeholders = implode(',', array_fill(0, count($users), '?'));

foreach ($activities as $activity) {
    echo "\n=== $activity ===\n\n";
    
    $sql = "SELECT message FROM ozna_tabel 
            WHERE user_id IN ($placeholders) 
            AND message LIKE ?
            ORDER BY dt DESC 
            LIMIT 3";
    
    $stmt = $pdo->prepare($sql);
    $params = array_merge($users, ["%$activity%"]);
    $stmt->execute($params);
    
    $found = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['message'] . "\n---\n";
        $found = true;
    }
    
    if (!$found) {
        echo "Нет записей\n";
    }
}
