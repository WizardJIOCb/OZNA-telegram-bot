<?php
/**
 * Debug script to analyze ZPI counting for project OI849
 */

require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO(
    "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
    "WizardJIOCb",
    "7qQ#28051961",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$projectCode = 'ОИ849';

echo "=== Диагностика подсчёта ЗПИ для проекта {$projectCode} ===\n\n";

// 1. Current query (как сейчас считается)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT ot.id) as zpi_count
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE CONCAT('%', ?, '%Участие в ЗПИ%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
");
$stmt->execute([$projectCode, $projectCode]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "1. Текущий подсчёт (LIKE '%Участие в ЗПИ%'): " . $row['zpi_count'] . " записей\n\n";

// 2. Show ALL entries that match current ZPI condition
echo "2. Записи, попадающие под условие ЗПИ:\n";
echo str_repeat('-', 100) . "\n";

$stmt = $pdo->prepare("
    SELECT ot.id, ot.user_id, ot.message, ot.dt, JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as fio
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE CONCAT('%', ?, '%Участие в ЗПИ%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ORDER BY ot.dt DESC
");
$stmt->execute([$projectCode, $projectCode]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$counter = 0;
foreach ($entries as $entry) {
    $counter++;
    echo "{$counter}. ID={$entry['id']} | {$entry['dt']} | {$entry['fio']}\n";
    echo "   Сообщение: " . mb_substr($entry['message'], 0, 150) . "...\n\n";
}
echo str_repeat('-', 100) . "\n";
echo "Всего записей по текущему условию: {$counter}\n\n";

// 3. Let's also check what the pattern actually matches
echo "3. Проверка паттерна LIKE:\n";
$stmt = $pdo->prepare("
    SELECT ot.id, ot.message, JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as fio
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
    AND ot.message LIKE '%ЗПИ%'
    ORDER BY ot.dt DESC
");
$stmt->execute([$projectCode]);
$allZpiMentions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Записи содержащие 'ЗПИ' (любой контекст):\n";
foreach ($allZpiMentions as $entry) {
    $hasUchastie = (strpos($entry['message'], 'Участие в ЗПИ') !== false) ? '✅' : '❌';
    echo "ID={$entry['id']} | Участие в ЗПИ: {$hasUchastie} | {$entry['fio']}\n";
    echo "   " . mb_substr($entry['message'], 0, 200) . "\n\n";
}

// 4. Check for entries with empty activity
echo "\n4. Записи с пустой активностью или только фамилией:\n";
$stmt = $pdo->prepare("
    SELECT ot.id, ot.message, ot.dt, JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as fio
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
    AND (
        ot.message NOT LIKE '%Добавлена запись в табель:%'
        OR LENGTH(TRIM(SUBSTRING_INDEX(ot.message, 'Добавлена запись в табель:', -1))) < 5
    )
    ORDER BY ot.dt DESC
    LIMIT 30
");
$stmt->execute([$projectCode]);
$emptyEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($emptyEntries as $entry) {
    echo "ID={$entry['id']} | {$entry['dt']} | {$entry['fio']}\n";
    echo "   Сообщение: {$entry['message']}\n\n";
}

echo "\n=== Конец диагностики ===\n";
