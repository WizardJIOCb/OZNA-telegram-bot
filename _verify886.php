<?php
$db = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$db->set_charset('utf8mb4');
$r = $db->query("SELECT auxiliary_data, fact_hours_days, milestone_details FROM metrics_cache WHERE project_code = 'ОИ886' AND work_type = 'ПНР'");
$row = $r->fetch_assoc();
echo "ОИ886 ПНР: fact=" . $row['fact_hours_days'] . "\n\n";
$milestones = json_decode($row['milestone_details'], true);
$total = 0;
echo "Milestones:\n";
foreach ($milestones as $m) {
    echo "  {$m['name']}: fact={$m['fact_days']}\n";
    $total += $m['fact_days'];
}
echo "Milestone total: {$total}\n\n";
$aux = json_decode($row['auxiliary_data'], true);
echo "Auxiliary:\n";
foreach ($aux as $k => $v) echo "  {$k}: plan={$v['plan']} fact={$v['fact']}\n";
echo "\nExpected from TG:\n";
echo "  milestones: Электрооборудование=25, Автоматизация=42, Метрология=1, Комплексное опробование=19 (total=87)\n";
echo "  mob=51, projAct=27, downtime=14, unaccounted=98\n";
$db->close();
