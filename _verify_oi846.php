<?php
$db = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$db->set_charset('utf8mb4');

$r = $db->query("SELECT project_code, work_type, auxiliary_data, planned_hours_days, fact_hours_days, forecast_efficiency FROM metrics_cache WHERE project_code = 'ОИ846' AND work_type = 'ПНР'");
$row = $r->fetch_assoc();
echo "ОИ846 ПНР:\n";
echo "  plan=" . $row['planned_hours_days'] . " fact=" . $row['fact_hours_days'] . " forecast_eff=" . $row['forecast_efficiency'] . "%\n";
echo "  auxiliary_data:\n";
$aux = json_decode($row['auxiliary_data'], true);
foreach ($aux as $key => $item) {
    echo "    {$key}: plan={$item['plan']} fact={$item['fact']}\n";
}

echo "\n--- TG expected ---\n";
echo "  mob: plan=25 fact=20\n";
echo "  proj_act: plan=5 fact=6\n";
echo "  downtime: fact=2\n";
echo "  rk_comments: fact=7\n";
echo "  unaccounted: fact=18\n";

$db->close();
