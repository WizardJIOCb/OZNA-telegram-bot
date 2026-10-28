<?php
$db = new mysqli('82.146.42.213','WizardJIOCb','7qQ#28051961','tg_bot');
$db->set_charset('utf8mb4');

$r = $db->query("SELECT project_code FROM project_cards WHERE cmr_forecast_enabled=1");
$codes = [];
while($row=$r->fetch_assoc()) $codes[] = "'" . $db->real_escape_string($row['project_code']) . "'";
$inList = implode(',', $codes);

$r = $db->query("SELECT project_code, stage_progress, current_efficiency, forecast_efficiency, forecast_remaining_days, forecast_completion, fact_hours_days, planned_hours_days FROM metrics_cache WHERE work_type='СМР' AND project_code IN ({$inList}) ORDER BY project_code");
echo "project_code | progress | eff | f_eff | f_rem | f_date | fact | plan\n";
echo str_repeat('-', 90) . "\n";
while($row=$r->fetch_assoc()) {
    echo sprintf("%-12s | %7s | %5s | %5s | %5s | %-12s | %5s | %5s\n",
        $row['project_code'],
        $row['stage_progress'] ?? '-',
        $row['current_efficiency'] ?? '-',
        $row['forecast_efficiency'] ?? '-',
        $row['forecast_remaining_days'] ?? '-',
        $row['forecast_completion'] ?? '-',
        $row['fact_hours_days'] ?? '-',
        $row['planned_hours_days'] ?? '-'
    );
}
