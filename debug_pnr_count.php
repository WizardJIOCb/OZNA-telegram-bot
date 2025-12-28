<?php
/**
 * Debug script to check PNR count for project ОИ879
 */

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\DB;

// Database configuration
$db_config = [
    'host' => 'localhost',
    'port' => 3306,
    'user' => 'root',
    'password' => '',
    'database' => 'ozna_bot'
];

// Connect to database
$mysqli = new mysqli(
    $db_config['host'],
    $db_config['user'],
    $db_config['password'],
    $db_config['database'],
    $db_config['port']
);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

$projectCode = 'ОИ879';

echo "=== DEBUG PNR COUNT FOR PROJECT {$projectCode} ===\n\n";

// Query 1: Current card query (precise pattern)
echo "Query 1: CARD QUERY (Precise pattern with HTML tags)\n";
echo "Pattern: Проект <b>{$projectCode}</b>\n";
$stmt = $mysqli->prepare("
    SELECT COUNT(*) as count
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE (ot.message LIKE CONCAT('%Проект <b>', ?, '</b>%') 
           OR ot.message LIKE CONCAT('%Проект <b>', ?, '</b>:%')
           OR ot.message LIKE CONCAT('%проект <b>', ?, '</b>%'))
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
");
$stmt->bind_param('sss', $projectCode, $projectCode, $projectCode);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
echo "Result: {$row['count']} records\n\n";

// Query 2: Simple LIKE pattern (old way)
echo "Query 2: SIMPLE LIKE PATTERN\n";
echo "Pattern: %{$projectCode}%\n";
$stmt = $mysqli->prepare("
    SELECT COUNT(*) as count
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
echo "Result: {$row['count']} records\n\n";

// Query 3: Report query (like export does)
echo "Query 3: REPORT QUERY (All entries, filter Тестировщик after)\n";
echo "Pattern: %{$projectCode}%\n";
$stmt = $mysqli->prepare("
    SELECT ot.user_id, ot.message, JSON_EXTRACT(od.data, '$.functional_direction') as fn
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

$totalCount = 0;
$pnrCount = 0;
$byFn = [];

while ($row = $result->fetch_assoc()) {
    $totalCount++;
    $fn = trim($row['fn'], '"'); // Remove JSON quotes
    
    if (!isset($byFn[$fn])) {
        $byFn[$fn] = 0;
    }
    $byFn[$fn]++;
    
    if ($fn === 'Пусконаладка') {
        $pnrCount++;
    }
}

echo "Total records (excl. Тестировщик): {$totalCount}\n";
echo "PNR records (Пусконаладка only): {$pnrCount}\n";
echo "\nBreakdown by functional_direction:\n";
foreach ($byFn as $fn => $count) {
    echo "  - {$fn}: {$count} records\n";
}
echo "\n";

// Query 4: Show sample messages to see format
echo "Query 4: SAMPLE MESSAGES (first 5)\n";
$stmt = $mysqli->prepare("
    SELECT ot.message, JSON_EXTRACT(od.data, '$.functional_direction') as fn
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
    LIMIT 5
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $fn = trim($row['fn'], '"');
    echo "FN: {$fn}\n";
    echo "Message: " . substr($row['message'], 0, 200) . "...\n";
    echo "---\n";
}

$mysqli->close();

echo "\n=== DEBUG COMPLETE ===\n";
