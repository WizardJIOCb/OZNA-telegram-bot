<?php
// Quick test of card functionality

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

try {
    $databaseService = new DatabaseService();
    $conn = $databaseService->getConnection();
    
    $projectCode = 'ОИ321';
    
    echo "=== Testing card queries for {$projectCode} ===\n\n";
    
    // Test 1: Count query
    echo "1. Count query...\n";
    $stmt = $conn->prepare("
        SELECT COUNT(*) as entry_count
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ");
    $stmt->bind_param('s', $projectCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    echo "   Result: {$data['entry_count']} entries\n\n";
    
    // Test 2: SMR additional
    echo "2. SMR additional work query...\n";
    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%') THEN 1 ELSE 0 END) as downtime_smr,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы вне ВОР%') THEN 1 ELSE 0 END) as out_of_scope,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Досборка%') THEN 1 ELSE 0 END) as preassembly,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%') 
                 AND (ot.message LIKE '%📦%' OR ot.message LIKE '%Шеф-монтаж%' OR ot.message LIKE '%Прочее оборудование%') 
                 THEN 1 ELSE 0 END) as rk_comments_smr,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%') THEN 1 ELSE 0 END) as warranty_smr,
            SUM(CASE WHEN (ot.message LIKE '%🚗 Дорога%' OR ot.message LIKE '%📋 Инструктаж%' OR ot.message LIKE '%Подготовка%') THEN 1 ELSE 0 END) as mobilization_smr
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ");
    $stmt->bind_param('ssssss', $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $smr = $result->fetch_assoc();
    echo "   SMR Additional:\n";
    print_r($smr);
    echo "\n";
    
    // Test 3: PNR additional
    echo "3. PNR additional work query...\n";
    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%') THEN 1 ELSE 0 END) as downtime_pnr,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%') 
                 AND (ot.message LIKE '%🤖%' OR ot.message LIKE '%Раздел%' OR ot.message NOT LIKE '%📦%') 
                 THEN 1 ELSE 0 END) as rk_comments_pnr,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%') THEN 1 ELSE 0 END) as warranty_pnr,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Реализация ТР%') THEN 1 ELSE 0 END) as additional_work,
            SUM(CASE WHEN (ot.message LIKE '%🚗 Дорога%' OR ot.message LIKE '%📋 Инструктаж%' OR ot.message LIKE '%Подготовка%') THEN 1 ELSE 0 END) as mobilization_pnr
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ");
    $stmt->bind_param('ssssss', $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $pnr = $result->fetch_assoc();
    echo "   PNR Additional:\n";
    print_r($pnr);
    
    echo "\n✅ ALL TESTS PASSED!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
