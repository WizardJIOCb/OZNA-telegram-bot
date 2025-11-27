<?php
require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO("mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4", "WizardJIOCb", "7qQ#28051961");

echo "====================================\n";
echo "ПРИМЕРЫ СООБЩЕНИЙ ШМР\n";
echo "====================================\n\n";

// Получаем пользователей ШМР
$users = $pdo->query("SELECT user_id FROM ozna_data WHERE JSON_EXTRACT(data, '$.functional_direction') = 'Шеф-монтаж'")->fetchAll(PDO::FETCH_COLUMN);

if (empty($users)) {
    echo "Нет пользователей ШМР\n";
    exit;
}

$placeholders = implode(',', array_fill(0, count($users), '?'));

// Получаем разные типы записей
echo "=== ШЕФ-МОНТАЖНЫЕ РАБОТЫ ===\n\n";
$sql = "SELECT message FROM ozna_tabel 
        WHERE user_id IN ($placeholders) 
        AND message LIKE '%Проект%'
        AND (message LIKE '%Монтаж%' OR message LIKE '%Сборка%' OR message LIKE '%Внутриблочный%')
        ORDER BY dt DESC 
        LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute($users);
while ($msg = $stmt->fetch(PDO::FETCH_COLUMN)) {
    echo $msg . "\n---\n";
}

echo "\n=== ПРОЕКТНАЯ ДЕЯТЕЛЬНОСТЬ ===\n\n";
$sql = "SELECT message FROM ozna_tabel 
        WHERE user_id IN ($placeholders) 
        AND message LIKE '%Проектная деятельность%'
        ORDER BY dt DESC 
        LIMIT 3";
$stmt = $pdo->prepare($sql);
$stmt->execute($users);
while ($msg = $stmt->fetch(PDO::FETCH_COLUMN)) {
    echo $msg . "\n---\n";
}

echo "\n=== УСТРАНЕНИЕ ЗАМЕЧАНИЙ РК ===\n\n";
$sql = "SELECT message FROM ozna_tabel 
        WHERE user_id IN ($placeholders) 
        AND message LIKE '%Устранени%замечани%'
        ORDER BY dt DESC 
        LIMIT 3";
$stmt = $pdo->prepare($sql);
$stmt->execute($users);
while ($msg = $stmt->fetch(PDO::FETCH_COLUMN)) {
    echo $msg . "\n---\n";
}

echo "\n=== РАБОТЫ ПО ГАРАНТИИ ===\n\n";
$sql = "SELECT message FROM ozna_tabel 
        WHERE user_id IN ($placeholders) 
        AND message LIKE '%гарантии%'
        ORDER BY dt DESC 
        LIMIT 3";
$stmt = $pdo->prepare($sql);
$stmt->execute($users);
while ($msg = $stmt->fetch(PDO::FETCH_COLUMN)) {
    echo $msg . "\n---\n";
}

echo "\n=== ПРОСТОЙ ===\n\n";
$sql = "SELECT message FROM ozna_tabel 
        WHERE user_id IN ($placeholders) 
        AND message LIKE '%Простой%'
        ORDER BY dt DESC 
        LIMIT 3";
$stmt = $pdo->prepare($sql);
$stmt->execute($users);
while ($msg = $stmt->fetch(PDO::FETCH_COLUMN)) {
    echo $msg . "\n---\n";
}

echo "\n=== ПРОЧЕЕ (ТЕХНИЧЕСКОЕ ОБОРУДОВАНИЕ) ===\n\n";
$sql = "SELECT message FROM ozna_tabel 
        WHERE user_id IN ($placeholders) 
        AND message LIKE '%Прочее%Технологическое%'
        ORDER BY dt DESC 
        LIMIT 3";
$stmt = $pdo->prepare($sql);
$stmt->execute($users);
while ($msg = $stmt->fetch(PDO::FETCH_COLUMN)) {
    echo $msg . "\n---\n";
}
