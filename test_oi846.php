<?php
/**
 * Test project card OI846
 */

require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO(
    "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
    "WizardJIOCb",
    "7qQ#28051961",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$projectCode = 'ОИ846';

echo "=== Тест карточки проекта {$projectCode} ===\n\n";

// Test 1: Check if project exists
echo "1. Проверка существования проекта:\n";
try {
    $stmt = $pdo->prepare("SELECT project_code, object_name FROM project_cards WHERE project_code = ?");
    $stmt->execute([$projectCode]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($project) {
        echo "✅ Проект найден: {$project['object_name']}\n\n";
    } else {
        echo "❌ Проект не найден в базе!\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: SMR query
echo "2. Тест ШМР запроса:\n";
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
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ ШМР мобилизация: {$row['mobilization_smr']} дн.\n\n";
} catch (Exception $e) {
    echo "❌ ОШИБКА ШМР: " . $e->getMessage() . "\n\n";
}

// Test 3: PNR query
echo "3. Тест ПНР запроса:\n";
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
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ ПНР мобилизация: {$row['mobilization_pnr']} дн.\n\n";
} catch (Exception $e) {
    echo "❌ ОШИБКА ПНР: " . $e->getMessage() . "\n\n";
}

// Test 4: CMR query
echo "4. Тест СМР запроса:\n";
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
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ СМР мобилизация: {$row['mobilization_cmr']} дн.\n\n";
} catch (Exception $e) {
    echo "❌ ОШИБКА СМР: " . $e->getMessage() . "\n\n";
}

echo "=== Тест завершен ===\n";
