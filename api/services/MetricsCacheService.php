<?php

namespace OznaApi\Services;

/**
 * MetricsCacheService
 *
 * Shared service for calculating project metrics and populating the metrics_cache table.
 * Used by both the nightly cron script and the real-time fallback in MetricsController.
 *
 * Calculation logic is extracted from DashboardExportController to avoid duplication.
 */
class MetricsCacheService
{
    private \mysqli $db;
    private $milestoneService;

    /** Projects to exclude from metrics */
    private const EXCLUDED_PROJECTS = ['ХХХ'];

    /** BN group mapping (values match business_units.name in DB) */
    public const BN_GROUPS = [
        'nko'   => ['НКО'],
        'opnvg' => ['ОПНВГ', 'ГАЗ'],
        'mso'   => ['МСО', 'АСН'],
    ];

    public function __construct(\mysqli $db, $milestoneService)
    {
        $this->db = $db;
        $this->milestoneService = $milestoneService;
    }

    /**
     * Valid milestone names per work_type.
     * Milestone rows whose names are NOT in this list are ignored (legacy/orphaned records).
     * Matches TG bot's structure validation (getSMRMilestoneStructure sub-milestones,
     * getPNRMilestoneNames, getCMRMilestoneStructure sub-milestones).
     */
    private function getValidMilestoneNames(string $workType): array
    {
        return match ($workType) {
            'ШМР' => [
                'Входной контроль',
                'Завоз оборудования на площадку',
                'Проверка монтажной площадки',
                'Расстановка блоков',
                'Монтаж площадок обслуживания',
                'Монтаж элементов укрытия',
                'Сборка кровли',
                'Технологическое оборудование',
                'Электрооборудование',
                'Отопление и вентиляция',
                'Пожарная сигнализация',
                'Охранная сигнализация',
                'Автоматизация',
                'Подписание акта ШМР',
            ],
            'ПНР' => [
                'Электрооборудование',
                'Пожарная сигнализация',
                'Охранная сигнализация',
                'Автоматизация',
                'Вентиляция и отопление',
                'Технологическое оборудование',
                'Метрология',
                'ИИ на рабочей среде',
                'Комплексное опробование',
                'Режимная наладка',       // legacy alias for "ИИ на рабочей среде"
                'Индивидуальные испытания', // current alias for "ИИ на рабочей среде"
            ],
            'СМР' => [
                'Электромонтажные работы - Досборка',
                'Электромонтажные работы - Реализация ТР',
                'Электромонтажные работы - Устранение замечаний РК',
                'Электромонтажные работы - Работы по гарантии',
                'Слесарные работы - Досборка',
                'Слесарные работы - Реализация ТР',
                'Слесарные работы - Устранение замечаний РК',
                'Слесарные работы - Работы по гарантии',
                'Сварочные работы - Досборка',
                'Сварочные работы - Реализация ТР',
                'Сварочные работы - Устранение замечаний РК',
                'Сварочные работы - Работы по гарантии',
                'Работы по теплоизоляции - Досборка',
                'Работы по теплоизоляции - Реализация ТР',
                'Работы по теплоизоляции - Устранение замечаний РК',
                'Работы по теплоизоляции - Работы по гарантии',
                'Малярные работы - Досборка',
                'Малярные работы - Реализация ТР',
                'Малярные работы - Устранение замечаний РК',
                'Малярные работы - Работы по гарантии',
            ],
            default => [],
        };
    }

    // ── Public API ────────────────────────────────────────────

    /**
     * Full refresh: recalculate metrics for ALL projects and populate cache.
     */
    public function fullRefresh(): array
    {
        $stats = ['processed' => 0, 'rows_written' => 0, 'errors' => 0];
        $db = $this->db;

        $allCodes = $this->getAllProjectCodes();
        $stats['processed'] = count($allCodes);

        // Load project card data in bulk
        $projectCards = $this->loadProjectCards($allCodes);

        foreach ($allCodes as $code) {
            if (in_array($code, self::EXCLUDED_PROJECTS)) continue;

            try {
                $project = $projectCards[$code] ?? null;
                $isActive = $this->checkActivity($code);
                $missingReasons = $this->detectMissingData($code, $project);
                $hasMissing = !empty($missingReasons);

                foreach (['СМР', 'ШМР', 'ПНР'] as $workType) {
                    $hasMilestones = $this->hasMilestones($code, $workType);
                    $factDays = $this->getTotalFactDays($code, $workType);

                    // Skip if no milestones AND no tabel activity AND no historical fact
                    $historicalFact = $this->getHistoricalFact($project ?? [], $workType);
                    if (!$hasMilestones && $factDays == 0 && $historicalFact == 0) continue;

                    $row = $this->calculateForProject($code, $workType, $project);
                    $row['is_active'] = $isActive ? 1 : 0;
                    $row['has_missing_data'] = $hasMissing ? 1 : 0;
                    $row['missing_data_reasons'] = json_encode($missingReasons, JSON_UNESCAPED_UNICODE);

                    $this->writeCache($row);
                    $stats['rows_written']++;
                }
            } catch (\Throwable $e) {
                error_log("MetricsCacheService::fullRefresh error for {$code}: " . $e->getMessage());
                $stats['errors']++;
            }
        }

        // Clean up stale rows for projects that no longer exist
        $this->cleanupStaleRows($allCodes);

        return $stats;
    }

    /**
     * Recalculate cache for a single project (all work types).
     * Called after editing a project card.
     */
    public function refreshProject(string $projectCode): void
    {
        $projectCards = $this->loadProjectCards([$projectCode]);
        $project = $projectCards[$projectCode] ?? null;
        $isActive = $this->checkActivity($projectCode);
        $missingReasons = $this->detectMissingData($projectCode, $project);
        $hasMissing = !empty($missingReasons);

        foreach (['СМР', 'ШМР', 'ПНР'] as $workType) {
            $hasMilestones = $this->hasMilestones($projectCode, $workType);
            $factDays = $this->getTotalFactDays($projectCode, $workType);
            $historicalFact = $this->getHistoricalFact($project ?? [], $workType);

            if (!$hasMilestones && $factDays == 0 && $historicalFact == 0) {
                // Remove stale row if exists
                $stmt = $this->db->prepare("DELETE FROM metrics_cache WHERE project_code = ? AND work_type = ?");
                $stmt->bind_param('ss', $projectCode, $workType);
                $stmt->execute();
                $stmt->close();
                continue;
            }

            $row = $this->calculateForProject($projectCode, $workType, $project);
            $row['is_active'] = $isActive ? 1 : 0;
            $row['has_missing_data'] = $hasMissing ? 1 : 0;
            $row['missing_data_reasons'] = json_encode($missingReasons, JSON_UNESCAPED_UNICODE);

            $this->writeCache($row);
        }
    }

