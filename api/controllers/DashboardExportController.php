<?php

namespace OznaApi\Controllers;

/**
 * Dashboard Export Controller
 * Generates Excel exports for management dashboards.
 * Access: Superadmin only (access_level >= 4).
 */
class DashboardExportController
{
    /**
     * GET /dashboard/exports/high-level-status
     * Generates "Верхнеуровневый статус" Excel report.
     * 
     * Columns: БН | Веха | ID Проекта | Завершение (план, прогресс, прогноз) |
     *          Трудозатраты (план, факт, прогноз) | Эффективность (текущая, прогнозная)
     * 
     * Filter: Active projects with milestone activity since 1st of previous month.
     */
    public function highLevelStatus(): void
    {
        // Require superadmin
        $authMw = $GLOBALS['auth_middleware'] ?? null;
        if ($authMw && !$authMw->requireLevel(4)) {
            return;
        }

        $botRoot = dirname(dirname(__DIR__));
        require_once $botRoot . '/vendor/autoload.php';

        if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
            http_response_code(500);
            echo json_encode(['error' => 'PhpSpreadsheet не установлен'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $db = getDb();

        // ── 1. Load all active metrics from cache in one query ──
        // metrics_cache is pre-populated by the cron job (MetricsCacheService).
        // This is the single source of truth for dashboard export data.

        $excludedProjects = ['ХХХ'];
        $excludeList = implode(',', array_map(function($c) use ($db) {
            return "'" . $db->real_escape_string($c) . "'";
        }, $excludedProjects));

        $cacheResult = $db->query("
            SELECT project_code, work_type, planned_completion, planned_hours_days, fact_hours_days,
                   stage_progress, current_efficiency, forecast_efficiency,
                   forecast_remaining_days, forecast_completion,
                   customer_name, business_unit_name
            FROM metrics_cache
            WHERE project_code NOT IN ({$excludeList})
            AND (planned_hours_days > 0 OR fact_hours_days > 0)
            ORDER BY project_code, FIELD(work_type, 'СМР', 'ШМР', 'ПНР')
        ");

        if (!$cacheResult) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка запроса к metrics_cache'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // ── 2. Load project card data for customer/BN fallback ──
        $projectCardsResult = $db->query("
            SELECT pc.project_code, c.name AS customer_name, bu.name AS business_unit_name
            FROM project_cards pc
            LEFT JOIN customers c ON pc.customer_id = c.id
            LEFT JOIN business_units bu ON pc.business_unit_id = bu.id
        ");
        $projects = [];
        while ($row = $projectCardsResult->fetch_assoc()) {
            $projects[$row['project_code']] = $row;
        }
        $projectCardsResult->free();

        // ── 3. Build rows ──

        $rows = [];

        while ($cached = $cacheResult->fetch_assoc()) {
            $code = $cached['project_code'];
            $project = $projects[$code] ?? null;

            $planDays = $cached['planned_hours_days'] ?? 0;
            $factDays = $cached['fact_hours_days'] ?? 0;

            // Заказчик
            $customer = $cached['customer_name'] ?? ($project['customer_name'] ?? '');

            // БН
            $bn = $cached['business_unit_name'] ?? ($project['business_unit_name'] ?? '');

            // Format values for Excel
            $progress = $cached['stage_progress'] ?? null;
            $currentEfficiency = $cached['current_efficiency'] ?? null;
            $forecastEfficiency = $cached['forecast_efficiency'] ?? null;
            $forecastRemainingDays = $cached['forecast_remaining_days'] ?? null;
            $forecastCompletion = $cached['forecast_completion'] ?? '';

            // Прогноз трудозатрат
            $forecastDays = null;
            if ($forecastRemainingDays !== null && $factDays > 0) {
                $forecastDays = round($factDays + $forecastRemainingDays, 1);
            }

            // Planned completion date formatting
            $plannedCompletion = '';
            if (!empty($cached['planned_completion'])) {
                $ts = strtotime($cached['planned_completion']);
                if ($ts) {
                    $plannedCompletion = date('d.m.Y', $ts);
                } else {
                    $plannedCompletion = $cached['planned_completion'];
                }
            }

            $rows[] = [
                'customer' => $customer,
                'bn' => $bn,
                'veha' => $cached['work_type'],
                'project_id' => $code,
                'completion_plan' => $plannedCompletion,
                'completion_progress' => $progress !== null ? str_replace('.', ',', $progress) . '%' : '',
                'completion_forecast' => $forecastCompletion,
                'labor_plan' => $planDays > 0 ? $planDays : '',
                'labor_fact' => $factDays > 0 ? $factDays : '',
                'labor_forecast' => $forecastDays !== null ? str_replace('.', ',', $forecastDays) : '',
                'efficiency_current' => $currentEfficiency !== null ? str_replace('.', ',', $currentEfficiency) . '%' : '',
                'efficiency_forecast' => $forecastEfficiency !== null ? str_replace('.', ',', $forecastEfficiency) . '%' : '',
            ];
        }
        $cacheResult->free();

        if (empty($rows)) {
            http_response_code(200);
            echo json_encode([
                'error' => 'Нет данных для формирования отчёта',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // ── 4. Generate Excel ──

        $filePath = $this->generateExcel($rows);

        // ── 5. Send file ──

        $filename = 'Верхнеуровневый_статус_' . date('Y-m-d') . '.xlsx';
        $filenameAscii = 'dashboard_high_level_status_' . date('Y-m-d') . '.xlsx';
        $filenameEncoded = rawurlencode($filename);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filenameAscii}\"; filename*=UTF-8''{$filenameEncoded}");
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, no-store, must-revalidate');

        readfile($filePath);
        unlink($filePath);
        exit;
    }

    /**
     * Calculate weighted-average progress % for a stage (ШМР or ПНР).
     * Uses plan hours as weights for each milestone's estimated_percent.
     */
    private function calculateStageProgress(\mysqli $db, $milestoneService, string $projectCode, string $workType): ?float
    {
        // Get all milestones with plan hours
        $stmt = $db->prepare("
            SELECT milestone_name, planned_hours, is_not_applicable, unit
            FROM project_milestones
            WHERE project_code = ? AND work_type = ?
        ");
        $stmt->bind_param('ss', $projectCode, $workType);
        $stmt->execute();
        $result = $stmt->get_result();

        $milestones = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['is_not_applicable'])) continue;
            $plan = (float)$row['planned_hours'];
            if ($plan <= 0) continue;
            if ($row['unit'] === 'hours') {
                $plan = ceil($plan / 8);
            }
            $milestones[$row['milestone_name']] = $plan;
        }
        $stmt->close();

        if (empty($milestones)) return null;

        // Get estimates
        $estimates = $milestoneService->getProjectMilestoneEstimates($projectCode, $workType);

        $totalWeightedProgress = 0;
        $totalPlan = 0;
        foreach ($milestones as $name => $plan) {
            $totalPlan += $plan;
            $estimate = $estimates[$name] ?? null;
            $percent = $estimate ? (float)$estimate['estimated_percent'] : 0;
            $totalWeightedProgress += $percent * $plan;
        }

        if ($totalPlan <= 0) return null;

        return round($totalWeightedProgress / $totalPlan, 1);
    }

    /**
     * Calculate forecast completion date based on planned date and current pace.
     */
    /**
     * Calculate forecasted completion date based on forecastRemainingDays and personnel count.
     * Matches TG bot logic: daysRemaining = forecastRemainingDays / personnelCount
     */
    private function calculateForecastCompletionDate(
        string $plannedCompletion,
        ?float $progress,
        \mysqli $db,
        $milestoneService,
        string $projectCode,
        string $workType,
        ?float $forecastRemainingDays = null
    ): string {
        // Need either progress >= 100 or forecastRemainingDays to compute
        if ($progress !== null && $progress >= 100) {
            return (new \DateTime())->format('d.m.Y');
        }

        if ($forecastRemainingDays === null || $forecastRemainingDays <= 0) {
            return '';
        }

        // Get personnel count: unique specialists working on this project+workType in last 3 days
        $funcDirection = '';
        if ($workType === 'ШМР') {
            $funcDirection = 'Шеф-монтаж';
        } elseif ($workType === 'ПНР') {
            $funcDirection = 'Пусконаладка';
        } elseif ($workType === 'СМР') {
            $funcDirection = 'Мобильная бригада';
        }

        if (empty($funcDirection)) return '';

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

        if ($personnelCount <= 0) {
            return '';
        }

        $calendarDaysRemaining = (int)ceil($forecastRemainingDays / $personnelCount);

        $forecastDate = new \DateTime();
        $forecastDate->modify("+{$calendarDaysRemaining} days");

        return $forecastDate->format('d.m.Y');
    }

    /**
     * Get milestones plan total in person-days.
     * Converts hours to days (ceil(hours/8)), keeps days as-is.
     */
    private function getMilestonesPlanDays(\mysqli $db, string $projectCode, string $workType): float
    {
        $stmt = $db->prepare("
            SELECT planned_hours, unit, is_not_applicable
            FROM project_milestones
            WHERE project_code = ? AND work_type = ?
        ");
        $stmt->bind_param('ss', $projectCode, $workType);
        $stmt->execute();
        $result = $stmt->get_result();

        $totalDays = 0;
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['is_not_applicable'])) continue;
            $plan = (float)($row['planned_hours'] ?? 0);
            if ($plan <= 0) continue;
            if ($row['unit'] === 'hours') {
                $plan = ceil($plan / 8);
            }
            $totalDays += $plan;
        }
        $stmt->close();

        return $totalDays;
    }

    /**
     * Get auxiliary plan days from project_cards (mobilization, project activity, etc.).
     */
    private function getAuxiliaryPlanDays(array $projectCard, string $workType): float
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
     * Get total fact person-days from ozna_tabel for a project+stage.
     * Matches the TG bot logic: counts distinct tabel entries per functional_direction.
     */
    private function getTotalFactDays(\mysqli $db, string $projectCode, string $workType): float
    {
        $funcDirection = '';
        if ($workType === 'ШМР') {
            $funcDirection = 'Шеф-монтаж';
        } elseif ($workType === 'ПНР') {
            $funcDirection = 'Пусконаладка';
        } elseif ($workType === 'СМР') {
            $funcDirection = 'Мобильная бригада';
        }

        if (empty($funcDirection)) return 0;

        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT ot.id) as total_days
            FROM ozna_tabel ot
            INNER JOIN ozna_data od ON ot.user_id = od.user_id
            WHERE ot.message LIKE CONCAT('%', ?, '%')
            AND JSON_EXTRACT(od.data, '$.functional_direction') = ?
            AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            AND ot.message NOT LIKE '%Этап наладки завершен%'
            AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
            AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
        ");
        $stmt->bind_param('ss', $projectCode, $funcDirection);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (float)($row['total_days'] ?? 0);
    }

