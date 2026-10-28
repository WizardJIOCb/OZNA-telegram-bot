<?php
/**
 * Check which exact query fails in production
 * Enable error logging to file
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_oi846_error.log');

require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO(
    "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
    "WizardJIOCb",
    "7qQ#28051961",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$projectCode = 'ОИ846';

try {
    echo "Testing all queries for project {$projectCode}...\n\n";
    
    // Test the EXACT query from line 10875 (showProjectCardSMRPage)
    echo "Testing SMR additional work query (line ~10875):\n";
    $sql = "
        SELECT 
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Исполнение обязанностей мастера%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as master_duties_smr,
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as rk_comments_smr,
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as warranty_smr,
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as downtime_smr,
            COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Дорога%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Инструктаж%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Подготовка к служебной поездке%')
                 OR ot.message LIKE CONCAT('%', ?, '%Сдача авансового отчета%'))
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as mobilization_smr,
            COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Разработка графика работ%')
                 OR ot.message LIKE CONCAT('%', ?, '%Разработка программы работ%'))
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as project_activity_smr
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ";
    
    $stmt = $pdo->prepare($sql);
    $params = [
        $projectCode, $projectCode, $projectCode, $projectCode,  // master, rk, warranty, downtime
        $projectCode, $projectCode, $projectCode, $projectCode,  // mobilization
        $projectCode, $projectCode,  // project activity
        $projectCode  // WHERE
    ];
    
    echo "   SQL prepared OK\n";
    echo "   Executing with " . count($params) . " parameters...\n";
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ SUCCESS! Mobilization: {$result['mobilization_smr']}, Project Activity: {$result['project_activity_smr']}\n\n";
    
} catch (Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    echo "   SQL State: " . $e->getCode() . "\n\n";
    error_log("ERROR in SMR query: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
}

echo "Done. Check debug_oi846_error.log for details.\n";
