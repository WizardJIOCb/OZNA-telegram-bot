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

$code = 'ОИ846';

echo "=== Testing fixed getMilestonesFactDays ===\n\n";

foreach (['ШМР', 'ПНР'] as $wt) {
    $milestoneFact = $cacheService->getMilestonesFactDays($code, $wt);
    echo "$wt milestoneFact = $milestoneFact\n";
}

echo "\n=== Full calculateForProject ===\n\n";

$totalPlan = 0; $totalFact = 0; $totalForecastFact = 0;

foreach (['ШМР', 'ПНР', 'СМР'] as $wt) {
    $result = $cacheService->calculateForProject($code, $wt);
    $plan = $result['planned_hours_days'] ?? 0;
    $fact = $result['fact_hours_days'] ?? 0;
    $currEff = $result['current_efficiency'] ?? null;
    $fcstEff = $result['forecast_efficiency'] ?? null;
    $fcstRemain = $result['forecast_remaining_days'] ?? null;
    
    $forecastFact = $fact;
    if ($fcstEff > 0 && $plan > 0) {
        $forecastFact = $plan / ($fcstEff / 100);
    }
    
    echo "$wt: plan=$plan fact=$fact currEff=$currEff fcstEff=$fcstEff fcstRemain=" . ($fcstRemain ?? 'NULL') . " forecastFact=" . round($forecastFact, 1) . "\n";
    
    $totalPlan += $plan;
    $totalFact += $fact;
    $totalForecastFact += $forecastFact;
}

echo "\n=== Summary ===\n";
echo "totalPlan=$totalPlan totalFact=$totalFact totalForecastFact=" . round($totalForecastFact, 1) . "\n";
$summCurr = $totalFact > 0 ? round($totalPlan / $totalFact * 100, 1) : null;
$summFcst = $totalForecastFact > 0 ? round($totalPlan / $totalForecastFact * 100, 1) : null;
echo "Summary current={$summCurr}% forecast={$summFcst}%\n";
echo "Expected: current=57.9% forecast=47.4%\n";

$db->close();
