<?php
require_once __DIR__ . '/vendor/autoload.php';

ini_set('memory_limit', '256M');

echo "====================================\n";
echo "ПРОВЕРКА ЗАПИСЕЙ РК И ГАРАНТИИ\n";
echo "====================================\n\n";

$pdo = new PDO("mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4", "WizardJIOCb", "7qQ#28051961");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Поиск записей с РК
echo "=== ЗАПИСИ РК (последние 10) ===\n\n";
$sql = "SELECT id, user_id, message, dt 
        FROM ozna_tabel 
        WHERE message LIKE '%РК%'
        OR message LIKE '%замечани%'
        ORDER BY dt DESC 
        LIMIT 10";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Найдено записей РК: " . count($result) . "\n\n";

foreach ($result as $row) {
    echo "----------------------------------------\n";
    echo "ID: {$row['id']}\n";
    echo "User ID: {$row['user_id']}\n";
    echo "Дата: {$row['dt']}\n";
    echo "Сообщение:\n{$row['message']}\n";
    echo "----------------------------------------\n\n";
}

// Поиск записей с гарантией
echo "\n=== ЗАПИСИ ГАРАНТИИ (последние 10) ===\n\n";
$sql2 = "SELECT id, user_id, message, dt 
        FROM ozna_tabel 
        WHERE message LIKE '%гаранти%'
        OR message LIKE '%Гаранти%'
        ORDER BY dt DESC 
        LIMIT 10";

$stmt2 = $pdo->prepare($sql2);
$stmt2->execute();
$result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

echo "Найдено записей гарантии: " . count($result2) . "\n\n";

foreach ($result2 as $row) {
    echo "----------------------------------------\n";
    echo "ID: {$row['id']}\n";
    echo "User ID: {$row['user_id']}\n";
    echo "Дата: {$row['dt']}\n";
    echo "Сообщение:\n{$row['message']}\n";
    echo "----------------------------------------\n\n";
}
