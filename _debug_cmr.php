<?php
$db = new mysqli('localhost', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$db->set_charset('utf8mb4');

$projectCode = 'ОИ834';

$mapping = [
    'Электромонтажные работы' => ['Электрооборудование'],
    'Слесарные работы' => ['Технологическое оборудование', 'Отопление и вентиляция'],
    'Сварочные работы' => ['Технологическое оборудование'],
    'Работы по теплоизоляции' => ['Технологическое оборудование', 'Отопление и вентиляция'],
];

echo "=== ШМР milestone plans (Внутриблочный монтаж subs) ===\n";
$r = $db->query("SELECT milestone_name, planned_hours, unit, is_not_applicable FROM project_milestones WHERE project_code = '{$projectCode}' AND work_type = 'ШМР'");
while ($row = $r->fetch_assoc()) {
    echo "  {$row['milestone_name']}: plan={$row['planned_hours']} unit={$row['unit']} na={$row['is_not_applicable']}\n";
}

echo "\n=== milestone_progress_estimates for ШМР ===\n";
$r = $db->query("SELECT milestone_name, estimated_percent, created_at FROM milestone_progress_estimates WHERE project_code = '{$projectCode}' AND work_type = 'ШМР' ORDER BY milestone_name, created_at DESC");
while ($row = $r->fetch_assoc()) {
    echo "  {$row['milestone_name']}: {$row['estimated_percent']}% (at {$row['created_at']})\n";
}

echo "\n=== Confirmed ШМР milestones ===\n";
$milestones = ['Электрооборудование', 'Технологическое оборудование', 'Отопление и вентиляция'];
foreach ($milestones as $m) {
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM ozna_tabel ot LEFT JOIN ozna_data od ON ot.user_id = od.user_id WHERE ot.message LIKE CONCAT('%', ?, '%') AND ot.message LIKE '%Этап шеф-монтажа завершен%' AND ot.message LIKE CONCAT('%', ?, '%') AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж' OR ot.message LIKE '%[Конструктор]%')");
    $stmt->bind_param('ss', $projectCode, $m);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    echo "  {$m}: confirmed=" . ($row['cnt'] > 0 ? 'YES' : 'NO') . " (cnt={$row['cnt']})\n";
}

echo "\n=== Simulating calculateCMRForecastFromSMR per work type ===\n";
$totalProgress = 0;
$totalWeight = 0;
foreach ($mapping as $cmrType => $smrMilestones) {
    echo "\n--- {$cmrType} -> [" . implode(', ', $smrMilestones) . "] ---\n";
    $wpTotal = 0;
    $planTotal = 0;
    foreach ($smrMilestones as $smrM) {
        // Get plan
        $stmt = $db->prepare("SELECT planned_hours, unit, is_not_applicable FROM project_milestones WHERE project_code = ? AND work_type = 'ШМР' AND milestone_name = ?");
        $stmt->bind_param('ss', $projectCode, $smrM);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row || $row['is_not_applicable'] || $row['planned_hours'] <= 0) {
            echo "  {$smrM}: NO PLAN or N/A\n";
            continue;
        }
        $plan = $row['unit'] === 'hours' ? ceil($row['planned_hours'] / 8) : $row['planned_hours'];
        
        // Get estimate
        $stmt2 = $db->prepare("SELECT estimated_percent FROM milestone_progress_estimates WHERE project_code = ? AND milestone_name = ? AND work_type = 'ШМР' ORDER BY created_at DESC LIMIT 1");
        $stmt2->bind_param('ss', $projectCode, $smrM);
        $stmt2->execute();
        $estRow = $stmt2->get_result()->fetch_assoc();
        $progress = $estRow ? $estRow['estimated_percent'] : 0;
        
        echo "  {$smrM}: plan={$plan}d, progress={$progress}%\n";
        $wpTotal += $progress * $plan;
        $planTotal += $plan;
    }
    if ($planTotal > 0) {
        $wp = round($wpTotal / $planTotal, 1);
        echo "  => weighted progress: {$wp}%, totalPlan: {$planTotal}\n";
        $totalProgress += $wp * $planTotal;
        $totalWeight += $planTotal;
    } else {
        echo "  => NO DATA\n";
    }
}

if ($totalWeight > 0) {
    echo "\n=== OVERALL CMR PROGRESS ===\n";
    echo round($totalProgress / $totalWeight, 1) . "%\n";
    echo "totalWeight: {$totalWeight}\n";
}

$db->close();
