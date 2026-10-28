<?php
/**
 * Debug ZPI counting - analyze dates vs entries
 */

$pdo = new PDO(
    "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
    "WizardJIOCb",
    "7qQ#28051961",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$projectCode = 'ОИ849';

echo "=== Анализ ЗПИ по датам для проекта {$projectCode} ===\n\n";

// Get all ZPI entries with parsed date from message
$stmt = $pdo->prepare("
    SELECT 
        ot.id, 
        ot.message, 
        ot.dt as created_at,
        JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as fio
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE '%Участие в ЗПИ%'
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
    ORDER BY ot.dt DESC
");
$stmt->execute([$projectCode]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Всего записей ЗПИ в базе: " . count($entries) . "\n\n";

// Extract work date from message (the date in the message, not creation date)
$workDates = [];
$byPerson = [];

foreach ($entries as $entry) {
    // Extract date from message like "✅06.10.2025, Проект"
    if (preg_match('/✅\s*(\d{2}\.\d{2}\.\d{4})/', $entry['message'], $matches)) {
        $workDate = $matches[1];
    } else {
        $workDate = 'НЕ РАСПОЗНАНА';
    }
    
    $workDates[] = $workDate;
    
    $fio = $entry['fio'];
    if (!isset($byPerson[$fio])) {
        $byPerson[$fio] = [];
    }
    $byPerson[$fio][] = $workDate;
    
    echo "ID={$entry['id']} | {$fio} | Дата работы: {$workDate}\n";
}

echo "\n" . str_repeat('-', 60) . "\n";
echo "Уникальные даты работы: " . count(array_unique($workDates)) . "\n";
echo "Уникальные даты: " . implode(', ', array_unique($workDates)) . "\n\n";

echo str_repeat('-', 60) . "\n";
echo "ПО СОТРУДНИКАМ (человеко-дни ЗПИ):\n";
echo str_repeat('-', 60) . "\n";

$totalPersonDays = 0;
foreach ($byPerson as $fio => $dates) {
    $uniqueDates = array_unique($dates);
    $personDays = count($uniqueDates);
    $totalPersonDays += $personDays;
    echo "{$fio}:\n";
    echo "  Записей: " . count($dates) . ", Уникальных дат: {$personDays}\n";
    echo "  Даты: " . implode(', ', $uniqueDates) . "\n\n";
}

echo str_repeat('=', 60) . "\n";
echo "ИТОГО записей: " . count($entries) . "\n";
echo "ИТОГО человеко-дней (уникальные даты по сотрудникам): {$totalPersonDays}\n";
echo str_repeat('=', 60) . "\n";
