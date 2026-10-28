<?php
/**
 * Debug parsing - why 3 entries are missing activity
 */

$pdo = new PDO(
    "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
    "WizardJIOCb",
    "7qQ#28051961",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$projectCode = 'ОИ849';

echo "=== Проверка парсинга записей ЗПИ за 30.09.2025 ===\n\n";

// Get all ZPI entries for 30.09.2025
$stmt = $pdo->prepare("
    SELECT 
        ot.id, 
        ot.message, 
        JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as fio
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE '%Участие в ЗПИ%'
    AND ot.message LIKE '%30.09.2025%'
    ORDER BY ot.id
");
$stmt->execute([$projectCode]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($entries as $entry) {
    echo "=== ID={$entry['id']} | {$entry['fio']} ===\n";
    
    // Show raw message with visible special chars
    $rawMsg = $entry['message'];
    echo "RAW MESSAGE (first 200 chars):\n";
    echo $rawMsg . "\n\n";
    
    // Test date extraction regex
    if (preg_match('/✅\s*(\d{2}\.\d{2}\.\d{4}),\s*Проект/', $rawMsg, $matches)) {
        echo "✅ Дата извлечена: {$matches[1]}\n";
    } else {
        echo "❌ Дата НЕ извлечена!\n";
        
        // Debug - show hex of first 50 chars
        echo "HEX первых 50 символов: " . bin2hex(mb_substr($rawMsg, 0, 50)) . "\n";
    }
    
    // Test activity extraction - look for pattern after "Добавлена запись в табель:"
    if (preg_match('/Добавлена запись в табель:\s*(.+)$/m', $rawMsg, $actMatches)) {
        echo "✅ Активность: " . trim($actMatches[1]) . "\n";
    } else {
        echo "❌ Активность НЕ извлечена!\n";
    }
    
    echo "\n" . str_repeat('-', 80) . "\n\n";
}

// Also check entries from 26.09.2025 mentioned in screenshot
echo "\n=== Проверка записей за 26.09.2025 (пустая активность на скриншоте) ===\n\n";

$stmt = $pdo->prepare("
    SELECT 
        ot.id, 
        ot.message, 
        JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as fio,
        JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.functional_direction')) as fn
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE '%26.09.2025%'
    ORDER BY ot.id
");
$stmt->execute([$projectCode]);
$entries26 = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Записей за 26.09.2025: " . count($entries26) . "\n\n";

foreach ($entries26 as $entry) {
    echo "ID={$entry['id']} | {$entry['fio']} | ФН: {$entry['fn']}\n";
    echo "Сообщение:\n{$entry['message']}\n";
    echo str_repeat('-', 80) . "\n\n";
}
