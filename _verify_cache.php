<?php
$db = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$db->set_charset('utf8mb4');

echo "=== Sample rows with auxiliary_data ===\n\n";
$r = $db->query("SELECT project_code, work_type, forecast_efficiency, aggregate_forecast_fact, auxiliary_data FROM metrics_cache WHERE auxiliary_data IS NOT NULL AND auxiliary_data != '[]' AND auxiliary_data != 'null' LIMIT 5");
while ($row = $r->fetch_assoc()) {
    echo $row['project_code'] . ' | ' . $row['work_type'] . "\n";
    echo "  forecast_eff=" . $row['forecast_efficiency'] . " | agg_forecast=" . $row['aggregate_forecast_fact'] . "\n";
    echo "  auxiliary=" . $row['auxiliary_data'] . "\n\n";
}

echo "=== Dual forecast comparison (ШМР/ПНР) ===\n\n";
$r = $db->query("SELECT project_code, work_type, forecast_efficiency, aggregate_forecast_fact, planned_hours_days, fact_hours_days FROM metrics_cache WHERE work_type IN ('ШМР','ПНР') AND forecast_efficiency IS NOT NULL LIMIT 5");
while ($row = $r->fetch_assoc()) {
    $aggEff = ($row['planned_hours_days'] > 0 && $row['aggregate_forecast_fact'] > 0)
        ? round($row['planned_hours_days'] / $row['aggregate_forecast_fact'] * 100, 1)
        : null;
    echo $row['project_code'] . ' | ' . $row['work_type'] . "\n";
    echo "  survey_eff=" . $row['forecast_efficiency'] . "% | agg_eff=" . $aggEff . "% | plan=" . $row['planned_hours_days'] . " | fact=" . $row['fact_hours_days'] . " | agg_forecast=" . $row['aggregate_forecast_fact'] . "\n\n";
}

$db->close();
