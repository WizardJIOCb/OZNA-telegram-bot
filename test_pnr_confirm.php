<?php
$conn = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$conn->set_charset('utf8mb4');

$projectCode = 'ОИ784';
$milestoneName = 'Охранная сигнализация';

echo "=== ДИАГНОСТИКА ПОДТВЕРЖДЕНИЯ ВЕХИ ===\n";
echo "Проект: {$projectCode}\n";
echo "Веха: {$milestoneName}\n\n";

// 1. Проверка записи "Этап наладки завершен"
echo "1. Записи 'Этап наладки завершен':\n";
$stmt = $conn->prepare("
    SELECT ot.id, ot.message, ot.dt, od.data
    FROM ozna_tabel ot
    LEFT JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND ot.message LIKE '%Этап наладки завершен%'
    AND ot.message LIKE CONCAT('%', ?, '%')
");
$stmt->bind_param('ss', $projectCode, $milestoneName);
$stmt->execute();
$result = $stmt->get_result();
$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    $fd = json_decode($row['data'], true)['functional_direction'] ?? 'N/A';
    echo "  [{$row['dt']}] FD={$fd}\n";
    echo "  Сообщение: " . substr($row['message'], 0, 200) . "...\n\n";
}
if ($count == 0) echo "  (нет записей)\n\n";

// 2. Проверка опроса прогресса
echo "2. Записи в milestone_progress_estimates:\n";
$stmt = $conn->prepare("
    SELECT milestone_name, estimated_percent, estimator_fio, estimated_at
    FROM milestone_progress_estimates
    WHERE project_code = ? AND work_type = 'ПНР' AND milestone_name = ?
");
$stmt->bind_param('ss', $projectCode, $milestoneName);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row) {
    echo "  Процент: {$row['estimated_percent']}%\n";
    echo "  Оценщик: {$row['estimator_fio']}\n";
    echo "  Дата: {$row['estimated_at']}\n";
} else {
    echo "  (нет записей)\n";
}

// 3. Все опросы ПНР для проекта
echo "\n3. Все опросы ПНР для {$projectCode}:\n";
$stmt = $conn->prepare("
    SELECT milestone_name, estimated_percent, estimator_fio, estimated_at
    FROM milestone_progress_estimates
    WHERE project_code = ? AND work_type = 'ПНР'
    ORDER BY milestone_name
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    echo "  - {$row['milestone_name']}: {$row['estimated_percent']}% ({$row['estimated_at']})\n";
}

$conn->close();
