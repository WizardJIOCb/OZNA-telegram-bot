<?php
$db = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$db->set_charset('utf8mb4');
$r = $db->query("SELECT auxiliary_data, fact_hours_days, forecast_efficiency, milestone_details FROM metrics_cache WHERE project_code = 'ОИ846' AND work_type = 'ПНР'");
$row = $r->fetch_assoc();
echo "fact=" . $row['fact_hours_days'] . " forecast_eff=" . $row['forecast_efficiency'] . "\n";
$aux = json_decode($row['auxiliary_data'], true);
echo "Auxiliary:\n";
foreach ($aux as $k => $v) echo "  {$k}: plan={$v['plan']} fact={$v['fact']}\n";
$milestones = json_decode($row['milestone_details'], true);
echo "\nMilestones:\n";
$total = 0;
foreach ($milestones as $m) {
    echo "  {$m['name']}: fact={$m['fact_days']}\n";
    $total += $m['fact_days'];
}
echo "Milestone total: {$total}\n";
echo "\nExpected from TG:\n";
echo "  milestones=152, mob=20, projAct=6, downtime=2, rk=7, unaccounted=23\n";
$db->close();
