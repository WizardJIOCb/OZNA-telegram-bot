<?php
// Test both SMR and PNR queries

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$projectCode = 'ОИ321';

echo "=== Testing queries ===\n\n";

// Count placeholders in SMR query
$smrQuery = "
    SELECT 
        SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%') THEN 1 ELSE 0 END) as downtime_smr,
        SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы вне ВОР%') THEN 1 ELSE 0 END) as out_of_scope,
        SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Досборка%') THEN 1 ELSE 0 END) as preassembly,
        SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
             AND (JSON_EXTRACT(od.data, '\$.functional_direction') = 'Шеф-монтаж' OR JSON_EXTRACT(od.data, '\$.functional_direction') = 'Мобильная бригада')
             THEN 1 ELSE 0 END) as rk_comments_smr,
        SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%') THEN 1 ELSE 0 END) as warranty_smr,
        SUM(CASE WHEN (ot.message LIKE '%🚗 Дорога%' OR ot.message LIKE '%📋 Инструктаж%' OR ot.message LIKE '%Подготовка%')
             AND (JSON_EXTRACT(od.data, '\$.functional_direction') = 'Шеф-монтаж' OR JSON_EXTRACT(od.data, '\$.functional_direction') = 'Мобильная бригада')
             THEN 1 ELSE 0 END) as mobilization_smr,
        SUM(CASE WHEN (ot.message LIKE '%💰 Сдача авансового отчета%' 
             OR ot.message LIKE '%📋 Разработка графика работ%'
             OR ot.message LIKE '%📋 Разработка программы работ%'
             OR ot.message LIKE '%Подготовка к служебной поездке%')
             AND (JSON_EXTRACT(od.data, '\$.functional_direction') = 'Шеф-монтаж' OR JSON_EXTRACT(od.data, '\$.functional_direction') = 'Мобильная бригада')
             THEN 1 ELSE 0 END) as project_activity_smr
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '\$.functional_direction') != 'Тестировщик'
";

$pnrQuery = "
    SELECT 
        SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%') THEN 1 ELSE 0 END) as downtime_pnr,
        SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
             AND JSON_EXTRACT(od.data, '\$.functional_direction') = 'Пусконаладка'
             THEN 1 ELSE 0 END) as rk_comments_pnr,
        SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%') THEN 1 ELSE 0 END) as warranty_pnr,
        SUM(CASE WHEN ot.message LIKE CONCAT('%', ?, '%Реализация ТР%') THEN 1 ELSE 0 END) as additional_work,
        SUM(CASE WHEN (ot.message LIKE '%🚗 Дорога%' OR ot.message LIKE '%📋 Инструктаж%' OR ot.message LIKE '%Подготовка%')
             AND JSON_EXTRACT(od.data, '\$.functional_direction') = 'Пусконаладка'
             THEN 1 ELSE 0 END) as mobilization_pnr,
        SUM(CASE WHEN ot.message LIKE '%Дополнительные работы%'
             AND ot.message NOT LIKE '%Работы по графику%'
             AND JSON_EXTRACT(od.data, '\$.functional_direction') = 'Пусконаладка'
             THEN 1 ELSE 0 END) as pnr_stage_additional_work,
        SUM(CASE WHEN (ot.message LIKE '%💰 Сдача авансового отчета%' 
             OR ot.message LIKE '%📋 Разработка графика работ%'
             OR ot.message LIKE '%📋 Разработка программы работ%'
             OR ot.message LIKE '%Подготовка к служебной поездке%')
             AND JSON_EXTRACT(od.data, '\$.functional_direction') = 'Пусконаладка'
             THEN 1 ELSE 0 END) as project_activity_pnr
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '\$.functional_direction') != 'Тестировщик'
";

$smrPlaceholders = substr_count($smrQuery, '?');
$pnrPlaceholders = substr_count($pnrQuery, '?');

echo "SMR query placeholders: {$smrPlaceholders}\n";
echo "PNR query placeholders: {$pnrPlaceholders}\n\n";

echo "Testing SMR query (expecting 6 params):\n";
try {
    $stmt = $conn->prepare($smrQuery);
    $stmt->bind_param('ssssss', $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    echo "✅ SMR OK\n";
    print_r($data);
} catch (Exception $e) {
    echo "❌ SMR ERROR: " . $e->getMessage() . "\n";
}

echo "\nTesting PNR query (expecting 5 params):\n";
try {
    $stmt = $conn->prepare($pnrQuery);
    $stmt->bind_param('sssss', $projectCode, $projectCode, $projectCode, $projectCode, $projectCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    echo "✅ PNR OK\n";
    print_r($data);
} catch (Exception $e) {
    echo "❌ PNR ERROR: " . $e->getMessage() . "\n";
}
