<?php
$config = require __DIR__ . '/api/config.php';
$db = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
$db->set_charset('utf8mb4');
$stmt = $db->prepare('SELECT work_type, planned_hours_days, stage_progress, current_efficiency FROM metrics_cache WHERE project_code = ?');
$code = 'ОИ879';
$stmt->bind_param('s', $code);
$stmt->execute();
$result = $stmt->get_result();
echo "=== ОИ879 metrics_cache ===\n";
while ($row = $result->fetch_assoc()) {
    echo $row['work_type'] . ' | plan=' . ($row['planned_hours_days'] ?? 'NULL') . ' | progress=' . ($row['stage_progress'] ?? 'NULL') . ' | eff=' . ($row['current_efficiency'] ?? 'NULL') . "\n";
}
$db->close();
