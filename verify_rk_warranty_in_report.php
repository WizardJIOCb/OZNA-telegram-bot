<?php
require_once __DIR__ . '/vendor/autoload.php';

ini_set('memory_limit', '256M');

echo "====================================\n";
echo "ПРОВЕРКА РК И ГАРАНТИИ В ВЫГРУЗКЕ\n";
echo "====================================\n\n";

$pdo = new PDO("mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4", "WizardJIOCb", "7qQ#28051961");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получаем записи за последний месяц
$startDate = date('Y-m-d 00:00:00', strtotime('-60 days'));
$endDate = date('Y-m-d 23:59:59');

echo "Период: {$startDate} - {$endDate}\n\n";

// Ищем записи гарантии
echo "=== ЗАПИСИ ГАРАНТИИ ===\n";
$sql = "SELECT COUNT(*) as count FROM ozna_tabel 
        WHERE (message LIKE '%Работы по гарантии%' OR message LIKE '%гаранти%')
        AND dt >= ? AND dt <= ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$startDate, $endDate]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Всего записей гарантии: {$result['count']}\n\n";

// Ищем записи РК
echo "=== ЗАПИСИ РК ===\n";
$sql = "SELECT COUNT(*) as count FROM ozna_tabel 
        WHERE (message LIKE '%Устранени% замечани% РК%' OR message LIKE '%замечани% РК%')
        AND dt >= ? AND dt <= ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$startDate, $endDate]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Всего записей РК: {$result['count']}\n\n";

// Примеры записей
echo "=== ПРИМЕРЫ ЗАПИСЕЙ ===\n\n";

echo "--- Гарантия (последняя) ---\n";
$sql = "SELECT message FROM ozna_tabel 
        WHERE message LIKE '%Работы по гарантии%'
        AND dt >= ? AND dt <= ?
        ORDER BY dt DESC LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$startDate, $endDate]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    echo $row['message'] . "\n\n";
}

echo "--- РК (последняя) ---\n";
$sql = "SELECT message FROM ozna_tabel 
        WHERE (message LIKE '%Устранени%' AND message LIKE '%замечани%')
        ORDER BY dt DESC LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    echo $row['message'] . "\n\n";
}

echo "✅ Проверка завершена!\n";
echo "Если количество записей > 0, значит данные есть в базе.\n";
echo "Теперь нужно проверить, правильно ли они парсятся в отчете.\n";
