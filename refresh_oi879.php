<?php
$config = require __DIR__ . '/api/config.php';
$db = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
$db->set_charset('utf8mb4');

$botRoot = __DIR__;
require_once $botRoot . '/CustomCommands/Services/DatabaseService.php';
require_once $botRoot . '/CustomCommands/Services/MilestoneProgressService.php';
require_once $botRoot . '/api/services/MetricsCacheService.php';

$dbService = new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
$milestoneService = new Longman\TelegramBot\Commands\SystemCommands\Services\MilestoneProgressService($dbService);
$cacheService = new OznaApi\Services\MetricsCacheService($db, $milestoneService);

$code = 'ОИ879';
echo "Refreshing cache for $code...\n";
$cacheService->refreshProject($code);
echo "Done.\n\n";

// Verify
$stmt = $db->prepare('SELECT work_type, planned_hours_days, stage_progress, current_efficiency FROM metrics_cache WHERE project_code = ?');
$stmt->bind_param('s', $code);
$stmt->execute();
$result = $stmt->get_result();
echo "=== Updated metrics_cache for $code ===\n";
while ($row = $result->fetch_assoc()) {
    echo $row['work_type'] . ' | plan_days=' . ($row['planned_hours_days'] ?? 'NULL') . ' | progress=' . ($row['stage_progress'] ?? 'NULL') . ' | eff=' . ($row['current_efficiency'] ?? 'NULL') . "\n";
}
$stmt->close();
$db->close();