    /**
     * Calculate forecast remaining actual days for ШМР/ПНР.
     * Per-milestone: remaining_plan / (effective_done / fact) extrapolation.
     * Matches the logic in MilestoneProgressService::getSMRTotalEfficiency / getPNRTotalEfficiency.
     */
    private function calculateForecastRemainingDays(
        \mysqli $db, $milestoneService, string $projectCode, string $workType
    ): ?float {
        $stmt = $db->prepare("
            SELECT milestone_name, planned_hours, is_not_applicable, unit
            FROM project_milestones
            WHERE project_code = ? AND work_type = ?
        ");
        $stmt->bind_param('ss', $projectCode, $workType);
        $stmt->execute();
        $result = $stmt->get_result();

        $milestonesPlan = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['is_not_applicable'])) continue;
            $plan = (float)($row['planned_hours'] ?? 0);
            if ($plan <= 0) continue;
            if ($row['unit'] === 'hours') {
                $plan = ceil($plan / 8);
            }
            $milestonesPlan[$row['milestone_name']] = $plan;
        }
        $stmt->close();

        if (empty($milestonesPlan)) return null;

        $estimates = $milestoneService->getProjectMilestoneEstimates($projectCode, $workType);

        $forecastRemainingDays = 0;
        foreach ($milestonesPlan as $name => $plan) {
            $factHours = $milestoneService->getMilestoneFactHours($projectCode, $name, $workType);
            $fact = $factHours / 8; // Convert hours to days

            $estimate = $estimates[$name] ?? null;
            $estimatedPercent = $estimate ? (float)$estimate['estimated_percent'] : 0;

            if ($estimatedPercent >= 100) {
                // Completed - no remaining
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
                // Not started - assume 100% efficiency
                $forecastRemainingDays += $plan;
            }
        }

        return $forecastRemainingDays;
    }

    /**
     * Calculate СМР forecast using hybrid Mode A/B logic.
     * Mode A (efficiency-based): no ШМР estimates, uses plan vs fact directly.
     * Mode B (dynamic): has ШМР estimates, uses weighted milestone progress.
     * Returns forecastRemainingDays and optional overallProgress.
     */
    private function calculateCMRForecast(\mysqli $db, string $projectCode): ?array
    {
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

        // Get СМР fact per work type using tabel activity patterns
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

            // Get ШМР milestone plans
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

            // If no ШМР plans, try Mode A with СМР plan/fact
            if (empty($milestonePlans)) {
                if ($cmrPlan > 0 || $cmrFact > 0) {
                    $hasAnyData = true;
                    $totalForecastRemaining += $this->calculateModeARemaining($cmrFact, $cmrPlan);
                }
                continue;
            }

            // Check each milestone for estimates/confirmations
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
                // Mode B: weighted progress from ШМР estimates
                $workTypeWeightedProgress = 0;
                $workTypePlan = 0;
                foreach ($milestonePlans as $milestoneName => $plan) {
                    $progress = $milestoneProgress[$milestoneName] ?? 0; // unassessed = 0% in Mode B
                    $workTypeWeightedProgress += $progress * $plan;
                    $workTypePlan += $plan;
                }
                $weightedProgress = $workTypePlan > 0 ? $workTypeWeightedProgress / $workTypePlan : 0;

                // Derive forecastRemainingDays from progress
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
                // Mode A: plan vs fact
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

    /**
     * Mode A: calculate remaining days from plan vs fact (100% efficiency)
     */
    private function calculateModeARemaining(float $fact, float $plan): float
    {
        if ($fact > 0 && $fact < $plan) {
            return $plan - $fact;
        } elseif ($fact >= $plan && $plan > 0) {
            return 0;
        }
        return $plan;
    }

    /**
     * Generate the Excel file matching the template structure.
     */
    private function generateExcel(array $rows): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Данные для заполнения');

        // ── Header row 1: group headers ──
        $sheet->setCellValue('A1', 'Заказчик');
        $sheet->setCellValue('B1', 'БН');
        $sheet->setCellValue('C1', 'Веха');
        $sheet->setCellValue('D1', 'ID Проекта');
        $sheet->setCellValue('E1', 'Завершение');
        $sheet->setCellValue('H1', 'Трудозатраты');
        $sheet->setCellValue('K1', 'Эффективность');

        $sheet->mergeCells('E1:G1');
        $sheet->mergeCells('H1:J1');
        $sheet->mergeCells('K1:L1');

        // Merge A1, B1, C1, D1 across two rows
        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');
        $sheet->mergeCells('D1:D2');

        // ── Header row 2: sub-headers ──
        $sheet->setCellValue('E2', 'план');
        $sheet->setCellValue('F2', 'прогресс');
        $sheet->setCellValue('G2', 'прогноз');
        $sheet->setCellValue('H2', 'план');
        $sheet->setCellValue('I2', 'факт');
        $sheet->setCellValue('J2', 'прогноз');
        $sheet->setCellValue('K2', 'текущая');
        $sheet->setCellValue('L2', 'прогнозная');

        // ── Styles ──
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2F5496'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];

        $subHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];

        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
        $sheet->getStyle('E2:L2')->applyFromArray($subHeaderStyle);
        // Also style A1:D2 merged cells
        $sheet->getStyle('A1:D2')->applyFromArray($headerStyle);

        // Row heights
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(25);

        // Column widths
        $colWidths = ['A' => 16, 'B' => 10, 'C' => 8, 'D' => 14, 'E' => 14, 'F' => 12, 'G' => 14, 'H' => 12, 'I' => 12, 'J' => 12, 'K' => 12, 'L' => 14];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // ── Data rows ──
        $dataStyle = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'D9E2F3'],
                ],
            ],
        ];

        $rowNum = 3;
        foreach ($rows as $i => $row) {
            $sheet->setCellValue("A{$rowNum}", $row['customer']);
            $sheet->setCellValue("B{$rowNum}", $row['bn']);
            $sheet->setCellValue("C{$rowNum}", $row['veha']);
            $sheet->setCellValue("D{$rowNum}", $row['project_id']);
            $sheet->setCellValue("E{$rowNum}", $row['completion_plan']);
            $sheet->setCellValue("F{$rowNum}", $row['completion_progress']);
            $sheet->setCellValue("G{$rowNum}", $row['completion_forecast']);
            $sheet->setCellValue("H{$rowNum}", $row['labor_plan']);
            $sheet->setCellValue("I{$rowNum}", $row['labor_fact']);
            $sheet->setCellValue("J{$rowNum}", $row['labor_forecast']);
            $sheet->setCellValue("K{$rowNum}", $row['efficiency_current']);
            $sheet->setCellValue("L{$rowNum}", $row['efficiency_forecast']);

            $sheet->getStyle("A{$rowNum}:L{$rowNum}")->applyFromArray($dataStyle);

            // Alternating row colors
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$rowNum}:L{$rowNum}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D6E4F0');
            } else {
                $sheet->getStyle("A{$rowNum}:L{$rowNum}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E9EFF7');
            }

            // Color-code efficiency cells
            $this->applyEfficiencyColor($sheet, "K{$rowNum}", $row['efficiency_current']);
            $this->applyEfficiencyColor($sheet, "L{$rowNum}", $row['efficiency_forecast']);

            $rowNum++;
        }

        // ── Auto-filter ──
        $lastRow = $rowNum - 1;
        $sheet->setAutoFilter("A2:L{$lastRow}");

        // ── Freeze panes (freeze header rows) ──
        $sheet->freezePane('A3');

        // ── Save ──
        $tempDir = sys_get_temp_dir();
        $filePath = $tempDir . '/dashboard_export_' . uniqid() . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);
        $spreadsheet->disconnectWorksheets();

        return $filePath;
    }

    /**
     * Apply conditional color to efficiency cell.
     * Green >= 100%, Yellow 80-99%, Red < 80%
     */
    private function applyEfficiencyColor($sheet, string $cell, string $value): void
    {
        if (empty($value)) return;

        $numValue = (float)str_replace('%', '', $value);
        if ($numValue >= 100) {
            $color = '92D050'; // green
        } elseif ($numValue >= 80) {
            $color = 'FFC000'; // yellow/amber
        } else {
            $color = 'FF6B6B'; // red
        }

        $sheet->getStyle($cell)->getFont()->getColor()->setRGB(
            $numValue >= 100 ? '1B5E20' : ($numValue >= 80 ? '7F6000' : '8B0000')
        );
    }

    /**
     * GET /dashboard/exports/list
     * Returns list of available dashboard exports.
     */
    public function listExports(): void
    {
        $authMw = $GLOBALS['auth_middleware'] ?? null;
        if ($authMw && !$authMw->requireLevel(4)) {
            return;
        }

        echo json_encode([
            'exports' => [
                [
                    'id' => 'high-level-status',
                    'name' => 'Верхнеуровневый статус',
                    'description' => 'Статус по вехам активных проектов: прогресс, трудозатраты, эффективность',
                ],
            ],
        ], JSON_UNESCAPED_UNICODE);
    }
}
