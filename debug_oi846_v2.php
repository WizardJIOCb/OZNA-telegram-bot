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

// ======= TG BOT APPROACH (replicate getWorkTypeDetailedData) =======
echo "=== TG BOT APPROACH ===\n\n";

$smrMilestoneStructure = [
    'Подготовительные работы' => [
        'Входной контроль' => ['Работы по графику (Входной контроль)'],
        'Завоз оборудования на площадку' => ['Работы по графику (Завоз оборудования на площадку)'],
        'Проверка монтажной площадки' => ['Работы по графику (Проверка монтажной площадки)']
    ],
    'Монтаж блоков' => [
        'Расстановка блоков' => ['Работы по графику (Расстановка блоков)'],
        'Монтаж площадок обслуживания' => ['Работы по графику (Монтаж площадок обслуживания)']
    ],
    'Сборка здания' => [
        'Монтаж элементов укрытия' => ['Работы по графику (Монтаж элементов укрытия)'],
        'Сборка кровли' => ['Работы по графику (Сборка кровли)']
    ],
    'Внутриблочный монтаж' => [
        'Технологическое оборудование' => ['Работы по графику (Технологическое оборудование)'],
        'Электрооборудование' => ['Работы по графику (Электрооборудование)'],
        'Отопление и вентиляция' => ['Работы по графику (Отопление и вентиляция)'],
        'Пожарная сигнализация' => ['Работы по графику (Пожарная сигнализация)'],
        'Охранная сигнализация' => ['Работы по графику (Охранная сигнализация)'],
        'Автоматизация' => ['Работы по графику (Автоматизация)']
    ],
    'Подписание акта ШМР' => [
        'Подписание акта ШМР' => ['Работы по графику (Подписание акта ШМР)']
    ]
];

$smrMilestoneNames = ['Подготовительные работы', 'Монтаж блоков', 'Сборка здания', 'Внутриблочный монтаж', 'Подписание акта ШМР'];
$pnrMilestoneNames = ['Электрооборудование', 'Пожарная сигнализация', 'Охранная сигнализация', 'Автоматизация', 'Вентиляция и отопление', 'Технологическое оборудование', 'Метрология', 'ИИ на рабочей среде', 'Комплексное опробование'];

// Sub-milestone names for ШМР
$validSubMilestoneNames = [];
foreach ($smrMilestoneStructure as $topName => $subs) {
    foreach ($subs as $subName => $activities) {
        $validSubMilestoneNames[] = $subName;
    }
}

$tgTotalPlan = 0;
$tgTotalFact = 0;
$tgTotalForecastFact = 0;

foreach (['ШМР', 'ПНР', 'СМР'] as $wt) {
    echo "--- $wt (TG bot) ---\n";
    
    // stagePlan from project_milestones
    $stmt = $db->prepare("SELECT milestone_name, planned_hours, is_not_applicable, unit FROM project_milestones WHERE project_code = ? AND work_type = ?");
    $stmt->bind_param('ss', $code, $wt);
    $stmt->execute();
    $result = $stmt->get_result();
    $stagePlan = 0;
    while ($row = $result->fetch_assoc()) {
        if ($wt === 'ШМР' && !in_array($row['milestone_name'], $validSubMilestoneNames)) continue;
        if (!$row['is_not_applicable'] && $row['planned_hours'] > 0) {
            if ($row['unit'] === 'hours') {
                $stagePlan += ceil($row['planned_hours'] / 8);
            } else {
                $stagePlan += $row['planned_hours'];
            }
        }
    }
    
    // auxPlan from project_cards
    $auxPlanColumns = match($wt) {
        'ШМР' => ['smr_mobilization', 'smr_project_activity', 'smr_master_duties'],
        'СМР' => ['cmr_mobilization', 'cmr_project_activity'],
        'ПНР' => ['pnr_mobilization', 'pnr_project_activity'],
        default => []
    };
    $auxPlan = 0;
    if (!empty($auxPlanColumns)) {
        $auxSelect = implode(', ', $auxPlanColumns);
        $stmt = $db->prepare("SELECT {$auxSelect} FROM project_cards WHERE project_code = ?");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $cardData = $result->fetch_assoc();
        if ($cardData) {
            foreach ($auxPlanColumns as $col) {
                $auxPlan += $cardData[$col] ?? 0;
            }
        }
    }
    $overallPlan = $stagePlan + $auxPlan;
    
    // totalFact from tabel
    $functionalDirection = match($wt) {
        'ШМР' => 'Шеф-монтаж',
        'СМР' => 'Мобильная бригада',
        'ПНР' => 'Пусконаладка',
        default => null
    };
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT ot.id) as total_count
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
        AND JSON_EXTRACT(od.data, '$.functional_direction') = ?
        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
        AND ot.message NOT LIKE '%Этап наладки завершен%'
        AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
        AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
    ");
    $stmt->bind_param('ss', $code, $functionalDirection);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $totalFact = $row['total_count'] ?? 0;
    
    // milestoneFact (TG bot way)
    $milestoneFact = 0;
    if ($wt === 'ШМР') {
        foreach ($smrMilestoneNames as $topName) {
            $subs = $smrMilestoneStructure[$topName] ?? [];
            foreach ($subs as $subName => $activities) {
                $activityConditions = [];
                foreach ($activities as $activity) {
                    $activityConditions[] = "ot.message LIKE '%" . $db->real_escape_string($activity) . "%'";
                }
                $activityFilter = '(' . implode(' OR ', $activityConditions) . ')';
                $sql = "
                    SELECT COUNT(DISTINCT ot.id) as days_count
                    FROM ozna_tabel ot
                    INNER JOIN ozna_data od ON ot.user_id = od.user_id
                    WHERE ot.message LIKE CONCAT('%', ?, '%')
                    AND {$activityFilter}
                    AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
                    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                ";
                $stmt = $db->prepare($sql);
                $stmt->bind_param('s', $code);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $dayCount = $row['days_count'] ?? 0;
                if ($dayCount > 0) echo "  ШМР sub '$subName': $dayCount days\n";
                $milestoneFact += $dayCount;
            }
        }
    } elseif ($wt === 'ПНР') {
        foreach ($pnrMilestoneNames as $milestoneName) {
            $stmt = $db->prepare("
                SELECT COUNT(DISTINCT ot.id) as days_count
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message NOT LIKE '%Этап наладки завершен%'
                AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
            ");
            $stmt->bind_param('ss', $code, $milestoneName);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $dayCount = $row['days_count'] ?? 0;
            if ($dayCount > 0) echo "  ПНР milestone '$milestoneName': $dayCount days\n";
            $milestoneFact += $dayCount;
        }
    }
    
    // forecastFact (TG bot way)
    $forecastFact = $totalFact;
    if (($wt === 'ШМР' || $wt === 'ПНР') && $stagePlan > 0) {
        $remainingPlan = max(0, $stagePlan - $milestoneFact);
        if ($milestoneFact > 0 && $remainingPlan > 0) {
            $currentEfficiency = $stagePlan / $milestoneFact;
            $forecastRemaining = $currentEfficiency > 0 ? $remainingPlan / $currentEfficiency : $remainingPlan;
            $forecastFact = $totalFact + $forecastRemaining;
        } elseif ($remainingPlan <= 0) {
            $forecastFact = $totalFact;
        } else {
            $forecastFact = $totalFact + $remainingPlan;
        }
    }
    // For СМР, just use totalFact as forecastFact for simplicity
    
    echo "  stagePlan=$stagePlan auxPlan=$auxPlan overallPlan=$overallPlan\n";
    echo "  totalFact=$totalFact milestoneFact=$milestoneFact\n";
    echo "  remainingPlan=" . max(0, $stagePlan - $milestoneFact) . " forecastFact=" . round($forecastFact, 1) . "\n";
    if ($overallPlan > 0 && $forecastFact > 0) {
        echo "  forecastEff=" . round($overallPlan / $forecastFact * 100, 1) . "%\n";
    }
    echo "\n";
    
    $tgTotalPlan += $overallPlan;
    $tgTotalFact += $totalFact;
    $tgTotalForecastFact += $forecastFact;
}

