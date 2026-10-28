<?php
$db = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$db->set_charset('utf8mb4');
// Check per-milestone facts directly
require_once __DIR__ . '/api/services/MetricsCacheService.php';
require_once __DIR__ . '/api/services/DatabaseService.php';
$dbService = new \App\Services\DatabaseService();
$service = new \App\Services\MetricsCacheService($dbService->getConnection());
$facts = $service->getPerMilestoneFactDays('ОИ886', 'ПНР');
echo "getPerMilestoneFactDays('ОИ886', 'ПНР'):\n";
foreach ($facts as $k => $v) echo "  {$k}: {$v}\n";
echo "Sum: " . array_sum($facts) . "\n";
$db->close();
