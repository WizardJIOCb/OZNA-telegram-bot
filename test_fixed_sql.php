<?php
/**
 * Test fixed SQL queries for mobilization counting
 */

require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO(
    "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
    "WizardJIOCb",
    "7qQ#28051961",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$projectCode = 'ОИ849';

echo "=== Тест исправленных SQL запросов ===\n\n";

// Test SMR query (11 parameters)
echo "1. Тест SQL для ШМР (мобилизация + проектная деятельность):\n";
try {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Дорога%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Инструктаж%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Подготовка к служебной поездке%')
                 OR ot.message LIKE CONCAT('%', ?, '%Сдача авансового отчета%')
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
    
    $params = [$projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode];
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ УСПЕШНО!\n";
    echo "   Мобилизация ШМР: {$row['mobilization_smr']} дн.\n";
    echo "   Проектная деятельность ШМР: {$row['project_activity_smr']} дн.\n\n";
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n\n";
}

// Test PNR query (11 parameters)
echo "2. Тест SQL для ПНР (мобилизация + проектная деятельность + ЗПИ):\n";
try {
    $stmt = $pdo->prepare("
        SELECT 
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
    
    $params = [$projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode];
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ УСПЕШНО!\n";
    echo "   Мобилизация ПНР: {$row['mobilization_pnr']} дн.\n";
    echo "   Проектная деятельность ПНР: {$row['project_activity_pnr']} дн.\n";
    echo "   ЗПИ ПНР: {$row['zpi_pnr']} дн.\n\n";
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n\n";
}

// Test CMR query (9 parameters)
echo "3. Тест SQL для СМР (мобилизация):\n";
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
        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ");
    
    $params = [$projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode];
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ УСПЕШНО!\n";
    echo "   Мобилизация СМР: {$row['mobilization_cmr']} дн.\n\n";
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n\n";
}

echo "=== Все тесты завершены ===\n";
