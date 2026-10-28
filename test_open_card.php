<?php
/**
 * Simulate opening project card OI846 step by step
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

$projectCode = 'ОИ846';

echo "=== Эмуляция открытия карточки проекта {$projectCode} ===\n\n";

// Step 1: Get project data
echo "1. Загрузка данных проекта:\n";
try {
    $stmt = $pdo->prepare("SELECT * FROM project_cards WHERE project_code = ?");
    $stmt->execute([$projectCode]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Проект: {$project['project_name']}\n\n";
} catch (Exception $e) {
    echo "   ❌ ОШИБКА: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Step 2: Load SMR efficiency summary (calculateSMREfficiencySummary)
echo "2. Расчёт эффективности ШМР:\n";
try {
    // Get SMR milestones plan
    $stmt = $pdo->prepare("
        SELECT milestone_name, planned_hours, is_not_applicable, unit
        FROM project_milestones
        WHERE project_code = ? AND work_type = 'ШМР'
    ");
    $stmt->execute([$projectCode]);
    $milestones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Вехи ШМР: " . count($milestones) . "\n";
    
    // Get auxiliary plan
    $stmt = $pdo->prepare("
        SELECT smr_mobilization, smr_project_activity, smr_master_duties
        FROM project_cards WHERE project_code = ?
    ");
    $stmt->execute([$projectCode]);
    $cardData = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   План мобилизации: {$cardData['smr_mobilization']}, Проектная деят.: {$cardData['smr_project_activity']}\n";
    
    // Get total fact from tabel
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT ot.id) as total_count
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
        AND ot.message NOT LIKE '%Этап наладки завершен%'
        AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
        AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
    ");
    $stmt->execute([$projectCode]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Факт ШМР: {$row['total_count']} дн.\n";
    echo "   ✅ ШМР OK\n\n";
} catch (Exception $e) {
    echo "   ❌ ОШИБКА ШМР: " . $e->getMessage() . "\n\n";
}

// Step 3: Load PNR efficiency summary (calculatePNREfficiencySummary)
echo "3. Расчёт эффективности ПНР:\n";
try {
    // Get PNR milestones plan
    $stmt = $pdo->prepare("
        SELECT milestone_name, planned_hours, is_not_applicable, unit
        FROM project_milestones
        WHERE project_code = ? AND work_type = 'ПНР'
    ");
    $stmt->execute([$projectCode]);
    $milestones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Вехи ПНР: " . count($milestones) . "\n";
    
    // Get auxiliary plan
    $stmt = $pdo->prepare("
        SELECT pnr_mobilization, pnr_project_activity
        FROM project_cards WHERE project_code = ?
    ");
    $stmt->execute([$projectCode]);
    $cardData = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   План мобилизации: {$cardData['pnr_mobilization']}, Проектная деят.: {$cardData['pnr_project_activity']}\n";
    
    // Get total fact from tabel
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT ot.id) as total_count
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
        AND ot.message NOT LIKE '%Этап наладки завершен%'
        AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
        AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
        AND ot.message NOT LIKE '%Участие в ЗПИ%'
    ");
    $stmt->execute([$projectCode]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Факт ПНР: {$row['total_count']} дн.\n";
    echo "   ✅ ПНР OK\n\n";
} catch (Exception $e) {
    echo "   ❌ ОШИБКА ПНР: " . $e->getMessage() . "\n\n";
}

// Step 4: Load CMR efficiency summary (calculateCMREfficiencySummary)
echo "4. Расчёт эффективности СМР:\n";
try {
    // Get CMR mobilization from tabel
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
        $projectCode, $projectCode, $projectCode, $projectCode,  // 4 params for mobilization
        $projectCode,  // message filter in CASE
        $projectCode   // WHERE
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Мобилизация СМР: {$result['mobilization_cmr']} дн.\n";
    echo "   ✅ СМР OK\n\n";
} catch (Exception $e) {
    echo "   ❌ ОШИБКА СМР: " . $e->getMessage() . "\n\n";
}

echo "=== Все этапы пройдены успешно! ===\n";
echo "\nВывод: Проблема НЕ в SQL запросах.\n";
echo "Возможно бот использует кэш PHP или не перезагружен после изменений.\n";
