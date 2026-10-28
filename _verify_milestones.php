<?php
$db = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$db->set_charset('utf8mb4');

echo "=== ОИ886 ПНР milestones ===\n";
$r = $db->query("SELECT milestone_details FROM metrics_cache WHERE project_code = 'ОИ886' AND work_type = 'ПНР'");
$row = $r->fetch_assoc();
$milestones = json_decode($row['milestone_details'], true);
if (empty($milestones)) {
    echo "  EMPTY - no milestones!\n";
} else {
    foreach ($milestones as $m) {
        echo "  {$m['name']}: plan={$m['plan_days']} fact={$m['fact_days']} progress={$m['progress']}%\n";
    }
}

echo "\nExpected from TG:\n";
echo "  Электрооборудование: plan=0 fact=25\n";
echo "  Автоматизация: plan=0 fact=42\n";
echo "  Метрология: plan=0 fact=1\n";
echo "  Комплексное опробование: plan=0 fact=19\n";

echo "\n=== ОИ846 ПНР milestones (has plans) ===\n";
$r = $db->query("SELECT milestone_details FROM metrics_cache WHERE project_code = 'ОИ846' AND work_type = 'ПНР'");
$row = $r->fetch_assoc();
$milestones = json_decode($row['milestone_details'], true);
foreach ($milestones as $m) {
    echo "  {$m['name']}: plan={$m['plan_days']} fact={$m['fact_days']} progress={$m['progress']}%\n";
}

$db->close();
