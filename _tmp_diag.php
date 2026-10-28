<?php
$db = new mysqli('82.146.42.213','WizardJIOCb','7qQ#28051961','tg_bot');
$db->set_charset('utf8mb4');

$r = $db->query("SELECT has_missing_data, is_active, COUNT(DISTINCT project_code) as cnt FROM metrics_cache GROUP BY has_missing_data, is_active ORDER BY is_active DESC, has_missing_data");
echo "Missing data breakdown:\n";
while($row=$r->fetch_assoc()) echo "  active=" . $row['is_active'] . " missing=" . $row['has_missing_data'] . ": " . $row['cnt'] . " projects\n";

echo "\nBy BN group:\n";
$r = $db->query("SELECT COALESCE(business_unit_name, '(null)') as bn, COUNT(DISTINCT project_code) as cnt FROM metrics_cache GROUP BY business_unit_name ORDER BY cnt DESC");
while($row=$r->fetch_assoc()) echo "  " . $row['bn'] . ": " . $row['cnt'] . "\n";

echo "\nProjects with BN+RP+Customer that are still missing=1:\n";
$r = $db->query("SELECT DISTINCT mc.project_code FROM metrics_cache mc WHERE mc.has_missing_data = 1 AND mc.project_code IN (SELECT pc.project_code FROM project_cards pc WHERE pc.business_unit_id IS NOT NULL AND pc.project_manager_id IS NOT NULL AND pc.customer_id IS NOT NULL)");
$cnt = 0;
while($row=$r->fetch_assoc()) { echo "  " . $row['project_code'] . "\n"; $cnt++; }
if ($cnt == 0) echo "  (none - correct!)\n";