    /**
     * Calculate metrics for one project + work_type.
     * Returns an associative array matching metrics_cache columns.
     */
    public function calculateForProject(string $projectCode, string $workType, ?array $project = null): array
    {
        $db = $this->db;

        if ($project === null) {
            $cards = $this->loadProjectCards([$projectCode]);
            $project = $cards[$projectCode] ?? null;
        }

        // Defaults when no project card
        $projectName = $project['project_name'] ?? '';
        $customerName = $project['customer_name'] ?? '';
        $bnName = $project['business_unit_name'] ?? null;
        $rpFio = $project['project_manager_fio'] ?? null;

        // Planned completion
        $plannedCompletion = null;
        if ($workType === 'ШМР' && !empty($project['smr_planned_completion'])) {
            $plannedCompletion = $project['smr_planned_completion'];
        } elseif ($workType === 'ПНР' && !empty($project['pnr_planned_completion'])) {
            $plannedCompletion = $project['pnr_planned_completion'];
        } elseif ($workType === 'СМР' && !empty($project['cmr_planned_completion'])) {
            $plannedCompletion = $project['cmr_planned_completion'];
        }

        // Plan days (milestones + auxiliary)
        $milestonesPlanDays = $this->getMilestonesPlanDays($projectCode, $workType);
        $auxPlan = $this->getAuxiliaryPlanDays($project ?? [], $workType);
        $overallPlanDays = $milestonesPlanDays + $auxPlan;

        // Fact days
        // For СМР: sum only known categories (milestones + aux), excluding garbage old-format records
        if ($workType === 'СМР') {
            $milestoneFact = $this->getMilestonesFactDays($projectCode, $workType);
            $auxFacts = $this->getAuxiliaryWorkFacts($projectCode, $workType, $project ?? []);
            $totalFactDays = $milestoneFact;
            foreach ($auxFacts as $cat) {
                $totalFactDays += (float)($cat['fact'] ?? 0);
            }
        } else {
            $totalFactDays = $this->getTotalFactDays($projectCode, $workType);
        }

        // Add historical fact from archival data (manual entry)
        $historicalFact = $this->getHistoricalFact($project ?? [], $workType);
        $totalFactDays += $historicalFact;

        // Stage progress
        $progress = $this->calculateStageProgress($projectCode, $workType);

        // СМР: hybrid Mode A/B forecast
        $cmrForecast = null;
        if ($workType === 'СМР') {
            $cmrForecastEnabled = !empty($project['cmr_forecast_enabled']);
            if ($cmrForecastEnabled) {
                $cmrForecast = $this->calculateCMRForecast($projectCode);
                if ($cmrForecast && $cmrForecast['overallProgress'] !== null && ($progress === null || $progress == 0)) {
                    $progress = $cmrForecast['overallProgress'];
                }
            }
        }

        // Forecast remaining days
        $forecastDays = null;
        $forecastEfficiency = null;
        $forecastRemainingDays = null;
        $aggregateForecastFact = null;

        if ($workType === 'СМР') {
            // СМР: forecast based on ШМР milestone progress
            if ($cmrForecast !== null && $totalFactDays > 0) {
                if ($cmrForecast['overallProgress'] !== null && $cmrForecast['overallProgress'] > 0) {
                    // Mode B: weighted ШМР milestone progress → derive forecast
                    $smrProgress = $cmrForecast['overallProgress'];
                    if ($smrProgress >= 100) {
                        $forecastDays = $totalFactDays;
                    } else {
                        $forecastDays = round($totalFactDays / ($smrProgress / 100), 1);
                    }
                    $forecastRemainingDays = round($forecastDays - $totalFactDays, 1);
                    if ($overallPlanDays > 0 && $forecastDays > 0) {
                        $forecastEfficiency = round(($overallPlanDays / $forecastDays) * 100, 1);
                    }
                    $aggregateForecastFact = $forecastDays;
                } elseif ($cmrForecast['forecastRemainingDays'] > 0) {
                    // Mode A fallback: use plan/fact remaining when no ШМР milestone progress
                    $forecastRemainingDays = round($cmrForecast['forecastRemainingDays'], 1);
                    $forecastDays = round($totalFactDays + $forecastRemainingDays, 1);
                    if ($overallPlanDays > 0 && $forecastDays > 0) {
                        $forecastEfficiency = round(($overallPlanDays / $forecastDays) * 100, 1);
                    }
                    $aggregateForecastFact = $forecastDays;
                }
            }
        } else {
            // ШМР/ПНР: dual forecast approach
            // 1) Survey-based (per-milestone) → forecast_efficiency (matches TG info page)
            $surveyRemaining = $this->calculateForecastRemainingDays($projectCode, $workType);
            if ($surveyRemaining !== null && $totalFactDays > 0) {
                $forecastRemainingDays = $surveyRemaining;
                $forecastDays = round($totalFactDays + $forecastRemainingDays, 1);
                if ($overallPlanDays > 0 && $forecastDays > 0) {
                    $forecastEfficiency = round(($overallPlanDays / $forecastDays) * 100, 1);
                }
            }

            // 2) Aggregate (milestoneFact-based) → aggregate_forecast_fact (for summary)
            if ($milestonesPlanDays > 0) {
                $milestoneFact = $this->getMilestonesFactDays($projectCode, $workType);
                if ($milestoneFact > 0) {
                    $remainingPlan = max(0, $milestonesPlanDays - $milestoneFact);
                    if ($remainingPlan > 0) {
                        $aggForecastRemaining = $remainingPlan / ($milestonesPlanDays / $milestoneFact);
                    } else {
                        $aggForecastRemaining = 0;
                    }
                    $aggregateForecastFact = round($totalFactDays + $aggForecastRemaining, 1);
                } elseif ($totalFactDays > 0) {
                    $aggregateForecastFact = round($totalFactDays + $milestonesPlanDays, 1);
                }
            }
        }

        // Forecast completion date
        if ($progress !== null && $progress >= 100) {
            $forecastCompletion = 'Завершен';
            // Completed: aggregate_forecast_fact = actual fact (final number)
            if ($totalFactDays > 0) {
                $aggregateForecastFact = round($totalFactDays, 1);
            }
        } else {
            $forecastCompletion = $this->calculateForecastCompletionDate(
                $projectCode, $workType, $progress, $forecastRemainingDays
            );
        }

        // Derive aggregate_forecast_fact from forecast_efficiency when available
        // This ensures summary uses the same forecast logic as per-work-type display
        if ($forecastEfficiency !== null && $forecastEfficiency > 0 && $overallPlanDays > 0) {
            $aggregateForecastFact = round($overallPlanDays / ($forecastEfficiency / 100), 1);
        }
        // Fallback for in-progress without forecast: extrapolate from stage progress
        if ($aggregateForecastFact === null && $totalFactDays > 0) {
            if ($progress !== null && $progress > 0 && $progress < 100) {
                $aggregateForecastFact = round($totalFactDays / ($progress / 100), 1);
            } else {
                // No forecast, no progress — use current fact as best estimate
                $aggregateForecastFact = round($totalFactDays, 1);
            }
        }

        // Current efficiency
        $currentEfficiency = null;
        if ($overallPlanDays > 0 && $totalFactDays > 0) {
            $currentEfficiency = round(($overallPlanDays / $totalFactDays) * 100, 1);
        }

        // Milestone details
        $milestoneDetails = $this->getMilestoneDetails($projectCode, $workType);

        // Auxiliary work data (for СМР already computed above for totalFactDays)
        if ($workType === 'СМР' && isset($auxFacts)) {
            $auxiliaryData = $auxFacts;
        } else {
            $auxiliaryData = $this->getAuxiliaryWorkFacts($projectCode, $workType, $project ?? []);
        }

        return [
            'project_code'           => $projectCode,
            'work_type'              => $workType,
            'business_unit_name'     => $bnName,
            'customer_name'          => $customerName,
            'project_name'           => $projectName,
            'project_manager_fio'    => $rpFio,
            'planned_completion'     => $plannedCompletion,
            'planned_hours_days'     => $overallPlanDays > 0 ? $overallPlanDays : null,
            'fact_hours_days'        => $totalFactDays > 0 ? $totalFactDays : null,
            'stage_progress'         => $progress,
            'current_efficiency'     => $currentEfficiency,
            'forecast_efficiency'    => $forecastEfficiency,
            'forecast_remaining_days'=> $forecastRemainingDays !== null ? round($forecastRemainingDays, 1) : null,
            'aggregate_forecast_fact'=> $aggregateForecastFact,
            'forecast_completion'    => $forecastCompletion ?: null,
            'milestone_details'      => json_encode($milestoneDetails, JSON_UNESCAPED_UNICODE),
            'auxiliary_data'         => json_encode($auxiliaryData, JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * Detect missing data for a project card.
     * Returns array of reason strings.
     */
    public function detectMissingData(string $projectCode, ?array $project = null): array
    {
        $reasons = [];

        if ($project === null) {
            $reasons[] = 'no_card';
            return $reasons;
        }

        $hasBN = !empty($project['business_unit_id']);
        $hasRP = !empty($project['project_manager_id']);
        $hasCustomer = !empty($project['customer_id']);

        if (!$hasBN) {
            $reasons[] = 'no_bn';
        }
        if (!$hasRP) {
            $reasons[] = 'no_rp';
        }
        if (!$hasCustomer) {
            $reasons[] = 'no_customer';
        }

        // If all primary fields (BN, RP, Customer) are filled,
        // the project is sortable and workable via its BN group — not "missing"
        if ($hasBN && $hasRP && $hasCustomer) {
            return [];
        }

        // Check per-work_type planned data
        foreach (['ШМР' => 'smr', 'ПНР' => 'pnr', 'СМР' => 'cmr'] as $wt => $prefix) {
            $hasMilestones = $this->hasMilestones($projectCode, $wt);
            $factDays = $this->getTotalFactDays($projectCode, $wt);

            // Only flag missing data if there's activity or milestones for this work type
            if ($hasMilestones || $factDays > 0) {
                if (empty($project[$prefix . '_planned_completion'])) {
                    $reasons[] = "no_planned_date_{$wt}";
                }
            }
        }

        return $reasons;
    }

    /**
     * Check if a project has tabel activity in the last N days.
     */
    public function checkActivity(string $projectCode, int $days = 30): bool
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        $spaced = $this->getSpacedCodeVariant($projectCode);
        if ($spaced) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as cnt FROM ozna_tabel ot
                WHERE ot.dt >= ? AND (ot.message LIKE CONCAT('%', ?, '%') OR ot.message LIKE CONCAT('%', ?, '%'))
            ");
            $stmt->bind_param('sss', $cutoffDate, $projectCode, $spaced);
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as cnt FROM ozna_tabel ot
                WHERE ot.dt >= ? AND ot.message LIKE CONCAT('%', ?, '%')
            ");
            $stmt->bind_param('ss', $cutoffDate, $projectCode);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return ($row['cnt'] ?? 0) > 0;
    }

    /**
     * UPSERT a row into metrics_cache.
     */
    public function writeCache(array $row): void
    {
        $db = $this->db;
        $stmt = $db->prepare("
            INSERT INTO metrics_cache 
                (project_code, work_type, business_unit_name, customer_name, project_name,
                 project_manager_fio, planned_completion, planned_hours_days, fact_hours_days,
                 stage_progress, current_efficiency, forecast_efficiency, forecast_remaining_days,
                 aggregate_forecast_fact, forecast_completion, milestone_details, auxiliary_data,
                 has_missing_data, missing_data_reasons, is_active, calculated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                business_unit_name = VALUES(business_unit_name),
                customer_name = VALUES(customer_name),
                project_name = VALUES(project_name),
                project_manager_fio = VALUES(project_manager_fio),
                planned_completion = VALUES(planned_completion),
                planned_hours_days = VALUES(planned_hours_days),
                fact_hours_days = VALUES(fact_hours_days),
                stage_progress = VALUES(stage_progress),
                current_efficiency = VALUES(current_efficiency),
                forecast_efficiency = VALUES(forecast_efficiency),
                forecast_remaining_days = VALUES(forecast_remaining_days),
                aggregate_forecast_fact = VALUES(aggregate_forecast_fact),
                forecast_completion = VALUES(forecast_completion),
                milestone_details = VALUES(milestone_details),
                auxiliary_data = VALUES(auxiliary_data),
                has_missing_data = VALUES(has_missing_data),
                missing_data_reasons = VALUES(missing_data_reasons),
                is_active = VALUES(is_active),
                calculated_at = NOW()
        ");

        $stmt->bind_param(
            'sssssssdddddddsssisi',
            $row['project_code'],
            $row['work_type'],
            $row['business_unit_name'],
            $row['customer_name'],
            $row['project_name'],
            $row['project_manager_fio'],
            $row['planned_completion'],
            $row['planned_hours_days'],
            $row['fact_hours_days'],
            $row['stage_progress'],
            $row['current_efficiency'],
            $row['forecast_efficiency'],
            $row['forecast_remaining_days'],
            $row['aggregate_forecast_fact'],
            $row['forecast_completion'],
            $row['milestone_details'],
            $row['auxiliary_data'],
            $row['has_missing_data'],
            $row['missing_data_reasons'],
            $row['is_active']
        );
        $stmt->execute();
        $stmt->close();
    }

    // ── Data Loading ──────────────────────────────────────────

    /**
     * Get all known project codes (from project_cards + active in tabel).
     */
    public function getAllProjectCodes(): array
    {
        $db = $this->db;

        // From project_cards
        $stmt = $db->prepare("SELECT project_code FROM project_cards ORDER BY project_code");
        $stmt->execute();
        $result = $stmt->get_result();
        $codes = [];
        while ($row = $result->fetch_assoc()) {
            $codes[$row['project_code']] = true;
        }
        $stmt->close();

        // From tabel (all time, no date cutoff)
        $result = $db->query("
            SELECT DISTINCT 
                SUBSTRING_INDEX(SUBSTRING_INDEX(ot.message, '</b>:', 1), 'Проект <b>', -1) as project_code
            FROM ozna_tabel ot
            WHERE ot.message LIKE '%Проект <b>%</b>:%'
        ");
        while ($row = $result->fetch_assoc()) {
            $code = trim($row['project_code'] ?? '');
            if (!empty($code) && mb_strlen($code) <= 20) {
                // Normalize: remove spaces (e.g. "ОИ 873" → "ОИ873")
                $normalized = preg_replace('/\s+/', '', $code);
                $codes[$normalized] = true;
            }
        }

        $allCodes = array_keys($codes);
        sort($allCodes);
        return array_filter($allCodes, function ($c) {
            return !in_array($c, self::EXCLUDED_PROJECTS);
        });
    }

    /**
     * Load project card data for given codes (bulk).
     * Returns associative array keyed by project_code.
     */
    public function loadProjectCards(array $codes): array
    {
        if (empty($codes)) return [];
        $db = $this->db;

        $placeholders = implode(',', array_fill(0, count($codes), '?'));
        $types = str_repeat('s', count($codes));

        $stmt = $db->prepare("
            SELECT 
                pc.*,
                bu.name AS business_unit_name,
                c.name AS customer_name,
                pm.name AS project_manager_fio
            FROM project_cards pc
            LEFT JOIN business_units bu ON pc.business_unit_id = bu.id
            LEFT JOIN customers c ON pc.customer_id = c.id
            LEFT JOIN project_managers pm ON pc.project_manager_id = pm.id
            WHERE pc.project_code IN ({$placeholders})
        ");
        $stmt->bind_param($types, ...$codes);
        $stmt->execute();
        $result = $stmt->get_result();

        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $projects[$row['project_code']] = $row;
        }
        $stmt->close();

        return $projects;
    }

    // ── Calculation Methods (extracted from DashboardExportController) ──

    /**
     * Weighted-average stage progress for a work_type.
     */
    public function calculateStageProgress(string $projectCode, string $workType): ?float
    {
        $db = $this->db;
        $validNames = $this->getValidMilestoneNames($workType);
        $stmt = $db->prepare("
            SELECT milestone_name, planned_hours, is_not_applicable, unit
            FROM project_milestones WHERE project_code = ? AND work_type = ?
        ");
        $stmt->bind_param('ss', $projectCode, $workType);
        $stmt->execute();
        $result = $stmt->get_result();

        $milestones = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($validNames) && !in_array($row['milestone_name'], $validNames)) continue;
            if (!empty($row['is_not_applicable'])) continue;
            $plan = (float)$row['planned_hours'];
            if ($plan <= 0) continue;
            if ($row['unit'] === 'hours') $plan = ceil($plan / 8);
            $milestones[$row['milestone_name']] = $plan;
        }
        $stmt->close();

        if (empty($milestones)) return null;

        $estimates = $this->milestoneService->getProjectMilestoneEstimates($projectCode, $workType);

        $totalWeightedProgress = 0;
        $totalPlan = 0;
        foreach ($milestones as $name => $plan) {
            $totalPlan += $plan;
            $estimate = $estimates[$name] ?? null;
            $percent = $estimate ? (float)$estimate['estimated_percent'] : 0;
            $totalWeightedProgress += $percent * $plan;
        }

        return $totalPlan > 0 ? round($totalWeightedProgress / $totalPlan, 1) : null;
    }

    /**
     * Total milestones plan in person-days.
     */
    public function getMilestonesPlanDays(string $projectCode, string $workType): float
    {
        $db = $this->db;
        $validNames = $this->getValidMilestoneNames($workType);
        $stmt = $db->prepare("
            SELECT milestone_name, planned_hours, unit, is_not_applicable
            FROM project_milestones WHERE project_code = ? AND work_type = ?
        ");
        $stmt->bind_param('ss', $projectCode, $workType);
        $stmt->execute();
        $result = $stmt->get_result();

        $totalDays = 0;
        while ($row = $result->fetch_assoc()) {
            if (!empty($validNames) && !in_array($row['milestone_name'], $validNames)) continue;
            if (!empty($row['is_not_applicable'])) continue;
            $plan = (float)($row['planned_hours'] ?? 0);
            if ($plan <= 0) continue;
            if ($row['unit'] === 'hours') $plan = ceil($plan / 8);
            $totalDays += $plan;
        }
        $stmt->close();
        return $totalDays;
    }

    /**
     * Auxiliary plan days from project_cards (mobilization, project_activity, etc.).
     */
    public function getAuxiliaryPlanDays(array $projectCard, string $workType): float
    {
        if ($workType === 'ШМР') {
            return (int)($projectCard['smr_mobilization'] ?? 0)
                 + (int)($projectCard['smr_project_activity'] ?? 0)
                 + (int)($projectCard['smr_master_duties'] ?? 0);
        } elseif ($workType === 'ПНР') {
            return (int)($projectCard['pnr_mobilization'] ?? 0)
                 + (int)($projectCard['pnr_project_activity'] ?? 0);
        } elseif ($workType === 'СМР') {
            return (int)($projectCard['cmr_mobilization'] ?? 0)
                 + (int)($projectCard['cmr_project_activity'] ?? 0);
        }
        return 0;
    }

    /**
     * Historical fact days from project card (manually entered archival data).
     */
    private function getHistoricalFact(array $project, string $workType): float
    {
        $prefix = match ($workType) { 'ШМР' => 'smr', 'ПНР' => 'pnr', 'СМР' => 'cmr', default => '' };
        return (float)($project["{$prefix}_historical_fact"] ?? 0);
    }

    /**
     * Generate spaced variant of a project code for tabel LIKE matching.
     * E.g. "ОИ873" → "ОИ 873", "БКНС120" → "БКНС 120"
     * Returns the spaced variant or null if code already contains a space or doesn't match the pattern.
     */
    private function getSpacedCodeVariant(string $code): ?string
    {
        if (preg_match('/^([А-ЯЁA-Z]+)(\d.*)$/u', $code, $m)) {
            $spaced = $m[1] . ' ' . $m[2];
            return ($spaced !== $code) ? $spaced : null;
        }
        return null;
    }

    /**
     * Total fact person-days from tabel for a project+work_type.
     */
    public function getTotalFactDays(string $projectCode, string $workType): float
    {
        $funcDirection = $this->workTypeToFuncDirection($workType);
        if (empty($funcDirection)) return 0;

        $db = $this->db;
        $spaced = $this->getSpacedCodeVariant($projectCode);
        $projectFilter = $spaced
            ? "(ot.message LIKE CONCAT('%', ?, '%') OR ot.message LIKE CONCAT('%', ?, '%'))"
            : "ot.message LIKE CONCAT('%', ?, '%')";
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT ot.id) as total_days
            FROM ozna_tabel ot
            INNER JOIN ozna_data od ON ot.user_id = od.user_id
            WHERE {$projectFilter}
            AND JSON_EXTRACT(od.data, '$.functional_direction') = ?
            AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            AND ot.message NOT LIKE '%Этап наладки завершен%'
            AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
            AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
            AND ot.message NOT LIKE '%Участие в ЗПИ%'
        ");
        if ($spaced) {
            $stmt->bind_param('sss', $projectCode, $spaced, $funcDirection);
        } else {
            $stmt->bind_param('ss', $projectCode, $funcDirection);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (float)($row['total_days'] ?? 0);
    }

    /**
     * Per-milestone fact days using TG bot's exact query patterns.
     * Returns associative array: ['milestone_name' => fact_days, ...]
     * Used as a shared helper for getMilestonesFactDays, calculateForecastRemainingDays, getMilestoneDetails.
     */
    public function getPerMilestoneFactDays(string $projectCode, string $workType): array
    {
        $db = $this->db;
        $result = [];

        if ($workType === 'ШМР') {
            $structure = $this->getSmrMilestoneStructure();
            foreach ($structure as $topName => $subs) {
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
                    $stmt->bind_param('s', $projectCode);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    $result[$subName] = (float)($row['days_count'] ?? 0);
                    $stmt->close();
                }
            }
        } elseif ($workType === 'ПНР') {
            $milestoneNames = $this->getPnrMilestoneNames();
            $nameAliases = ['ИИ на рабочей среде' => 'Режимная наладка'];
            $standaloneActivityMilestones = ['ИИ на рабочей среде', 'Комплексное опробование'];

            foreach ($milestoneNames as $milestoneName) {
                $alias = $nameAliases[$milestoneName] ?? null;
                $isStandalone = in_array($milestoneName, $standaloneActivityMilestones);

                if ($isStandalone && $alias) {
                    // Standalone with alias: match prefixed + bare name + alias patterns
                    $stmt = $db->prepare("
                        SELECT COUNT(DISTINCT ot.id) as days_count
                        FROM ozna_tabel ot
                        INNER JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND (ot.message LIKE CONCAT('%Работы по графику%', ?, '%')
                             OR ot.message LIKE CONCAT('%Формирование ИД%', ?, '%')
                             OR ot.message LIKE CONCAT('%', ?, '%')
                             OR ot.message LIKE CONCAT('%Работы по графику%', ?, '%')
                             OR ot.message LIKE CONCAT('%Формирование ИД%', ?, '%')
                             OR ot.message LIKE CONCAT('%', ?, '%'))
                        AND ot.message NOT LIKE '%Этап наладки завершен%'
                        AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                    ");
                    $stmt->bind_param('sssssss', $projectCode, $milestoneName, $milestoneName, $milestoneName, $alias, $alias, $alias);
                } elseif ($isStandalone) {
                    // Standalone without alias: match prefixed + bare name
                    $stmt = $db->prepare("
                        SELECT COUNT(DISTINCT ot.id) as days_count
                        FROM ozna_tabel ot
                        INNER JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND (ot.message LIKE CONCAT('%Работы по графику%', ?, '%')
                             OR ot.message LIKE CONCAT('%Формирование ИД%', ?, '%')
                             OR ot.message LIKE CONCAT('%', ?, '%'))
                        AND ot.message NOT LIKE '%Этап наладки завершен%'
                        AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                    ");
                    $stmt->bind_param('ssss', $projectCode, $milestoneName, $milestoneName, $milestoneName);
                } else {
                    // Standard milestones: only "Работы по графику" / "Формирование ИД"
                    $stmt = $db->prepare("
                        SELECT COUNT(DISTINCT ot.id) as days_count
                        FROM ozna_tabel ot
                        INNER JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND (ot.message LIKE CONCAT('%Работы по графику%', ?, '%')
                             OR ot.message LIKE CONCAT('%Формирование ИД%', ?, '%'))
                        AND ot.message NOT LIKE '%Этап наладки завершен%'
                        AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                    ");
                    $stmt->bind_param('sss', $projectCode, $milestoneName, $milestoneName);
                }
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $result[$milestoneName] = (float)($row['days_count'] ?? 0);
                $stmt->close();
            }
        } elseif ($workType === 'СМР') {
            $structure = $this->getCmrMilestoneStructure();
            foreach ($structure as $topName => $subs) {
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
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                    ";
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param('s', $projectCode);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    $result[$topName . ' - ' . $subName] = (float)($row['days_count'] ?? 0);
                    $stmt->close();
                }
            }
        }

        return $result;
    }

    /**
     * Sum of per-milestone fact days (aggregate, for summary forecast calculation).
     * Matches TG bot's getWorkTypeDetailedData milestoneFact.
     */
    public function getMilestonesFactDays(string $projectCode, string $workType): float
    {
        return array_sum($this->getPerMilestoneFactDays($projectCode, $workType));
    }

    /**
     * ШМР milestone structure matching TG bot's getSMRMilestoneStructure().
     * Maps top-level milestones → sub-milestones → activity patterns for tabel LIKE matching.
     */
    private function getSmrMilestoneStructure(): array
    {
        return [
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
                'Технологическое оборудование' => [
                    'Работы по графику (Монтаж трубопроводов)',
                    'Работы по графику (Гидро/пневмо испытания)',
                    'Работы по графику (Монтаж технологического оборудования)',
                    'Работы по графику (Монтаж ЗРА)',
                    'Работы по графику (Протяжка соединений)',
                    'Работы по графику (Монтаж теплоизоляции)',
                    'Прочее (Технологическое оборудование)'
                ],
                'Электрооборудование' => [
                    'Работы по графику (Монтаж оборудования - Электрооборудование)',
                    'Работы по графику (Монтаж кабеленесущих систем - Электрооборудование)',
                    'Работы по графику (Прокладка кабеля - Электрооборудование)',
                    'Работы по графику (Подключение оборудования - Электрооборудование)',
                    'Работы по графику (Маркировка оборудования/КЛ - Электрооборудование)',
                    'Прочее (Электрооборудование)'
                ],
                'Отопление и вентиляция' => [
                    'Работы по графику (Монтаж оборудования - Отопление и вентиляция)',
                    'Работы по графику (Монтаж воздуховодов/труб - Отопление и вентиляция)',
                    'Работы по графику (Монтаж кондиционеров - Отопление и вентиляция)',
                    'Работы по графику (Монтаж ИТП - Отопление и вентиляция)',
                    'Работы по графику (Проведение пневмо/гидро испытаний - Отопление и вентиляция)',
                    'Работы по графику (Монтаж КНС - Отопление и вентиляция)',
                    'Работы по графику (Прокладка кабеля - Отопление и вентиляция)',
                    'Работы по графику (Подключение кабеля - Отопление и вентиляция)',
                    'Работы по графику (Маркировка оборудования/КЛ - Отопление и вентиляция)',
                    'Прочее (Отопление и вентиляция)'
                ],
                'Пожарная сигнализация' => [
                    'Работы по графику (Монтаж оборудования - Пожарная сигнализация)',
                    'Работы по графику (Монтаж кабеленесущих систем - Пожарная сигнализация)',
                    'Работы по графику (Прокладка кабеля - Пожарная сигнализация)',
                    'Работы по графику (Подключение оборудования - Пожарная сигнализация)',
                    'Работы по графику (Маркировка оборудования/КЛ - Пожарная сигнализация)',
                    'Прочее (Пожарная сигнализация)'
                ],
                'Охранная сигнализация' => [
                    'Работы по графику (Монтаж оборудования - Охранная сигнализация)',
                    'Работы по графику (Монтаж кабеленесущих систем - Охранная сигнализация)',
                    'Работы по графику (Прокладка кабеля - Охранная сигнализация)',
                    'Работы по графику (Подключение оборудования - Охранная сигнализация)',
                    'Работы по графику (Маркировка оборудования/КЛ - Охранная сигнализация)',
                    'Прочее (Охранная сигнализация)'
                ],
                'Автоматизация' => [
                    'Работы по графику (Монтаж оборудования - Автоматизация)',
                    'Работы по графику (Монтаж кабеленесущих систем - Автоматизация)',
                    'Работы по графику (Прокладка кабеля - Автоматизация)',
                    'Работы по графику (Подключение оборудования - Автоматизация)',
                    'Работы по графику (Маркировка оборудования/КЛ - Автоматизация)',
                    'Прочее (Автоматизация)'
                ]
            ],
            'Подписание акта ШМР' => [
                'Подписание акта ШМР' => ['Подписание акта ШМР']
            ]
        ];
    }

    /**
     * ПНР milestone names matching TG bot's getPNRMilestoneNames().
     */
    private function getPnrMilestoneNames(): array
    {
        return [
            'Электрооборудование',
            'Пожарная сигнализация',
            'Охранная сигнализация',
            'Автоматизация',
            'Вентиляция и отопление',
            'Технологическое оборудование',
            'Метрология',
            'ИИ на рабочей среде',
            'Комплексное опробование',
        ];
    }

    /**
     * СМР milestone structure matching TG bot's getCMRMilestoneStructure().
     * Maps top-level milestones → sub-milestones → activity patterns for tabel LIKE matching.
     */
    private function getCmrMilestoneStructure(): array
    {
        return [
            'Электромонтажные работы' => [
                'Досборка' => ['Электромонтажные работы (Досборка)', 'Электромонтажные работы добавлена'],
                'Реализация ТР' => ['Электромонтажные работы (Реализация ТР)'],
                'Устранение замечаний РК' => ['Электромонтажные работы (Устранение замечаний РК)'],
                'Работы по гарантии' => ['Электромонтажные работы (Работы по гарантии)']
            ],
            'Слесарные работы' => [
                'Досборка' => ['Слесарные работы (Досборка)', 'Слесарные работы добавлена'],
                'Реализация ТР' => ['Слесарные работы (Реализация ТР)'],
                'Устранение замечаний РК' => ['Слесарные работы (Устранение замечаний РК)'],
                'Работы по гарантии' => ['Слесарные работы (Работы по гарантии)']
            ],
            'Сварочные работы' => [
                'Досборка' => ['Сварочные работы (Досборка)', 'Сварочные работы добавлена'],
                'Реализация ТР' => ['Сварочные работы (Реализация ТР)'],
                'Устранение замечаний РК' => ['Сварочные работы (Устранение замечаний РК)'],
                'Работы по гарантии' => ['Сварочные работы (Работы по гарантии)']
            ],
            'Работы по теплоизоляции' => [
                'Досборка' => ['Работы по теплоизоляции (Досборка)', 'Работы по теплоизоляции добавлена'],
                'Реализация ТР' => ['Работы по теплоизоляции (Реализация ТР)'],
                'Устранение замечаний РК' => ['Работы по теплоизоляции (Устранение замечаний РК)'],
                'Работы по гарантии' => ['Работы по теплоизоляции (Работы по гарантии)']
            ],
            'Малярные работы' => [
                'Досборка' => ['Малярные работы (Досборка)', 'Малярные работы добавлена'],
                'Реализация ТР' => ['Малярные работы (Реализация ТР)'],
                'Устранение замечаний РК' => ['Малярные работы (Устранение замечаний РК)'],
                'Работы по гарантии' => ['Малярные работы (Работы по гарантии)']
            ]
        ];
    }

    /**
     * Forecast remaining days (per-milestone survey-based extrapolation).
     * Matches TG bot's calculateSMREfficiencySummary / calculatePNREfficiencySummary approach.
     * Uses getPerMilestoneFactDays() for correct fact values (no /8 bug).
     */
    public function calculateForecastRemainingDays(string $projectCode, string $workType): ?float
    {
        $db = $this->db;
        $validNames = $this->getValidMilestoneNames($workType);
        $stmt = $db->prepare("
            SELECT milestone_name, planned_hours, is_not_applicable, unit
            FROM project_milestones WHERE project_code = ? AND work_type = ?
        ");
        $stmt->bind_param('ss', $projectCode, $workType);
        $stmt->execute();
        $result = $stmt->get_result();

        $milestonesPlan = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($validNames) && !in_array($row['milestone_name'], $validNames)) continue;
            if (!empty($row['is_not_applicable'])) continue;
            $plan = (float)($row['planned_hours'] ?? 0);
            if ($plan <= 0) continue;
            if ($row['unit'] === 'hours') $plan = ceil($plan / 8);
            $milestonesPlan[$row['milestone_name']] = $plan;
        }
        $stmt->close();

        if (empty($milestonesPlan)) return null;

        // Get per-milestone fact days using TG bot query patterns
        $perMilestoneFact = $this->getPerMilestoneFactDays($projectCode, $workType);
        $estimates = $this->milestoneService->getProjectMilestoneEstimates($projectCode, $workType);

        $forecastRemainingDays = 0;
        foreach ($milestonesPlan as $name => $plan) {
            $fact = $perMilestoneFact[$name] ?? 0;

            $estimate = $estimates[$name] ?? null;
            $estimatedPercent = $estimate ? (float)$estimate['estimated_percent'] : 0;

            if ($estimatedPercent >= 100) {
                // Completed
            } elseif ($fact > 0 && $estimate) {
                $effectiveDone = ($estimatedPercent * $plan) / 100;
                $remainingPlan = $plan - $effectiveDone;
                if ($effectiveDone > 0 && $remainingPlan > 0) {
                    $forecastRemainingDays += $remainingPlan / ($effectiveDone / $fact);
                } elseif ($remainingPlan > 0) {
                    $forecastRemainingDays += $remainingPlan;
                }
            } elseif ($fact > 0) {
                $effectiveDone = min($fact, $plan);
                $remainingPlan = $plan - $effectiveDone;
                if ($effectiveDone > 0 && $remainingPlan > 0) {
                    $forecastRemainingDays += $remainingPlan / ($effectiveDone / $fact);
                } elseif ($remainingPlan > 0) {
                    $forecastRemainingDays += $remainingPlan;
                }
            } else {
                $forecastRemainingDays += $plan;
            }
        }

        return $forecastRemainingDays;
    }

    /**
     * Forecast completion date using forecastRemainingDays + personnel count.
     */
    public function calculateForecastCompletionDate(
        string $projectCode, string $workType, ?float $progress, ?float $forecastRemainingDays
    ): string {
        if ($progress !== null && $progress >= 100) {
            return (new \DateTime())->format('d.m.Y');
        }
        if ($forecastRemainingDays === null || $forecastRemainingDays <= 0) {
            return '';
        }

        $funcDirection = $this->workTypeToFuncDirection($workType);
        if (empty($funcDirection)) return '';

        $db = $this->db;
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT JSON_EXTRACT(od.data, '$.fio')) as unique_specialists
            FROM ozna_tabel ot
            INNER JOIN ozna_data od ON ot.user_id = od.user_id
            WHERE ot.message LIKE CONCAT('%', ?, '%')
            AND JSON_EXTRACT(od.data, '$.functional_direction') = ?
            AND ot.dt >= DATE_SUB(NOW(), INTERVAL 3 DAY)
        ");
        $stmt->bind_param('ss', $projectCode, $funcDirection);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $personnelCount = (int)($row['unique_specialists'] ?? 0);
        if ($personnelCount <= 0) return '';

        $calendarDaysRemaining = (int)ceil($forecastRemainingDays / $personnelCount);
        $forecastDate = new \DateTime();
        $forecastDate->modify("+{$calendarDaysRemaining} days");
        return $forecastDate->format('d.m.Y');
    }

    /**
     * СМР hybrid Mode A/B forecast (from ШМР milestones).
     * Faithfully replicates DashboardExportController::calculateCMRForecast().
     */
    public function calculateCMRForecast(string $projectCode): ?array
    {
        $db = $this->db;

        $cmrToSmrMapping = [
            'Электромонтажные работы' => ['Электрооборудование'],
            'Слесарные работы' => ['Технологическое оборудование', 'Отопление и вентиляция'],
            'Сварочные работы' => ['Технологическое оборудование'],
            'Работы по теплоизоляции' => ['Технологическое оборудование', 'Отопление и вентиляция'],
        ];

        $cmrWorkTypes = array_keys($cmrToSmrMapping);

        // Get СМР plan per work type from project_milestones
        $cmrWorkTypePlans = [];
        foreach ($cmrWorkTypes as $wt) {
            $stmt = $db->prepare("
                SELECT SUM(CASE WHEN unit='hours' THEN CEIL(planned_hours/8) ELSE planned_hours END) as total
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'СМР'
                AND milestone_name LIKE CONCAT(?, '%')
                AND is_not_applicable = 0 AND planned_hours > 0
            ");
            $stmt->bind_param('ss', $projectCode, $wt);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $cmrWorkTypePlans[$wt] = (float)($row['total'] ?? 0);
            $stmt->close();
        }

        // Get СМР fact per work type
        $cmrActivities = [
            'Электромонтажные работы' => ['Электромонтажные работы'],
            'Слесарные работы' => ['Слесарные работы'],
            'Сварочные работы' => ['Сварочные работы'],
            'Работы по теплоизоляции' => ['Работы по теплоизоляции'],
        ];
        $cmrWorkTypeFacts = [];
        foreach ($cmrWorkTypes as $wt) {
            $patterns = $cmrActivities[$wt] ?? [];
            if (empty($patterns)) { $cmrWorkTypeFacts[$wt] = 0; continue; }
            $likeConds = [];
            foreach ($patterns as $p) {
                $likeConds[] = "ot.message LIKE '%" . $db->real_escape_string($p) . "%'";
            }
            $filter = '(' . implode(' OR ', $likeConds) . ')';
            $sql = "SELECT COUNT(DISTINCT ot.id) as cnt FROM ozna_tabel ot
                    INNER JOIN ozna_data od ON ot.user_id = od.user_id
                    WHERE ot.message LIKE CONCAT('%', ?, '%') AND {$filter}
                    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада'";
            $stmt = $db->prepare($sql);
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $cmrWorkTypeFacts[$wt] = (float)($row['cnt'] ?? 0);
            $stmt->close();
        }

        $totalForecastRemaining = 0;
        $totalModeB_Progress = 0;
        $totalModeB_Weight = 0;
        $hasAnyData = false;

        foreach ($cmrToSmrMapping as $cmrWorkType => $relatedSMRMilestones) {
            $cmrFact = $cmrWorkTypeFacts[$cmrWorkType] ?? 0;
            $cmrPlan = $cmrWorkTypePlans[$cmrWorkType] ?? 0;

            $placeholders = implode(',', array_fill(0, count($relatedSMRMilestones), '?'));
            $sql = "
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'ШМР'
                AND milestone_name IN ({$placeholders})
            ";
            $stmt = $db->prepare($sql);
            $types = 's' . str_repeat('s', count($relatedSMRMilestones));
            $params = array_merge([$projectCode], $relatedSMRMilestones);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $milestonePlans = [];
            while ($row = $result->fetch_assoc()) {
                if (!$row['is_not_applicable'] && $row['planned_hours'] > 0) {
                    $planDays = $row['unit'] === 'hours' ? ceil($row['planned_hours'] / 8) : $row['planned_hours'];
                    $milestonePlans[$row['milestone_name']] = $planDays;
                }
            }
            $stmt->close();

            if (empty($milestonePlans)) {
                if ($cmrPlan > 0 || $cmrFact > 0) {
                    $hasAnyData = true;
                    $totalForecastRemaining += $this->calculateModeARemaining($cmrFact, $cmrPlan);
                }
                continue;
            }

            $hasEstimates = false;
            $milestoneProgress = [];

            foreach ($milestonePlans as $milestoneName => $plan) {
                // Check confirmed
                $confirmStmt = $db->prepare("
                    SELECT COUNT(*) as cnt FROM ozna_tabel ot
                    LEFT JOIN ozna_data od ON ot.user_id = od.user_id
                    WHERE ot.message LIKE CONCAT('%', ?, '%')
                    AND ot.message LIKE '%Этап шеф-монтажа завершен%'
                    AND ot.message LIKE CONCAT('%', ?, '%')
                    AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         OR ot.message LIKE '%[Конструктор]%')
                ");
                $confirmStmt->bind_param('ss', $projectCode, $milestoneName);
                $confirmStmt->execute();
                $confirmRow = $confirmStmt->get_result()->fetch_assoc();
                $isConfirmed = ($confirmRow['cnt'] ?? 0) > 0;
                $confirmStmt->close();

                if ($isConfirmed) {
                    $milestoneProgress[$milestoneName] = 100;
                    $hasEstimates = true;
                } else {
                    $estStmt = $db->prepare("
                        SELECT estimated_percent FROM milestone_progress_estimates
                        WHERE project_code = ? AND milestone_name = ? AND work_type = 'ШМР'
                        ORDER BY created_at DESC LIMIT 1
                    ");
                    $estStmt->bind_param('ss', $projectCode, $milestoneName);
                    $estStmt->execute();
                    $estRow = $estStmt->get_result()->fetch_assoc();
                    $estStmt->close();

                    if ($estRow) {
                        $milestoneProgress[$milestoneName] = (float)$estRow['estimated_percent'];
                        $hasEstimates = true;
                    } else {
                        $milestoneProgress[$milestoneName] = null;
                    }
                }
            }

            $hasAnyData = true;

            if ($hasEstimates) {
                $workTypeWeightedProgress = 0;
                $workTypePlan = 0;
                foreach ($milestonePlans as $milestoneName => $plan) {
                    $progress = $milestoneProgress[$milestoneName] ?? 0;
                    $workTypeWeightedProgress += $progress * $plan;
                    $workTypePlan += $plan;
                }
                $weightedProgress = $workTypePlan > 0 ? $workTypeWeightedProgress / $workTypePlan : 0;

                if ($weightedProgress >= 100) {
                    $remaining = 0;
                } elseif ($weightedProgress > 0 && $cmrFact > 0) {
                    $remaining = round($cmrFact * (100 / $weightedProgress - 1), 1);
                } else {
                    $remaining = $cmrPlan > 0 ? $cmrPlan : 0;
                }
                $totalForecastRemaining += $remaining;
                $totalModeB_Progress += $weightedProgress * $workTypePlan;
                $totalModeB_Weight += $workTypePlan;
            } else {
                $totalForecastRemaining += $this->calculateModeARemaining($cmrFact, $cmrPlan);
            }
        }

        if (!$hasAnyData) return null;

        return [
            'forecastRemainingDays' => $totalForecastRemaining,
            'overallProgress' => $totalModeB_Weight > 0
                ? round($totalModeB_Progress / $totalModeB_Weight, 1) : null,
        ];
    }

    // ── Helper Methods ────────────────────────────────────────

    /**
     * Get per-milestone details for the JSON column.
     */
    private function getMilestoneDetails(string $projectCode, string $workType): array
    {
        $db = $this->db;

        // Get per-milestone fact days from tabel (always available)
        $perMilestoneFact = $this->getPerMilestoneFactDays($projectCode, $workType);

        // Get plan data from project_milestones (may be empty)
        $stmt = $db->prepare("
            SELECT milestone_name, planned_hours, is_not_applicable, unit
            FROM project_milestones WHERE project_code = ? AND work_type = ?
            ORDER BY milestone_name
        ");
        $stmt->bind_param('ss', $projectCode, $workType);
        $stmt->execute();
        $result = $stmt->get_result();

        $planData = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['is_not_applicable'])) continue;
            $plan = (float)($row['planned_hours'] ?? 0);
            if ($row['unit'] === 'hours' && $plan > 0) $plan = ceil($plan / 8);
            $planData[$row['milestone_name']] = $plan;
        }
        $stmt->close();

        // Get survey estimates
        $estimates = $this->milestoneService->getProjectMilestoneEstimates($projectCode, $workType);

        // Batch-query confirmation status
        $confirmedNames = $this->getConfirmedMilestoneNames($projectCode, $workType, $estimates);

        // Build milestone list: include all milestones that have fact > 0 OR plan > 0
        $allNames = array_unique(array_merge(array_keys($perMilestoneFact), array_keys($planData)));

        $details = [];
        foreach ($allNames as $name) {
            $planDays = $planData[$name] ?? 0;
            $factDays = round($perMilestoneFact[$name] ?? 0, 1);

            if ($planDays <= 0 && $factDays <= 0) continue;

            $estimate = $estimates[$name] ?? null;
            $progress = $estimate ? (float)$estimate['estimated_percent'] : 0;

            $efficiency = null;
            if ($factDays > 0 && $progress > 0 && $planDays > 0) {
                $effectiveDone = ($progress * $planDays) / 100;
                if ($effectiveDone > 0) {
                    $efficiency = round(($effectiveDone / $factDays) * 100, 1);
                }
            }

            $details[] = [
                'name' => $name,
                'plan_days' => $planDays,
                'fact_days' => $factDays,
                'progress' => $progress,
                'efficiency' => $efficiency,
                'is_confirmed' => in_array($name, $confirmedNames),
            ];
        }

        return $details;
    }

    /**
     * Get list of confirmed milestone names for a project+workType.
     * ШМР: checks ozna_tabel for "Этап шеф-монтажа завершен" messages.
     * ПНР: checks ozna_tabel for "Этап наладки завершен" messages.
     * СМР: checks milestone_progress_estimates for estimated_percent >= 100.
     */
    private function getConfirmedMilestoneNames(string $projectCode, string $workType, array $estimates): array
    {
        $db = $this->db;
        $confirmed = [];

        if ($workType === 'ШМР') {
            $stmt = $db->prepare("
                SELECT DISTINCT ot.message FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message LIKE '%Этап шеф-монтажа завершен%'
                AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                     OR ot.message LIKE '%[Конструктор]%')
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row['message'];
            }
            $stmt->close();

            // Check each valid milestone name against the messages
            $validNames = $this->getValidMilestoneNames($workType);
            foreach ($validNames as $name) {
                foreach ($messages as $msg) {
                    if (mb_strpos($msg, $name) !== false) {
                        $confirmed[] = $name;
                        break;
                    }
                }
            }
        } elseif ($workType === 'ПНР') {
            $stmt = $db->prepare("
                SELECT DISTINCT ot.message FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message LIKE '%Этап наладки завершен%'
                AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                     OR ot.message LIKE '%[Конструктор]%')
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row['message'];
            }
            $stmt->close();

            $validNames = $this->getPnrMilestoneNames();
            foreach ($validNames as $name) {
                foreach ($messages as $msg) {
                    if (mb_strpos($msg, $name) !== false) {
                        $confirmed[] = $name;
                        break;
                    }
                }
            }
        } elseif ($workType === 'СМР') {
            // СМР: confirmed if estimated_percent >= 100
            foreach ($estimates as $name => $est) {
                if (isset($est['estimated_percent']) && (float)$est['estimated_percent'] >= 100) {
                    $confirmed[] = $name;
                }
            }
        }

        return $confirmed;
    }

    private function hasMilestones(string $projectCode, string $workType): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as cnt FROM project_milestones 
            WHERE project_code = ? AND work_type = ?
        ");
        $stmt->bind_param('ss', $projectCode, $workType);
        $stmt->execute();
        $cnt = $stmt->get_result()->fetch_assoc()['cnt'] > 0;
        $stmt->close();
        return $cnt;
    }

