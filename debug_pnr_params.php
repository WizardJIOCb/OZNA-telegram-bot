<?php
require_once __DIR__ . '/vendor/autoload.php';
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();
$projectCode = 'ОИ321';

echo "=== Testing current queries ===\n\n";

// Test PNR query
echo "Testing PNR query:\n";
try {
    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%') THEN 1 ELSE 0 END) as downtime_pnr,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN 1 ELSE 0 END) as rk_comments_pnr,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%') THEN 1 ELSE 0 END) as warranty_pnr,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Реализация ТР%') THEN 1 ELSE 0 END) as additional_work,
            SUM(CASE WHEN (ot.message LIKE '%🚗 Дорога%' OR ot.message LIKE '%📋 Инструктаж%' OR (ot.message LIKE '%Подготовка%' AND ot.message NOT LIKE '%Подготовка к служебной поездке%'))
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN 1 ELSE 0 END) as mobilization_pnr,
            SUM(CASE WHEN ot.message LIKE '%Дополнительные работы%'
                 AND ot.message NOT LIKE '%Работы по графику%'
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN 1 ELSE 0 END) as pnr_stage_additional_work,
            SUM(CASE WHEN (ot.message LIKE '%💰 Сдача авансового отчета%' 
                 OR ot.message LIKE '%📋 Разработка графика работ%'
                 OR ot.message LIKE '%📋 Разработка программы работ%'
                 OR ot.message LIKE '%Подготовка к служебной поездке%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN 1 ELSE 0 END) as project_activity_pnr
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ");
    
    $placeholders = substr_count($stmt->sqlstate, '?');
    echo "  Placeholders in query: " . substr_count("
        SELECT 
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%') THEN 1 ELSE 0 END) as downtime_pnr,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN 1 ELSE 0 END) as rk_comments_pnr,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%') THEN 1 ELSE 0 END) as warranty_pnr,
            SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Реализация ТР%') THEN 1 ELSE 0 END) as additional_work,
            SUM(CASE WHEN (ot.message LIKE '%🚗 Дорога%' OR ot.message LIKE '%📋 Инструктаж%' OR (ot.message LIKE '%Подготовка%' AND ot.message NOT LIKE '%Подготовка к служебной поездке%'))
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN 1 ELSE 0 END) as mobilization_pnr,
            SUM(CASE WHEN ot.message LIKE '%Дополнительные работы%'
                 AND ot.message NOT LIKE '%Работы по графику%'
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN 1 ELSE 0 END) as pnr_stage_additional_work,
            SUM(CASE WHEN (ot.message LIKE '%💰 Сдача авансового отчета%' 
                 OR ot.message LIKE '%📋 Разработка графика работ%'
                 OR ot.message LIKE '%📋 Разработка программы работ%'
                 OR ot.message LIKE '%Подготовка к служебной поездке%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN 1 ELSE 0 END) as project_activity_pnr
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ", '?') . "\n";
    
    echo "  Trying with 5 params...\n";
    $stmt->bind_param('sssss', $projectCode, $projectCode, $projectCode, $projectCode, $projectCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    echo "  ✅ PNR OK\n";
    print_r($data);
} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
    echo "  Trying with 6 params...\n";
    try {
        $stmt = $conn->prepare("
            SELECT 
                SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%') THEN 1 ELSE 0 END) as downtime_pnr,
                SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
                     AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                     THEN 1 ELSE 0 END) as rk_comments_pnr,
                SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%') THEN 1 ELSE 0 END) as warranty_pnr,
                SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Реализация ТР%') THEN 1 ELSE 0 END) as additional_work,
                SUM(CASE WHEN (ot.message LIKE '%🚗 Дорога%' OR ot.message LIKE '%📋 Инструктаж%' OR (ot.message LIKE '%Подготовка%' AND ot.message NOT LIKE '%Подготовка к служебной поездке%'))
                     AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                     THEN 1 ELSE 0 END) as mobilization_pnr,
                SUM(CASE WHEN ot.message LIKE '%Дополнительные работы%'
                     AND ot.message NOT LIKE '%Работы по графику%'
                     AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                     THEN 1 ELSE 0 END) as pnr_stage_additional_work,
                SUM(CASE WHEN (ot.message LIKE '%💰 Сдача авансового отчета%' 
                     OR ot.message LIKE '%📋 Разработка графика работ%'
                     OR ot.message LIKE '%📋 Разработка программы работ%'
                     OR ot.message LIKE '%Подготовка к служебной поездке%')
                     AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                     THEN 1 ELSE 0 END) as project_activity_pnr
            FROM ozna_tabel ot
            INNER JOIN ozna_data od ON ot.user_id = od.user_id
            WHERE ot.message LIKE CONCAT('%', ?, '%')
            AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
        ");
        $stmt->bind_param('ssssss', $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        echo "  ✅ With 6 params works!\n";
        print_r($data);
    } catch (Exception $e2) {
        echo "  ❌ Also failed with 6: " . $e2->getMessage() . "\n";
    }
}
