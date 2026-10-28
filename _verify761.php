<?php
$db = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$db->set_charset('utf8mb4');

echo "=== ОИ761 per work type ===\n\n";
$r = $db->query("SELECT work_type, planned_hours_days, fact_hours_days, stage_progress, current_efficiency, forecast_efficiency, aggregate_forecast_fact FROM metrics_cache WHERE project_code = 'ОИ761' ORDER BY work_type");
$totalPlan = 0;
$totalAgg = 0;
while ($row = $r->fetch_assoc()) {
    $plan = (float)$row['planned_hours_days'];
    $fact = (float)$row['fact_hours_days'];
    $agg = $row['aggregate_forecast_fact'] !== null ? (float)$row['aggregate_forecast_fact'] : null;
    $progress = $row['stage_progress'];
    echo "{$row['work_type']}: plan={$plan} fact={$fact} progress={$progress}% agg_forecast=" . ($agg ?? 'NULL') . "\n";
    $totalPlan += $plan;
    $totalAgg += ($agg ?? $fact);
}
echo "\nSummary: totalPlan={$totalPlan} totalAggForecast={$totalAgg}\n";
$eff = $totalAgg > 0 ? round($totalPlan / $totalAgg * 100, 1) : 0;
echo "forecastEff={$eff}%\n";
$db->close();
