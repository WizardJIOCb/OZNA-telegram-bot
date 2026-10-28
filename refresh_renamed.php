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

// Find all projects with ИИ на рабочей среде milestone (these were renamed from Режимная наладка)
$result = $db->query("SELECT DISTINCT project_code FROM project_milestones WHERE milestone_name = 'ИИ на рабочей среде'");
$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row['project_code'];
}
echo "Projects with 'ИИ на рабочей среде' milestone: " . count($projects) . "\n";
echo implode(', ', $projects) . "\n\n";

foreach ($projects as $code) {
    echo "Refreshing $code... ";
    $cacheService->refreshProject($code);
    echo "OK\n";
}

// Verify ОИ879
echo "\n=== Verify ОИ879 ===\n";
$stmt = $db->prepare('SELECT work_type, planned_hours_days, stage_progress, current_efficiency FROM metrics_cache WHERE project_code = ?');
$code = 'ОИ879';
$stmt->bind_param('s', $code);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    echo $row['work_type'] . ' | plan=' . ($row['planned_hours_days'] ?? 'NULL') . ' | progress=' . ($row['stage_progress'] ?? 'NULL') . ' | eff=' . ($row['current_efficiency'] ?? 'NULL') . "\n";
}
$stmt->close();
$db->close();
