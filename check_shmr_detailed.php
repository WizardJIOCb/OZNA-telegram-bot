<?php
$pdo = new PDO('mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4', 'WizardJIOCb', '7qQ#28051961');

$users = $pdo->query("SELECT user_id FROM ozna_data WHERE JSON_EXTRACT(data, '$.functional_direction') = 'Шеф-монтаж'")->fetchAll(PDO::FETCH_COLUMN);

if (empty($users)) {
    echo "Нет пользователей ШМР\n";
    exit;
}

$placeholders = implode(',', array_fill(0, count($users), '?'));

// Ищем сообщения с " - " разделителем
echo "=== СООБЩЕНИЯ С РАЗДЕЛИТЕЛЕМ ' - ' ===\n\n";

$sql = "SELECT message FROM ozna_tabel 
        WHERE user_id IN ($placeholders) 
        AND message LIKE '% - %'
        AND message LIKE '%Шеф-монтажные работы%'
        ORDER BY dt DESC 
        LIMIT 10";

$stmt = $pdo->prepare($sql);
$stmt->execute($users);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['message'] . "\n---\n";
}

// Ищем сообщения с подкатегориями
echo "\n=== СООБЩЕНИЯ С ПОДКАТЕГОРИЯМИ ===\n\n";

$subcategories = [
    'Монтаж оборудования',
    'Прокладка кабеля',
    'Подключение оборудования',
    'Маркировка',
    'Электрооборудование',
    'Автоматизация',
    'Пожарная сигнализация'
];

foreach ($subcategories as $subcategory) {
    echo "\n--- $subcategory ---\n";
    
    $sql = "SELECT message FROM ozna_tabel 
            WHERE user_id IN ($placeholders) 
            AND message LIKE ?
            AND message LIKE '%Шеф-монтажные работы%'
            ORDER BY dt DESC 
            LIMIT 2";
    
    $stmt = $pdo->prepare($sql);
    $params = array_merge($users, ["%$subcategory%"]);
    $stmt->execute($params);
    
    $found = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['message'] . "\n";
        $found = true;
    }
    
    if (!$found) {
        echo "Нет записей\n";
    }
}
