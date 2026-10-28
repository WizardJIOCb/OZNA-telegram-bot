<?php
/**
 * Test all SQL queries after syntax fixes
 */

require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO(
    "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
    "WizardJIOCb",
    "7qQ#28051961",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$projectCode = 'ОИ849';

echo "=== Тест всех SQL запросов после исправления синтаксиса ===\n\n";

// Test 1: SMR (showProjectCardSMRPage - line ~10875)
echo "1. ШМР (showProjectCardSMRPage):\n";
try {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Дорога%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Инструктаж%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Подготовка к служебной поездке%')
                 OR ot.message LIKE CONCAT('%', ?, '%Сдача авансового отчета%'))
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as mobilization_smr
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
    ");
    $stmt->execute([$projectCode, $projectCode, $projectCode, $projectCode, $projectCode]);
    echo "✅ УСПЕШНО! Мобилизация ШМР: " . $stmt->fetchColumn() . " дн.\n\n";
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n\n";
}

// Test 2: CMR (line ~11532)
echo "2. СМР (CMR mobilization):\n";
try {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Дорога%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Инструктаж%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Подготовка к служебной поездке%')
                 OR ot.message LIKE CONCAT('%', ?, '%Сдача авансового отчета%'))
                 AND ot.message LIKE CONCAT('%', ?, '%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада'
                 THEN ot.id END) as mobilization_cmr
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
    ");
    $stmt->execute([$projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode]);
    echo "✅ УСПЕШНО! Мобилизация СМР: " . $stmt->fetchColumn() . " дн.\n\n";
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n\n";
}

// Test 3: PNR additional (line ~12486)
echo "3. ПНР (additional work facts):\n";
try {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Дорога%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Инструктаж%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Подготовка к служебной поездке%')
                 OR ot.message LIKE CONCAT('%', ?, '%Сдача авансового отчета%'))
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN ot.id END) as mobilization_pnr
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
    ");
    $stmt->execute([$projectCode, $projectCode, $projectCode, $projectCode, $projectCode]);
    echo "✅ УСПЕШНО! Мобилизация ПНР: " . $stmt->fetchColumn() . " дн.\n\n";
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n\n";
}

echo "=== Все тесты завершены ===\n";
