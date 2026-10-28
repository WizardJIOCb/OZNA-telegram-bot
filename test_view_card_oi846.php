<?php
/**
 * Simulate viewProjectCard for OI846 to find the error
 */

require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO(
    "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
    "WizardJIOCb",
    "7qQ#28051961",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$projectCode = 'ОИ846';

echo "=== Эмуляция viewProjectCard для {$projectCode} ===\n\n";

// Get project data
$stmt = $pdo->prepare("SELECT * FROM project_cards WHERE project_code = ?");
$stmt->execute([$projectCode]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    echo "❌ Проект не найден!\n";
    exit(1);
}

echo "✅ Проект загружен: {$project['project_name']}\n\n";

// Try to load SMR page
echo "1. Загрузка ШМР страницы:\n";
try {
    // Get SMR milestones
    $stmt = $pdo->prepare("
        SELECT milestone_name, planned_hours, is_not_applicable, unit
        FROM project_milestones
        WHERE project_code = ? AND work_type = 'ШМР'
    ");
    $stmt->execute([$projectCode]);
    $milestones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Вехи: " . count($milestones) . "\n";
    
    // Test additional work query (line ~10875)
    $stmt = $pdo->prepare("
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
    ");
    $stmt->execute([
        $projectCode, $projectCode, $projectCode, $projectCode,  // master, rk, warranty, downtime
        $projectCode, $projectCode, $projectCode, $projectCode,  // mobilization (4 params)
        $projectCode, $projectCode,  // project activity (2 params)
        $projectCode  // WHERE clause
    ]);
    $smrAdditional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ ШМР доп работы:\n";
    echo "   Мобилизация: {$smrAdditional['mobilization_smr']} дн.\n";
    echo "   Проектная деятельность: {$smrAdditional['project_activity_smr']} дн.\n\n";
} catch (Exception $e) {
    echo "❌ ОШИБКА ШМР: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n\n";
}

// Try to load PNR page
echo "2. Загрузка ПНР страницы:\n";
try {
    // Get PNR milestones
    $stmt = $pdo->prepare("
        SELECT milestone_name, planned_hours, is_not_applicable, unit
        FROM project_milestones
        WHERE project_code = ? AND work_type = 'ПНР'
    ");
    $stmt->execute([$projectCode]);
    $milestones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Вехи: " . count($milestones) . "\n";
    
    // Test additional work query (line ~13358)
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN ot.id END) as downtime_pnr,
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN ot.id END) as rk_comments_pnr,
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN ot.id END) as warranty_pnr,
            COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Дорога%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Инструктаж%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Подготовка к служебной поездке%')
                 OR ot.message LIKE CONCAT('%', ?, '%Сдача авансового отчета%'))
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN ot.id END) as mobilization_pnr,
            COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Разработка графика работ%')
                 OR ot.message LIKE CONCAT('%', ?, '%Разработка программы работ%'))
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN ot.id END) as project_activity_pnr,
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Участие в ЗПИ%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                 THEN ot.id END) as zpi_pnr
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ");
    $stmt->execute([
        $projectCode, $projectCode, $projectCode,  // downtime, rk, warranty
        $projectCode, $projectCode, $projectCode, $projectCode,  // mobilization (4 params)
        $projectCode, $projectCode,  // project activity (2 params)
        $projectCode,  // zpi
        $projectCode  // WHERE
    ]);
    $pnrAdditional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ ПНР доп работы:\n";
    echo "   Мобилизация: {$pnrAdditional['mobilization_pnr']} дн.\n";
    echo "   Проектная деятельность: {$pnrAdditional['project_activity_pnr']} дн.\n";
    echo "   ЗПИ: {$pnrAdditional['zpi_pnr']} дн.\n\n";
} catch (Exception $e) {
    echo "❌ ОШИБКА ПНР: " . $e->getMessage() . "\n\n";
}

// Try to load CMR page
echo "3. Загрузка СМР страницы:\n";
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
    $stmt->execute([
        $projectCode, $projectCode, $projectCode, $projectCode,  // mobilization (4 params)
        $projectCode,  // message filter in CASE
        $projectCode   // WHERE
    ]);
    $cmrAdditional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ СМР мобилизация: {$cmrAdditional['mobilization_cmr']} дн.\n\n";
} catch (Exception $e) {
    echo "❌ ОШИБКА СМР: " . $e->getMessage() . "\n\n";
}

echo "=== Тест завершен ===\n";
