<?php
$config = require __DIR__ . '/api/config.php';
$db = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
$db->set_charset('utf8mb4');

// 1. Check how many records have old name
$result = $db->query("SELECT COUNT(*) as cnt FROM project_milestones WHERE milestone_name = 'Режимная наладка'");
$row = $result->fetch_assoc();
echo "project_milestones with 'Режимная наладка': {$row['cnt']}\n";

$result2 = $db->query("SELECT COUNT(*) as cnt FROM milestone_progress_estimates WHERE milestone_name = 'Режимная наладка'");
$row2 = $result2->fetch_assoc();
echo "milestone_progress_estimates with 'Режимная наладка': {$row2['cnt']}\n";

// 2. Rename in project_milestones
$db->query("UPDATE project_milestones SET milestone_name = 'ИИ на рабочей среде' WHERE milestone_name = 'Режимная наладка' AND work_type = 'ПНР'");
echo "\nUpdated project_milestones: " . $db->affected_rows . " rows\n";

// 3. Rename in milestone_progress_estimates
$db->query("UPDATE milestone_progress_estimates SET milestone_name = 'ИИ на рабочей среде' WHERE milestone_name = 'Режимная наладка' AND work_type = 'ПНР'");
echo "Updated milestone_progress_estimates: " . $db->affected_rows . " rows\n";

// 4. Verify
$result3 = $db->query("SELECT COUNT(*) as cnt FROM project_milestones WHERE milestone_name = 'Режимная наладка'");
$row3 = $result3->fetch_assoc();
echo "\nRemaining project_milestones with 'Режимная наладка': {$row3['cnt']}\n";

$result4 = $db->query("SELECT COUNT(*) as cnt FROM project_milestones WHERE milestone_name = 'ИИ на рабочей среде'");
$row4 = $result4->fetch_assoc();
echo "project_milestones with 'ИИ на рабочей среде': {$row4['cnt']}\n";

$db->close();
