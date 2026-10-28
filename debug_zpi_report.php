<?php
/**
 * Debug why ZPI entries for 30.09.2025 are missing from report
 */

$pdo = new PDO(
    "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
    "WizardJIOCb",
    "7qQ#28051961",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$projectCode = 'ОИ849';
$targetDate = '30.09.2025';

echo "=== Диагностика записей ЗПИ за {$targetDate} для проекта {$projectCode} ===\n\n";

// Get ALL entries for this project on 30.09.2025 (by message date)
$stmt = $pdo->prepare("
    SELECT 
        ot.id, 
        ot.user_id,
        ot.message, 
        ot.dt as created_at,
        JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as fio,
        JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.functional_direction')) as fn
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE CONCAT('%', ?, '%')
    ORDER BY ot.dt DESC
");
$stmt->execute([$projectCode, $targetDate]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "1. ВСЕ записи содержащие '{$projectCode}' И '{$targetDate}':\n";
echo str_repeat('-', 100) . "\n";
echo "Найдено: " . count($entries) . " записей\n\n";

foreach ($entries as $entry) {
    $hasZpi = (strpos($entry['message'], 'Участие в ЗПИ') !== false) ? '✅ ЗПИ' : '❌';
    echo "ID={$entry['id']} | {$entry['fio']} | ФН: {$entry['fn']} | {$hasZpi}\n";
    echo "Создано: {$entry['created_at']}\n";
    echo "Сообщение:\n{$entry['message']}\n";
    echo str_repeat('-', 100) . "\n\n";
}

// Now check specifically for ZPI entries
echo "\n2. ТОЛЬКО записи ЗПИ за {$targetDate}:\n";
echo str_repeat('-', 100) . "\n";

$stmt = $pdo->prepare("
    SELECT 
        ot.id, 
        ot.user_id,
        ot.message, 
        ot.dt as created_at,
        JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as fio,
        JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.functional_direction')) as fn
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE '%Участие в ЗПИ%'
    AND ot.message LIKE CONCAT('%', ?, '%')
    ORDER BY ot.dt DESC
");
$stmt->execute([$projectCode, $targetDate]);
$zpiEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Найдено ЗПИ записей: " . count($zpiEntries) . "\n\n";

foreach ($zpiEntries as $entry) {
    echo "ID={$entry['id']} | {$entry['fio']} | ФН: {$entry['fn']}\n";
    echo "Создано: {$entry['created_at']}\n";
    
    // Check if date can be parsed
    if (preg_match('/✅\s*(\d{2}\.\d{2}\.\d{4}),\s*Проект/', $entry['message'], $matches)) {
        echo "Дата из сообщения: {$matches[1]}\n";
    } else {
        echo "⚠️ НЕ УДАЛОСЬ ИЗВЛЕЧЬ ДАТУ из сообщения!\n";
    }
    echo str_repeat('-', 100) . "\n\n";
}

// Check the raw database query used by report
echo "\n3. Проверка условий для выгрузки отчёта (getSimpleReportEntries):\n";
echo str_repeat('-', 100) . "\n";

// Check date range for report - entries created between expanded date range
$targetDateTime = DateTime::createFromFormat('d.m.Y', $targetDate);
$startDate = (clone $targetDateTime)->modify('-7 days')->format('Y-m-d H:i:s');
$endDate = (clone $targetDateTime)->modify('+7 days')->format('Y-m-d 23:59:59');

echo "Расширенный диапазон дат для запроса: {$startDate} - {$endDate}\n\n";

$stmt = $pdo->prepare("
    SELECT 
        ot.id, 
        ot.user_id,
        ot.dt,
        ot.message,
        JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as fio,
        JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.functional_direction')) as fn
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.dt >= ?
    AND ot.dt <= ?
    ORDER BY ot.dt
");
$stmt->execute([$startDate, $endDate]);
$rawEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Всего записей в БД за расширенный период: " . count($rawEntries) . "\n\n";

// Filter for our project and check date extraction
$filtered = [];
foreach ($rawEntries as $entry) {
    if (strpos($entry['message'], $projectCode) !== false && strpos($entry['message'], 'Участие в ЗПИ') !== false) {
        // Try to extract date
        $dateExtracted = 'НЕТ';
        if (preg_match('/✅\s*(\d{2}\.\d{2}\.\d{4}),\s*Проект/', $entry['message'], $matches)) {
            $dateExtracted = $matches[1];
        }
        
        $filtered[] = [
            'id' => $entry['id'],
            'fio' => $entry['fio'],
            'fn' => $entry['fn'],
            'created_at' => $entry['dt'],
            'date_in_message' => $dateExtracted,
            'message_preview' => mb_substr($entry['message'], 0, 100)
        ];
    }
}

echo "ЗПИ записи для {$projectCode} в расширенном периоде:\n";
foreach ($filtered as $f) {
    $matchesTarget = ($f['date_in_message'] === $targetDate) ? '✅' : '❌';
    echo "ID={$f['id']} | {$f['fio']} | ФН: {$f['fn']}\n";
    echo "Создано: {$f['created_at']} | Дата в сообщении: {$f['date_in_message']} {$matchesTarget}\n";
    echo str_repeat('-', 50) . "\n";
}

echo "\n=== Конец диагностики ===\n";
