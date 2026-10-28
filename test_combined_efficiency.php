<?php
/**
 * Diagnostic script to debug combined efficiency calculation for a project
 */

$conn = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$conn->set_charset('utf8mb4');

$projectCode = 'ОИ879';

echo "=== ДИАГНОСТИКА СВОДНОЙ ЭФФЕКТИВНОСТИ ДЛЯ {$projectCode} ===\n\n";

// Все записи project_milestones для проекта (без фильтра work_type)
echo "========== ВСЕ ЗАПИСИ project_milestones ==========\n";
$stmt = $conn->prepare("
    SELECT milestone_name, planned_hours, is_not_applicable, unit, work_type
    FROM project_milestones
    WHERE project_code = ?
    ORDER BY work_type, milestone_name
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();
$totalRows = 0;
while ($row = $result->fetch_assoc()) {
    $totalRows++;
    $plan = $row['planned_hours'] ?? 0;
    $wt = $row['work_type'] ?? '(NULL)';
    $na = $row['is_not_applicable'] ? ' (N/A)' : '';
    echo "  [{$wt}] {$row['milestone_name']}: {$plan} {$row['unit']}{$na}\n";
}
if ($totalRows === 0) echo "  (нет записей)\n";
echo "  ВСЕГО записей: {$totalRows}\n\n";

// Все записи project_cards для проекта
echo "========== project_cards ==========\n";
$stmt = $conn->prepare("SELECT * FROM project_cards WHERE project_code = ?");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();
$cardData = $result->fetch_assoc();
if ($cardData) {
    foreach ($cardData as $key => $value) {
        if ($value !== null && $value !== '' && $value !== '0' && $value !== 0) {
            echo "  {$key}: {$value}\n";
        }
    }
} else {
    echo "  (нет записи в project_cards)\n";
}
echo "\n";

// Что показывается в Инфо
echo "========== СТРАНИЦЫ ЭТАПОВ (что показывает бот) ==========\n";

// ШМР факт
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT ot.id) as cnt
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    AND ot.message NOT LIKE '%Этап наладки завершен%'
    AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
    AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
echo "  ШМР факт (с исключениями): {$r['cnt']} дн.\n";

// СМР факт
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT ot.id) as cnt
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада'
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    AND ot.message NOT LIKE '%Этап наладки завершен%'
    AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
    AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
echo "  СМР факт (с исключениями): {$r['cnt']} дн.\n";

// ПНР факт
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT ot.id) as cnt
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'ПНР'
     OR JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    AND ot.message NOT LIKE '%Этап наладки завершен%'
    AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
    AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
echo "  ПНР факт (с исключениями): {$r['cnt']} дн.\n\n";

// Все functional_direction для проекта
echo "========== functional_direction пользователей на проекте ==========\n";
$stmt = $conn->prepare("
    SELECT JSON_EXTRACT(od.data, '$.functional_direction') as fd, COUNT(DISTINCT ot.id) as cnt
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    GROUP BY fd
    ORDER BY cnt DESC
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    echo "  {$row['fd']}: {$row['cnt']} записей\n";
}

$conn->close();
