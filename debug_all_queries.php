<?php
/**
 * Debug all queries that could fail in project card
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO(
    "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
    "WizardJIOCb",
    "7qQ#28051961",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Test multiple projects
$testProjects = ['ОИ846', 'ОИ849', 'ОИ765'];

foreach ($testProjects as $projectCode) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "ТЕСТ ПРОЕКТА: {$projectCode}\n";
    echo str_repeat('=', 60) . "\n\n";
    
    // Check if project exists
    $stmt = $pdo->prepare("SELECT project_code, project_name FROM project_cards WHERE project_code = ?");
    $stmt->execute([$projectCode]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        echo "⚠️ Проект не найден, пропускаем...\n";
        continue;
    }
    
    echo "✅ Проект: {$project['project_name']}\n\n";
    
    // Test 1: SMR milestones
    echo "1. Вехи ШМР: ";
    try {
        $stmt = $pdo->prepare("SELECT milestone_name, planned_hours, unit FROM project_milestones WHERE project_code = ? AND work_type = 'ШМР'");
        $stmt->execute([$projectCode]);
        $count = $stmt->rowCount();
        echo "{$count} вех\n";
    } catch (Exception $e) {
        echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    }
    
    // Test 2: PNR milestones
    echo "2. Вехи ПНР: ";
    try {
        $stmt = $pdo->prepare("SELECT milestone_name, planned_hours, unit FROM project_milestones WHERE project_code = ? AND work_type = 'ПНР'");
        $stmt->execute([$projectCode]);
        $count = $stmt->rowCount();
        echo "{$count} вех\n";
    } catch (Exception $e) {
        echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    }
    
    // Test 3: SMR additional work query (EXACT from code line ~10875)
    echo "3. ШМР доп. работы (11 параметров): ";
    try {
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
            $projectCode, $projectCode, $projectCode, $projectCode,
            $projectCode, $projectCode, $projectCode, $projectCode,
            $projectCode, $projectCode, $projectCode
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Моб: {$result['mobilization_smr']}, Проект: {$result['project_activity_smr']}\n";
    } catch (Exception $e) {
        echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    }
    
    // Test 4: PNR additional work query (EXACT from code line ~13358)
    echo "4. ПНР доп. работы (11 параметров): ";
    try {
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
            $projectCode, $projectCode, $projectCode,
            $projectCode, $projectCode, $projectCode, $projectCode,
            $projectCode, $projectCode, $projectCode, $projectCode
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Моб: {$result['mobilization_pnr']}, Проект: {$result['project_activity_pnr']}, ЗПИ: {$result['zpi_pnr']}\n";
    } catch (Exception $e) {
        echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    }
    
    // Test 5: CMR mobilization query
    echo "5. СМР мобилизация (7 параметров): ";
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
            $projectCode, $projectCode, $projectCode, $projectCode,
            $projectCode, $projectCode
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Моб: {$result['mobilization_cmr']}\n";
    } catch (Exception $e) {
        echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "ТЕСТ ЗАВЕРШЕН\n";
echo str_repeat('=', 60) . "\n";
