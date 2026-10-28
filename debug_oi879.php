<?php
$config = require __DIR__ . '/api/config.php';
$db = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
$db->set_charset('utf8mb4');

// Load services (same way MetricsController does)
$botRoot = __DIR__;
require_once $botRoot . '/CustomCommands/Services/DatabaseService.php';
require_once $botRoot . '/CustomCommands/Services/MilestoneProgressService.php';
require_once $botRoot . '/api/services/MetricsCacheService.php';

$dbService = new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
$milestoneService = new Longman\TelegramBot\Commands\SystemCommands\Services\MilestoneProgressService($dbService);
$cacheService = new OznaApi\Services\MetricsCacheService($db, $milestoneService);

$code = 'ОИ879';
echo "=== Testing getMilestonesPlanDays for $code ===\n";
foreach (['ШМР', 'ПНР', 'СМР'] as $wt) {
    $plan = $cacheService->getMilestonesPlanDays($code, $wt);
    echo "$wt: milestones_plan=$plan\n";
}

echo "\n=== Full recalculation for ШМР ===\n";
$result = $cacheService->calculateForProject($code, 'ШМР');
echo "planned_hours_days = " . ($result['planned_hours_days'] ?? 'NULL') . "\n";
echo "stage_progress = " . ($result['stage_progress'] ?? 'NULL') . "\n";

echo "\n=== Full recalculation for ПНР ===\n";
$result2 = $cacheService->calculateForProject($code, 'ПНР');
echo "planned_hours_days = " . ($result2['planned_hours_days'] ?? 'NULL') . "\n";
echo "stage_progress = " . ($result2['stage_progress'] ?? 'NULL') . "\n";

$db->close();
