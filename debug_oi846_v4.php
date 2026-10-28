<?php
// Quick verification: per-work-type values for ОИ846 against TG bot's expected output
$config = require __DIR__ . '/api/config.php';
$db = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
$db->set_charset('utf8mb4');

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MilestoneProgressService.php';
require_once __DIR__ . '/api/services/MetricsCacheService.php';

$dbService = new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
$milestoneService = new Longman\TelegramBot\Commands\SystemCommands\Services\MilestoneProgressService($dbService);
$cacheService = new OznaApi\Services\MetricsCacheService($db, $milestoneService);

$code = 'ОИ846';

echo "=== Per-work-type metrics for Step 4 display ===\n\n";

foreach (['ШМР', 'ПНР', 'СМР'] as $wt) {
    $r = $cacheService->calculateForProject($code, $wt);
    echo "$wt:\n";
    echo "  Plan: {$r['planned_hours_days']} | Fact: {$r['fact_hours_days']}\n";
    echo "  Current eff: {$r['current_efficiency']}%\n";
    echo "  Forecast eff: {$r['forecast_efficiency']}%\n";
    echo "  Forecast remaining: {$r['forecast_remaining_days']} days\n";
    echo "  Forecast completion: {$r['forecast_completion']}\n";
    echo "  Progress: {$r['stage_progress']}%\n\n";
}

// Also test another project to make sure we didn't break anything
echo "=== Quick sanity check on other projects ===\n";
foreach (['ОИ834', 'ОИ879'] as $testCode) {
    echo "\n$testCode:\n";
    foreach (['ШМР', 'ПНР'] as $wt) {
        $r = $cacheService->calculateForProject($testCode, $wt);
        if ($r['planned_hours_days'] || $r['fact_hours_days']) {
            echo "  $wt: plan={$r['planned_hours_days']} fact={$r['fact_hours_days']} curr={$r['current_efficiency']}% fcst={$r['forecast_efficiency']}%\n";
        }
    }
}

$db->close();
