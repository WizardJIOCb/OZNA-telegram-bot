<?php
require_once __DIR__ . '/vendor/autoload.php';

ini_set('memory_limit', '256M');

echo "====================================\n";
echo "ПРОВЕРКА ЗАПИСЕЙ ТЕКУЩЕЙ И ПРОЕКТНОЙ ДЕЯТЕЛЬНОСТИ\n";
echo "====================================\n\n";

$pdo = new PDO("mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4", "WizardJIOCb", "7qQ#28051961");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Поиск записей с текущей деятельностью
$sql = "SELECT id, user_id, message, dt 
        FROM ozna_tabel 
        WHERE message LIKE '%Текущая деятельность%' 
        OR message LIKE '%Проектная деятельность%'
        ORDER BY dt DESC 
        LIMIT 20";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Найдено записей: " . count($result) . "\n\n";

foreach ($result as $row) {
    echo "----------------------------------------\n";
    echo "ID: {$row['id']}\n";
    echo "User ID: {$row['user_id']}\n";
    echo "Дата создания: {$row['dt']}\n";
    echo "Сообщение:\n{$row['message']}\n";
    echo "----------------------------------------\n\n";
}

// Проверим также записи с проектами
echo "\n====================================\n";
echo "ЗАПИСИ С ПРОЕКТАМИ (последние 10)\n";
echo "====================================\n\n";

$sql2 = "SELECT id, user_id, message, dt 
        FROM ozna_tabel 
        WHERE message LIKE '%Проект <b>%</b>%'
        ORDER BY dt DESC 
        LIMIT 10";

$stmt2 = $pdo->prepare($sql2);
$stmt2->execute();
$result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

echo "Найдено записей с проектами: " . count($result2) . "\n\n";

foreach ($result2 as $row) {
    echo "----------------------------------------\n";
    echo "ID: {$row['id']}\n";
    echo "User ID: {$row['user_id']}\n";
    echo "Дата создания: {$row['dt']}\n";
    echo "Сообщение:\n{$row['message']}\n";
    echo "----------------------------------------\n\n";
}
