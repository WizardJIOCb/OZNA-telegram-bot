<?php
// Test card query to find the error

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

try {
    $databaseService = new DatabaseService();
    $conn = $databaseService->getConnection();
    
    $projectCode = 'ОИ321';
    
    echo "Testing SMR additional work query...\n";
    
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
    
    $stmt->bind_param('sssssss', $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $smrAdditional = $result->fetch_assoc();
    
    echo "SUCCESS!\n";
    print_r($smrAdditional);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
