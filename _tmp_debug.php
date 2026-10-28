<?php
$db = new mysqli('82.146.42.213','WizardJIOCb','7qQ#28051961','tg_bot');
$db->set_charset('utf8mb4');

$r = $db->query("SELECT project_code FROM project_cards WHERE cmr_forecast_enabled=1");
$codes = [];
while($row=$r->fetch_assoc()) $codes[] = $row['project_code'];
echo "CMR forecast enabled: " . implode(', ', $codes) . "\n\n";

foreach($codes as $code) {
  echo "=== {$code} ===\n";
  
  // СМР milestones
  $r = $db->query("SELECT milestone_name, planned_hours, is_not_applicable, unit FROM project_milestones WHERE project_code='" . $db->real_escape_string($code) . "' AND work_type='СМР' ORDER BY milestone_name");
  echo "СМР milestones: " . $r->num_rows . "\n";
  while($row=$r->fetch_assoc()) echo "  {$row['milestone_name']}: plan={$row['planned_hours']} na={$row['is_not_applicable']} {$row['unit']}\n";
  
  // ШМР milestones (used for forecast)
  $r = $db->query("SELECT milestone_name, planned_hours, is_not_applicable, unit FROM project_milestones WHERE project_code='" . $db->real_escape_string($code) . "' AND work_type='ШМР' ORDER BY milestone_name");
  echo "ШМР milestones: " . $r->num_rows . "\n";
  while($row=$r->fetch_assoc()) echo "  {$row['milestone_name']}: plan={$row['planned_hours']} na={$row['is_not_applicable']} {$row['unit']}\n";
  
  // ШМР estimates
  $r = $db->query("SELECT milestone_name, estimated_percent FROM milestone_progress_estimates WHERE project_code='" . $db->real_escape_string($code) . "' AND work_type='ШМР' ORDER BY created_at DESC LIMIT 10");
  echo "ШМР estimates: " . $r->num_rows . "\n";
  while($row=$r->fetch_assoc()) echo "  {$row['milestone_name']}: {$row['estimated_percent']}%\n";
  
  // Cache
  $r = $db->query("SELECT stage_progress, current_efficiency, forecast_efficiency, forecast_remaining_days, forecast_completion, fact_hours_days, planned_hours_days FROM metrics_cache WHERE project_code='" . $db->real_escape_string($code) . "' AND work_type='СМР'");
  $row = $r->fetch_assoc();
  echo "Cache: progress={$row['stage_progress']}, eff={$row['current_efficiency']}, f_eff={$row['forecast_efficiency']}, f_rem={$row['forecast_remaining_days']}, f_date={$row['forecast_completion']}, fact={$row['fact_hours_days']}, plan={$row['planned_hours_days']}\n\n";
}
