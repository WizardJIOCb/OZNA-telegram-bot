<?php
$db = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$db->set_charset('utf8mb4');

$r = $db->query("SELECT project_code, work_type, auxiliary_data, planned_hours_days, fact_hours_days FROM metrics_cache WHERE work_type = 'ШМР' AND auxiliary_data IS NOT NULL AND auxiliary_data != '[]' AND auxiliary_data != 'null' LIMIT 5");
while ($row = $r->fetch_assoc()) {
    echo $row['project_code'] . " | ШМР | plan=" . $row['planned_hours_days'] . " fact=" . $row['fact_hours_days'] . "\n";
    $aux = json_decode($row['auxiliary_data'], true);
    foreach ($aux as $key => $item) {
        echo "    {$key}: plan={$item['plan']} fact={$item['fact']}\n";
    }
    echo "\n";
}
$db->close();