echo "=== TG BOT SUMMARY ===\n";
echo "totalPlan=$tgTotalPlan totalFact=$tgTotalFact totalForecastFact=" . round($tgTotalForecastFact, 1) . "\n";
$tgCurr = $tgTotalFact > 0 ? round($tgTotalPlan / $tgTotalFact * 100, 1) : null;
$tgFcst = $tgTotalForecastFact > 0 ? round($tgTotalPlan / $tgTotalForecastFact * 100, 1) : null;
echo "TG current={$tgCurr}% forecast={$tgFcst}%\n\n";

// ======= PWA APPROACH (current MetricsCacheService) =======
echo "=== PWA APPROACH ===\n\n";
$pwaTotalPlan = 0; $pwaTotalFact = 0; $pwaTotalForecastFact = 0;

foreach (['ШМР', 'ПНР', 'СМР'] as $wt) {
    echo "--- $wt (PWA) ---\n";
    $result = $cacheService->calculateForProject($code, $wt);
    $plan = $result['planned_hours_days'] ?? 0;
    $fact = $result['fact_hours_days'] ?? 0;
    $fcstEff = $result['forecast_efficiency'] ?? null;
    
    $forecastFact = $fact;
    if ($fcstEff > 0 && $plan > 0) {
        $forecastFact = $plan / ($fcstEff / 100);
    }
    
    // Also get internal values
    $milestoneFact = $cacheService->getMilestonesFactDays($code, $wt);
    $milestonePlan = $cacheService->getMilestonesPlanDays($code, $wt);
    
    echo "  plan=$plan fact=$fact\n";
    echo "  milestonePlan=$milestonePlan milestoneFact=$milestoneFact\n";
    echo "  fcstEff=$fcstEff forecastFact=" . round($forecastFact, 1) . "\n\n";
    
    $pwaTotalPlan += $plan;
    $pwaTotalFact += $fact;
    $pwaTotalForecastFact += $forecastFact;
}

echo "=== PWA SUMMARY ===\n";
echo "totalPlan=$pwaTotalPlan totalFact=$pwaTotalFact totalForecastFact=" . round($pwaTotalForecastFact, 1) . "\n";
$pwaCurr = $pwaTotalFact > 0 ? round($pwaTotalPlan / $pwaTotalFact * 100, 1) : null;
$pwaFcst = $pwaTotalForecastFact > 0 ? round($pwaTotalPlan / $pwaTotalForecastFact * 100, 1) : null;
echo "PWA current={$pwaCurr}% forecast={$pwaFcst}%\n\n";

echo "Expected: current=57.9% forecast=47.4%\n";

$db->close();