    private function calculateModeARemaining(float $fact, float $plan): float
    {
        if ($fact > 0 && $fact < $plan) return $plan - $fact;
        if ($fact >= $plan && $plan > 0) return 0;
        return $plan;
    }

    private function workTypeToFuncDirection(string $workType): string
    {
        return match ($workType) {
            'ШМР' => 'Шеф-монтаж',
            'ПНР' => 'Пусконаладка',
            'СМР' => 'Мобильная бригада',
            default => '',
        };
    }

    /**
     * Auxiliary work facts from tabel, matching TG bot's ProjectHandler queries.
     * Returns structured array with plan/fact for each auxiliary category.
     */
    private function getAuxiliaryWorkFacts(string $projectCode, string $workType, array $projectCard): array
    {
        $db = $this->db;
        $funcDirection = $this->workTypeToFuncDirection($workType);
        if (empty($funcDirection)) return [];

        $prefix = match ($workType) { 'ШМР' => 'smr', 'ПНР' => 'pnr', 'СМР' => 'cmr', default => '' };
        if (empty($prefix)) return [];

        $fd = $db->real_escape_string($funcDirection);

        // Query all auxiliary fact categories in a single query (matching TG bot patterns)
        if ($workType === 'ШМР') {
            $stmt = $db->prepare("
                SELECT
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Дорога%')
                         OR ot.message LIKE CONCAT('%', ?, '%Инструктаж%')
                         OR ot.message LIKE CONCAT('%', ?, '%Подготовка к служебной поездке%')
                         OR ot.message LIKE CONCAT('%', ?, '%Сдача авансового отчета%'))
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as mobilization,
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Разработка графика работ%')
                         OR ot.message LIKE CONCAT('%', ?, '%Разработка программы работ%'))
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as project_activity,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Исполнение обязанностей мастера%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as master_duties,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as downtime,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as rk_comments,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as warranty
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            ");
            $stmt->bind_param('sssssssssss',
                $projectCode, $projectCode, $projectCode, $projectCode,
                $projectCode, $projectCode, $projectCode,
                $projectCode, $projectCode, $projectCode, $projectCode
            );
        } elseif ($workType === 'ПНР') {
            $stmt = $db->prepare("
                SELECT
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Дорога%')
                         OR ot.message LIKE CONCAT('%', ?, '%Инструктаж%')
                         OR ot.message LIKE CONCAT('%', ?, '%Подготовка к служебной поездке%')
                         OR ot.message LIKE CONCAT('%', ?, '%Сдача авансового отчета%'))
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as mobilization,
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Разработка графика работ%')
                         OR ot.message LIKE CONCAT('%', ?, '%Разработка программы работ%'))
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as project_activity,
                    0 as master_duties,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as downtime,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as rk_comments,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as warranty
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            ");
            $stmt->bind_param('ssssssssss',
                $projectCode, $projectCode, $projectCode, $projectCode,
                $projectCode, $projectCode,
                $projectCode, $projectCode, $projectCode, $projectCode
            );
        } elseif ($workType === 'СМР') {
            // For СМР, rk_comments and warranty are already tracked as sub-milestones
            // (Устранение замечаний РК, Работы по гарантии), so only count downtime here.
            $stmt = $db->prepare("
                SELECT
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Дорога%')
                         OR ot.message LIKE CONCAT('%', ?, '%Инструктаж%')
                         OR ot.message LIKE CONCAT('%', ?, '%Подготовка к служебной поездке%')
                         OR ot.message LIKE CONCAT('%', ?, '%Сдача авансового отчета%'))
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as mobilization,
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Разработка графика работ%')
                         OR ot.message LIKE CONCAT('%', ?, '%Разработка программы работ%'))
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as project_activity,
                    0 as master_duties,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = '{$fd}'
                         THEN ot.id END) as downtime,
                    0 as rk_comments,
                    0 as warranty
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            ");
            $stmt->bind_param('ssssssss',
                $projectCode, $projectCode, $projectCode, $projectCode,
                $projectCode, $projectCode,
                $projectCode, $projectCode
            );
        } else {
            return [];
        }

        $stmt->execute();
        $facts = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $mobFact = (int)($facts['mobilization'] ?? 0);
        $projActFact = (int)($facts['project_activity'] ?? 0);
        $masterDutiesFact = (int)($facts['master_duties'] ?? 0);
        $downtimeFact = (int)($facts['downtime'] ?? 0);
        $rkCommentsFact = (int)($facts['rk_comments'] ?? 0);
        $warrantyFact = (int)($facts['warranty'] ?? 0);

        // Calculate NVOR (works not in scope) for ШМР — derived from milestone activity patterns
        $nvorFact = 0;
        if ($workType === 'ШМР') {
            $structure = $this->getSmrMilestoneStructure();
            $nvorConditions = [];
            foreach ($structure as $subs) {
                foreach ($subs as $activities) {
                    foreach ($activities as $activity) {
                        if (strpos($activity, 'Работы по графику (') === 0) {
                            $nvorPattern = str_replace('Работы по графику (', 'Работы неучтенные в ВОР (', $activity);
                            $nvorPattern = str_replace('/', '%', $nvorPattern);
                            $nvorConditions[] = "ot.message LIKE '%" . $db->real_escape_string($nvorPattern) . "%'";
                        }
                    }
                }
            }
            if (!empty($nvorConditions)) {
                $nvorFilter = '(' . implode(' OR ', $nvorConditions) . ')';
                $nvorSql = "
                    SELECT COUNT(DISTINCT ot.id) as nvor_count
                    FROM ozna_tabel ot
                    INNER JOIN ozna_data od ON ot.user_id = od.user_id
                    WHERE ot.message LIKE CONCAT('%', ?, '%')
                    AND {$nvorFilter}
                    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                ";
                $nvorStmt = $db->prepare($nvorSql);
                $nvorStmt->bind_param('s', $projectCode);
                $nvorStmt->execute();
                $nvorRow = $nvorStmt->get_result()->fetch_assoc();
                $nvorFact = (int)($nvorRow['nvor_count'] ?? 0);
                $nvorStmt->close();
            }
        }

        // Plan values from project_cards
        $mobPlan = (int)($projectCard["{$prefix}_mobilization"] ?? 0);
        $projActPlan = (int)($projectCard["{$prefix}_project_activity"] ?? 0);
        $masterDutiesPlan = ($workType === 'ШМР') ? (int)($projectCard['smr_master_duties'] ?? 0) : 0;

        // Calculate unaccounted: totalFact - all known categories (including NVOR)
        $totalFactDays = $this->getTotalFactDays($projectCode, $workType);
        $milestoneFact = $this->getMilestonesFactDays($projectCode, $workType);
        $knownFact = $milestoneFact + $nvorFact + $mobFact + $projActFact + $masterDutiesFact
                   + $downtimeFact + $rkCommentsFact + $warrantyFact;
        $unaccountedFact = max(0, (int)($totalFactDays - $knownFact));

        // Build result in display order
        // For СМР: only downtime (rk_comments, warranty, nvor are sub-milestones; no unaccounted)
        if ($workType === 'СМР') {
            $result = [];
            if ($mobFact > 0 || $mobPlan > 0) {
                $result['mobilization'] = ['plan' => $mobPlan, 'fact' => $mobFact];
            }
            if ($projActFact > 0 || $projActPlan > 0) {
                $result['project_activity'] = ['plan' => $projActPlan, 'fact' => $projActFact];
            }
            if ($downtimeFact > 0) {
                $result['downtime'] = ['plan' => 0, 'fact' => $downtimeFact];
            }
            return $result;
        }

        $result = [
            'mobilization' => ['plan' => $mobPlan, 'fact' => $mobFact],
            'project_activity' => ['plan' => $projActPlan, 'fact' => $projActFact],
        ];

        if ($workType === 'ШМР') {
            $result['master_duties'] = ['plan' => $masterDutiesPlan, 'fact' => $masterDutiesFact];
        }

        if ($nvorFact > 0) {
            $result['nvor'] = ['plan' => 0, 'fact' => $nvorFact];
        }
        if ($downtimeFact > 0) {
            $result['downtime'] = ['plan' => 0, 'fact' => $downtimeFact];
        }
        if ($rkCommentsFact > 0) {
            $result['rk_comments'] = ['plan' => 0, 'fact' => $rkCommentsFact];
        }
        if ($warrantyFact > 0) {
            $result['warranty'] = ['plan' => 0, 'fact' => $warrantyFact];
        }
        // Unaccounted always last
        if ($unaccountedFact > 0) {
            $result['unaccounted'] = ['plan' => 0, 'fact' => $unaccountedFact];
        }

        return $result;
    }

    private function cleanupStaleRows(array $validCodes): void
    {
        if (empty($validCodes)) return;
        $db = $this->db;
        $placeholders = implode(',', array_fill(0, count($validCodes), '?'));
        $types = str_repeat('s', count($validCodes));
        $stmt = $db->prepare("DELETE FROM metrics_cache WHERE project_code NOT IN ({$placeholders})");
        $stmt->bind_param($types, ...$validCodes);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Resolve BN group key for a business_unit_name.
     */
    public static function resolveBNGroup(?string $bnName): string
    {
        if (empty($bnName)) return 'unsorted';
        foreach (self::BN_GROUPS as $group => $names) {
            if (in_array($bnName, $names)) return $group;
        }
        return 'unsorted';
    }
}
