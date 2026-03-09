<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

/**
 * Service for managing milestone progress surveys and estimates
 * Handles PNR and SMR milestone completion percentage tracking
 */
class MilestoneProgressService
{
    private DatabaseService $databaseService;
    
    // Маппинг activity_type на название вехи ПНР
    private const PNR_ACTIVITY_TO_MILESTONE = [
        'РабПоГр_ЭлектрОб' => 'Электрооборудование',
        'РабПоГр_ПожСигн' => 'Пожарная сигнализация',
        'РабПоГр_ОхрСигн' => 'Охранная сигнализация',
        'РабПоГр_Автом' => 'Автоматизация',
        'РабПоГр_ВентОтопл' => 'Вентиляция и отопление',
        'РабПоГр_ТехОб' => 'Технологическое оборудование',
        'РабПоГр_Метрол' => 'Метрология',
        'РежимнаяНаладка' => 'ИИ на рабочей среде',
        'КомплексноеОпробование' => 'Комплексное опробование',
    ];
    
    // Маппинг activity_type на название подвехи ШМР
    // Ключи — все варианты activity_type (длинные и короткие), значения — названия подвех из getSMRMilestoneStructure()
    private const SMR_ACTIVITY_TO_SUBMILESTONE = [
        // Подготовительные работы
        'ШМР_ВходнойКонтроль_РабПоГр' => 'Входной контроль',
        'ШМР_ВхКонтр_РабПоГр' => 'Входной контроль',
        'ШМР_ВхОднойКонтроль_РабПоГр' => 'Входной контроль',
        'ШМР_ЗавозОборудования_РабПоГр' => 'Завоз оборудования на площадку',
        'ШМР_ЗавОбор_РабПоГр' => 'Завоз оборудования на площадку',
        'ШМР_АналФунд_РабПоГр' => 'Проверка монтажной площадки',
        'ПроверкаМонтажнойПлощадки' => 'Проверка монтажной площадки',
        'ШМР_ПроверкаМонтажнойПлощадки' => 'Проверка монтажной площадки',
        
        // Монтаж блоков
        'ШМР_РасстановкаБлоков_РабПоГр' => 'Расстановка блоков',
        'ШМР_РасстБлок_РабПоГр' => 'Расстановка блоков',
        'ШМР_МонтажПлощадок_РабПоГр' => 'Монтаж площадок обслуживания',
        'ШМР_МонтПлощ_РабПоГр' => 'Монтаж площадок обслуживания',
        
        // Сборка здания
        'ШМР_МонтажЭлементовУкрытия_РабПоГр' => 'Монтаж элементов укрытия',
        'ШМР_МонтЭлУкр_РабПоГр' => 'Монтаж элементов укрытия',
        'ШМР_СборкаКровли_РабПоГр' => 'Сборка кровли',
        'ШМР_СборКровл_РабПоГр' => 'Сборка кровли',
        
        // Внутриблочный монтаж — Технологическое оборудование
        'ШМР_МонтажТрубопроводов_РабПоГр' => 'Технологическое оборудование',
        'ШМР_ГидроПневмоИспытания_РабПоГр' => 'Технологическое оборудование',
        'ШМР_МонтажТехнологическогоОборудования_РабПоГр' => 'Технологическое оборудование',
        'ШМР_МонтажЗРА_РабПоГр' => 'Технологическое оборудование',
        'ШМР_ПротяжкаСоединений_РабПоГр' => 'Технологическое оборудование',
        'ШМР_МонтажТеплоизоляции_РабПоГр' => 'Технологическое оборудование',
        'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр' => 'Технологическое оборудование',
        
        // Внутриблочный монтаж — Электрооборудование (5 подвидов работ)
        'ШМР_ЭлОб_МонтОб_РабГр' => 'Электрооборудование',
        'ШМР_ЭлОб_МонтКбСист_РабГр' => 'Электрооборудование',
        'ШМР_ЭлОб_ПроклКб_РабГр' => 'Электрооборудование',
        'ШМР_ЭлОб_ПодклОб_РабГр' => 'Электрооборудование',
        'ШМР_ЭлОб_МаркОб_РабГр' => 'Электрооборудование',
        
        // Внутриблочный монтаж — Отопление и вентиляция (9 подвидов работ)
        'ШМР_ОтоплВент_МонтОб_РабГр' => 'Отопление и вентиляция',
        'ШМР_ОтоплВент_МонтВозд_РабГр' => 'Отопление и вентиляция',
        'ШМР_ОтоплВент_МонтКонд_РабГр' => 'Отопление и вентиляция',
        'ШМР_ОтоплВент_МонтИТП_РабГр' => 'Отопление и вентиляция',
        'ШМР_ОтоплВент_ПневмГидр_РабГр' => 'Отопление и вентиляция',
        'ШМР_ОтоплВент_МонтКНС_РабГр' => 'Отопление и вентиляция',
        'ШМР_ОтоплВент_ПроклКб_РабГр' => 'Отопление и вентиляция',
        'ШМР_ОтоплВент_ПодклКб_РабГр' => 'Отопление и вентиляция',
        'ШМР_ОтоплВент_МаркОб_РабГр' => 'Отопление и вентиляция',
        
        // Внутриблочный монтаж — Пожарная сигнализация (5 подвидов работ)
        'ШМР_ПожСигн_МонтОб_РабГр' => 'Пожарная сигнализация',
        'ШМР_ПожСигн_МонтКбСист_РабГр' => 'Пожарная сигнализация',
        'ШМР_ПожСигн_ПроклКб_РабГр' => 'Пожарная сигнализация',
        'ШМР_ПожСигн_ПодклОб_РабГр' => 'Пожарная сигнализация',
        'ШМР_ПожСигн_МаркОб_РабГр' => 'Пожарная сигнализация',
        
        // Внутриблочный монтаж — Охранная сигнализация (5 подвидов работ)
        'ШМР_ОхрСигн_МонтОб_РабГр' => 'Охранная сигнализация',
        'ШМР_ОхрСигн_МонтКбСист_РабГр' => 'Охранная сигнализация',
        'ШМР_ОхрСигн_ПроклКб_РабГр' => 'Охранная сигнализация',
        'ШМР_ОхрСигн_ПодклОб_РабГр' => 'Охранная сигнализация',
        'ШМР_ОхрСигн_МаркОб_РабГр' => 'Охранная сигнализация',
        
        // Внутриблочный монтаж — Автоматизация (5 подвидов работ)
        'ШМР_Автом_МонтОб_РабГр' => 'Автоматизация',
        'ШМР_Автом_МонтКбСист_РабГр' => 'Автоматизация',
        'ШМР_Автом_ПроклКб_РабГр' => 'Автоматизация',
        'ШМР_Автом_ПодклОб_РабГр' => 'Автоматизация',
        'ШМР_Автом_МаркОб_РабГр' => 'Автоматизация',
        
        // ИСКЛЮЧЕНЫ: МонтажВентиляции, МонтажСнятогоОборудования, МонтажКабельнойПродукции
        // — activity_type не содержит контекст подвехи, невозможно однозначно определить
    ];
    
    // Маппинг ПодтВех activity_type → название вехи ПНР (ручное подтверждение = 100%)
    private const PNR_CONFIRMATION_TO_MILESTONE = [
        'ПодтВех_ЭлектрОб' => 'Электрооборудование',
        'ПодтВех_ПожСигн' => 'Пожарная сигнализация',
        'ПодтВех_ОхрСигн' => 'Охранная сигнализация',
        'ПодтВех_Автом' => 'Автоматизация',
        'ПодтВех_ВентОтопл' => 'Вентиляция и отопление',
        'ПодтВех_ТехОб' => 'Технологическое оборудование',
        'ПодтВех_Метрол' => 'Метрология',
    ];
    
    // Маппинг ПодтВех activity_type → название подвехи ШМР (ручное подтверждение = 100%)
    private const SMR_CONFIRMATION_TO_MILESTONE = [
        'ШМР_ВхКонтр_ПодтВех' => 'Входной контроль',
        'ШМР_ЗавОбор_ПодтВех' => 'Завоз оборудования на площадку',
        'ШМР_РасстБлок_ПодтВех' => 'Расстановка блоков',
        'ШМР_МонтПлощ_ПодтВех' => 'Монтаж площадок обслуживания',
        'ШМР_МонтЭлУкр_ПодтВех' => 'Монтаж элементов укрытия',
        'ШМР_СборКровл_ПодтВех' => 'Сборка кровли',
        'ШМР_ТехОб_ПодтВех' => 'Технологическое оборудование',
        'ШМР_ЭлОб_ПодтВех' => 'Электрооборудование',
        'ШМР_ПожСигн_ПодтВех' => 'Пожарная сигнализация',
        'ШМР_ОхрСигн_ПодтВех' => 'Охранная сигнализация',
        'ШМР_Автом_ПодтВех' => 'Автоматизация',
        'ШМР_ОтоплВент_ПодтВех' => 'Отопление и вентиляция',
    ];
    
    // Минимальный и максимальный интервал опроса (дни)
    private const MIN_SURVEY_INTERVAL = 1;
    private const MAX_SURVEY_INTERVAL = 5;
    
    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }
    
    /**
     * Map activity type to PNR milestone name
     */
    public function mapActivityToMilestone(string $activityType): ?string
    {
        return self::PNR_ACTIVITY_TO_MILESTONE[$activityType] ?? null;
    }
    
    /**
     * Check if activity type is a PNR "Работы по графику" activity
     */
    public function isPNRScheduleActivity(string $activityType): bool
    {
        return isset(self::PNR_ACTIVITY_TO_MILESTONE[$activityType]);
    }
    
    /**
     * Map activity type to SMR sub-milestone name
     */
    public function mapSMRActivityToSubmilestone(string $activityType): ?string
    {
        return self::SMR_ACTIVITY_TO_SUBMILESTONE[$activityType] ?? null;
    }
    
    /**
     * Check if activity type is an SMR "Работы по графику" activity
     */
    public function isSMRScheduleActivity(string $activityType): bool
    {
        return isset(self::SMR_ACTIVITY_TO_SUBMILESTONE[$activityType]);
    }
    
    /**
     * Map confirmation activity type (ПодтВех_*) to milestone name and work type.
     * Returns ['milestone_name' => ..., 'work_type' => 'ПНР'|'ШМР'] or null.
     */
    public function mapConfirmationToMilestone(string $activityType): ?array
    {
        if (isset(self::PNR_CONFIRMATION_TO_MILESTONE[$activityType])) {
            return [
                'milestone_name' => self::PNR_CONFIRMATION_TO_MILESTONE[$activityType],
                'work_type' => 'ПНР',
            ];
        }
        if (isset(self::SMR_CONFIRMATION_TO_MILESTONE[$activityType])) {
            return [
                'milestone_name' => self::SMR_CONFIRMATION_TO_MILESTONE[$activityType],
                'work_type' => 'ШМР',
            ];
        }
        return null;
    }
    
    /**
     * Handle manual milestone confirmation (ПодтВех) — sets progress to 100%
     * and records the confirmer's FIO.
     */
    public function confirmMilestoneCompletion(string $projectCode, string $activityType, $userId, string $fio): bool
    {
        $mapping = $this->mapConfirmationToMilestone($activityType);
        if ($mapping === null) {
            return false;
        }
        
        $milestoneName = $mapping['milestone_name'];
        $workType = $mapping['work_type'];
        
        $planHours = $this->getMilestonePlanHours($projectCode, $milestoneName, $workType);
        $factHours = $this->getMilestoneFactHours($projectCode, $milestoneName, $workType);
        
        $efficiency = 0;
        if ($planHours !== null && $planHours > 0) {
            $efficiency = $this->calculateEfficiency(100, $factHours, $planHours);
        }
        
        $result = $this->saveEstimate([
            'project_code' => $projectCode,
            'milestone_name' => $milestoneName,
            'work_type' => $workType,
            'estimated_percent' => 100,
            'estimator_user_id' => $userId,
            'estimator_fio' => $fio,
            'plan_hours' => $planHours,
            'fact_hours' => $factHours,
            'efficiency' => $efficiency,
        ]);
        
        if ($result) {
            error_log("MilestoneProgressService: Manual confirmation 100% by {$fio} for {$projectCode}/{$milestoneName} ({$workType})");
        }
        
        return $result;
    }
    
    /**
     * Calculate survey interval based on plan, fact hours and efficiency
     * Uses dynamic formula: plan/5 normally, (plan-fact)/5 when behind schedule
     */
    public function calculateSurveyInterval(?float $planHours, float $factHours = 0, ?float $efficiency = null): int
    {
        // No plan - default max interval
        if ($planHours === null || $planHours <= 0) {
            return self::MAX_SURVEY_INTERVAL; // 5 days
        }
        
        // Convert to days (8 hours = 1 day)
        $planDays = $planHours / 8;
        $factDays = $factHours / 8;
        
        // Use accelerated formula when efficiency is low (behind schedule)
        if ($efficiency !== null && $efficiency < 100) {
            $remainingDays = $planDays - $factDays;
            $interval = $remainingDays / 5;
        } else {
            // Base formula (first survey, or on track)
            $interval = $planDays / 5;
        }
        
        // Round and clamp to [1, 5]
        return max(self::MIN_SURVEY_INTERVAL, min(self::MAX_SURVEY_INTERVAL, (int)round($interval)));
    }
    
    /**
     * Check if survey should be shown for this milestone
     * Returns true if:
     * 1. No previous estimate exists, OR
     * 2. Enough days have passed since last estimate (dynamic interval)
     */
    public function shouldShowSurvey(string $projectCode, string $milestoneName, string $workType = 'ПНР'): bool
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get last estimate date and efficiency
            $stmt = $conn->prepare("
                SELECT estimated_at, plan_hours_at_estimate, efficiency_at_estimate 
                FROM milestone_progress_estimates 
                WHERE project_code = ? AND milestone_name = ? AND work_type = ?
            ");
            $stmt->bind_param('sss', $projectCode, $milestoneName, $workType);
            $stmt->execute();
            $result = $stmt->get_result();
            $lastEstimate = $result->fetch_assoc();
            
            // No previous estimate - should show survey
            if (!$lastEstimate) {
                error_log("MilestoneProgressService: No previous estimate for {$projectCode}/{$milestoneName} ({$workType}), showing survey");
                return true;
            }
            
            // Get CURRENT plan and fact hours (always fresh data)
            $planHours = $this->getMilestonePlanHours($projectCode, $milestoneName, $workType);
            $factHours = $this->getMilestoneFactHours($projectCode, $milestoneName, $workType);
            $efficiency = $lastEstimate['efficiency_at_estimate'] ?? null;
            
            $interval = $this->calculateSurveyInterval($planHours, $factHours, $efficiency);
            
            // Calculate days since last estimate
            $lastEstimateDate = new \DateTime($lastEstimate['estimated_at']);
            $now = new \DateTime();
            $daysSinceLastEstimate = $now->diff($lastEstimateDate)->days;
            
            $shouldShow = $daysSinceLastEstimate >= $interval;
            
            error_log("MilestoneProgressService: {$projectCode}/{$milestoneName} ({$workType}) - plan: " . round(($planHours ?? 0) / 8, 1) . "d, fact: " . round($factHours / 8, 1) . "d, eff: {$efficiency}%, interval: {$interval}d, since: {$daysSinceLastEstimate}d, show: " . ($shouldShow ? 'yes' : 'no'));
            
            return $shouldShow;
            
        } catch (\Exception $e) {
            error_log("MilestoneProgressService::shouldShowSurvey error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the last estimate for a milestone
     */
    public function getLastEstimate(string $projectCode, string $milestoneName, string $workType = 'ПНР'): ?array
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("
                SELECT * FROM milestone_progress_estimates 
                WHERE project_code = ? AND milestone_name = ? AND work_type = ?
            ");
            $stmt->bind_param('sss', $projectCode, $milestoneName, $workType);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc() ?: null;
            
        } catch (\Exception $e) {
            error_log("MilestoneProgressService::getLastEstimate error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get planned hours for a milestone from project_milestones
     */
    public function getMilestonePlanHours(string $projectCode, string $milestoneName, string $workType = 'ПНР'): ?float
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("
                SELECT planned_hours, unit, is_not_applicable 
                FROM project_milestones 
                WHERE project_code = ? AND milestone_name = ? AND work_type = ?
            ");
            $stmt->bind_param('sss', $projectCode, $milestoneName, $workType);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if (!$row || $row['is_not_applicable']) {
                return null;
            }
            
            // Convert to hours if in days
            if ($row['unit'] === 'days') {
                return $row['planned_hours'] * 8;
            }
            
            return (float)$row['planned_hours'];
            
        } catch (\Exception $e) {
            error_log("MilestoneProgressService::getMilestonePlanHours error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get fact hours for a milestone from ozna_tabel
     * Uses same logic as project card for consistency
     */
    public function getMilestoneFactHours(string $projectCode, string $milestoneName, string $workType = 'ПНР'): float
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            if ($workType === 'ШМР') {
                // SMR: match sub-milestone activity patterns with functional_direction = 'Шеф-монтаж'
                $stmt = $conn->prepare("
                    SELECT COUNT(DISTINCT ot.id) as days_count
                    FROM ozna_tabel ot
                    INNER JOIN ozna_data od ON ot.user_id = od.user_id
                    WHERE ot.message LIKE CONCAT('%', ?, '%')
                    AND ot.message LIKE CONCAT('%Работы по графику%', ?, '%')
                    AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
                    AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
                    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                ");
                $stmt->bind_param('ss', $projectCode, $milestoneName);
            } else {
                // PNR: original logic
                // Milestones that are recorded as standalone activities (without "Работы по графику" wrapper)
                $standaloneActivityMilestones = ['ИИ на рабочей среде', 'Комплексное опробование'];
                
                if (in_array($milestoneName, $standaloneActivityMilestones)) {
                    // For standalone milestones - also match standalone activity name
                    $stmt = $conn->prepare("
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
                    // Standard milestones - match "Работы по графику" and "Формирование ИД"
                    $stmt = $conn->prepare("
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
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            // Return days * 8 hours
            return ($row['days_count'] ?? 0) * 8;
            
        } catch (\Exception $e) {
            error_log("MilestoneProgressService::getMilestoneFactHours error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calculate efficiency based on completion percentage and hours
     * Formula: (% completion) / (fact_hours / plan_hours)
     */
    public function calculateEfficiency(float $percent, float $factHours, float $planHours): float
    {
        if ($factHours <= 0 || $planHours <= 0) {
            return 0;
        }
        
        $hoursRatio = $factHours / $planHours;
        if ($hoursRatio <= 0) {
            return 0;
        }
        
        return round($percent / $hoursRatio, 1);
    }
    
    /**
     * Validate that new percentage is >= previous (monotonically increasing)
     */
    public function validateMonotonicity(float $newPercent, float $previousPercent): bool
    {
        return $newPercent >= $previousPercent;
    }
    
    /**
     * Get motivational message based on efficiency level
     * Thresholds: >=100% (good), 80-99% (normal), <80% (warning)
     */
    public function getMotivationalMessage(float $efficiency): array
    {
        if ($efficiency >= 100) {
            return [
                'level' => 'good',
                'emoji' => '🟢',
                'message' => 'Отличная работа! Продолжайте в том же духе!'
            ];
        } elseif ($efficiency >= 80) {
            return [
                'level' => 'normal',
                'emoji' => '🟡',
                'message' => 'Хороший темп. Держите ритм!'
            ];
        } else {
            return [
                'level' => 'warning',
                'emoji' => '🔴',
                'message' => 'Есть отставание. При проблемах — сообщите РП.'
            ];
        }
    }
    
    /**
     * Save milestone progress estimate
     * Updates current estimate and adds to history
     */
    public function saveEstimate(array $data): bool
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Start transaction
            $conn->begin_transaction();
            
            // Get previous estimate (if exists)
            $previousPercent = 0;
            $workType = $data['work_type'] ?? 'ПНР';
            $stmt = $conn->prepare("
                SELECT estimated_percent FROM milestone_progress_estimates 
                WHERE project_code = ? AND milestone_name = ? AND work_type = ?
            ");
            $stmt->bind_param('sss', $data['project_code'], $data['milestone_name'], $workType);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            if ($existing) {
                $previousPercent = $existing['estimated_percent'];
            }
            
            // Validate monotonicity
            if ($data['estimated_percent'] < $previousPercent) {
                error_log("MilestoneProgressService: Rejecting estimate - new {$data['estimated_percent']}% < previous {$previousPercent}%");
                $conn->rollback();
                return false;
            }
            
            // Insert or update current estimate
            $stmt = $conn->prepare("
                INSERT INTO milestone_progress_estimates 
                (project_code, milestone_name, work_type, estimated_percent, previous_percent, 
                 estimator_user_id, estimator_fio, plan_hours_at_estimate, 
                 fact_hours_at_estimate, efficiency_at_estimate, estimated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    previous_percent = estimated_percent,
                    estimated_percent = VALUES(estimated_percent),
                    estimator_user_id = VALUES(estimator_user_id),
                    estimator_fio = VALUES(estimator_fio),
                    plan_hours_at_estimate = VALUES(plan_hours_at_estimate),
                    fact_hours_at_estimate = VALUES(fact_hours_at_estimate),
                    efficiency_at_estimate = VALUES(efficiency_at_estimate),
                    estimated_at = NOW()
            ");
            $stmt->bind_param(
                'sssiissddd',
                $data['project_code'],
                $data['milestone_name'],
                $workType,
                $data['estimated_percent'],
                $previousPercent,
                $data['estimator_user_id'],
                $data['estimator_fio'],
                $data['plan_hours'],
                $data['fact_hours'],
                $data['efficiency']
            );
            $stmt->execute();
            
            // Add to history
            $stmt = $conn->prepare("
                INSERT INTO milestone_progress_history 
                (project_code, milestone_name, work_type, estimated_percent, 
                 estimator_user_id, estimator_fio, plan_hours, fact_hours, efficiency)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'sssissddd',
                $data['project_code'],
                $data['milestone_name'],
                $workType,
                $data['estimated_percent'],
                $data['estimator_user_id'],
                $data['estimator_fio'],
                $data['plan_hours'],
                $data['fact_hours'],
                $data['efficiency']
            );
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            error_log("MilestoneProgressService: Saved estimate {$data['estimated_percent']}% for {$data['project_code']}/{$data['milestone_name']}");
            
            return true;
            
        } catch (\Exception $e) {
            error_log("MilestoneProgressService::saveEstimate error: " . $e->getMessage());
            if (isset($conn)) {
                $conn->rollback();
            }
            return false;
        }
    }
    
    /**
     * Get total PNR efficiency for a project
     * Weighted average of all milestones with estimates
     */
    public function getPNRTotalEfficiency(string $projectCode): ?float
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("
                SELECT AVG(efficiency_at_estimate) as avg_efficiency
                FROM milestone_progress_estimates 
                WHERE project_code = ? AND work_type = 'ПНР' AND efficiency_at_estimate IS NOT NULL
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['avg_efficiency'] ? round($row['avg_efficiency'], 1) : null;
            
        } catch (\Exception $e) {
            error_log("MilestoneProgressService::getPNRTotalEfficiency error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get total SMR efficiency for a project
     * Weighted average of all SMR sub-milestones with estimates
     */
    public function getSMRTotalEfficiency(string $projectCode): ?float
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("
                SELECT AVG(efficiency_at_estimate) as avg_efficiency
                FROM milestone_progress_estimates 
                WHERE project_code = ? AND work_type = 'ШМР' AND efficiency_at_estimate IS NOT NULL
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['avg_efficiency'] ? round($row['avg_efficiency'], 1) : null;
            
        } catch (\Exception $e) {
            error_log("MilestoneProgressService::getSMRTotalEfficiency error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all estimates for a project's milestones
     */
    public function getProjectMilestoneEstimates(string $projectCode, ?string $workType = null): array
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            if ($workType !== null) {
                $stmt = $conn->prepare("
                    SELECT * FROM milestone_progress_estimates 
                    WHERE project_code = ? AND work_type = ?
                    ORDER BY milestone_name
                ");
                $stmt->bind_param('ss', $projectCode, $workType);
            } else {
                $stmt = $conn->prepare("
                    SELECT * FROM milestone_progress_estimates 
                    WHERE project_code = ?
                    ORDER BY milestone_name
                ");
                $stmt->bind_param('s', $projectCode);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $estimates = [];
            while ($row = $result->fetch_assoc()) {
                $estimates[$row['milestone_name']] = $row;
            }
            
            return $estimates;
            
        } catch (\Exception $e) {
            error_log("MilestoneProgressService::getProjectMilestoneEstimates error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get history of estimates for a milestone
     */
    public function getMilestoneProgressHistory(string $projectCode, string $milestoneName, int $limit = 10): array
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("
                SELECT * FROM milestone_progress_history 
                WHERE project_code = ? AND milestone_name = ?
                ORDER BY estimated_at DESC
                LIMIT ?
            ");
            $stmt->bind_param('ssi', $projectCode, $milestoneName, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $history = [];
            while ($row = $result->fetch_assoc()) {
                $history[] = $row;
            }
            
            return $history;
            
        } catch (\Exception $e) {
            error_log("MilestoneProgressService::getMilestoneProgressHistory error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Format survey message for display
     */
    public function formatSurveyMessage(
        string $projectCode,
        string $milestoneName,
        ?array $lastEstimate,
        float $planHours,
        float $factHours,
        ?float $milestoneEfficiency,
        ?float $totalEfficiency,
        string $workType = 'ПНР'
    ): string {
        $planDays = round($planHours / 8, 1);
        $factDays = round($factHours / 8, 1);
        
        $text = "<b>📊 Оценка выполнения вехи</b>\n\n";
        $text .= "<b>Проект:</b> {$projectCode}\n";
        $text .= "<b>Веха:</b> {$milestoneName}\n\n";
        
        $text .= "<b>📋 Показатели:</b>\n";
        $text .= "• План: <b>{$planDays}</b> дн.\n";
        $text .= "• Факт: <b>{$factDays}</b> дн.\n";
        
        if ($lastEstimate) {
            $prevPercent = $lastEstimate['estimated_percent'];
            $prevDate = date('d.m.Y', strtotime($lastEstimate['estimated_at']));
            $prevFio = $lastEstimate['estimator_fio'];
            $text .= "• Предыдущая оценка: <b>{$prevPercent}%</b> ({$prevDate}, {$prevFio})\n";
        }
        
        if ($milestoneEfficiency !== null) {
            $text .= "\n<b>📈 Эффективность вехи:</b> {$milestoneEfficiency}%\n";
        }
        
        $effLabel = $workType === 'ШМР' ? 'ШМР' : 'ПНР';
        if ($totalEfficiency !== null) {
            $text .= "<b>📈 Эффективность {$effLabel}:</b> {$totalEfficiency}%\n";
        }
        
        $text .= "\n<b>Введите процент выполнения вехи (0-100):</b>";
        
        if ($lastEstimate) {
            $minPercent = $lastEstimate['estimated_percent'];
            $text .= "\n<i>Минимально допустимое значение: {$minPercent}%</i>";
        }
        
        return $text;
    }
    
    /**
     * Format info message for non-survey users
     */
    public function formatInfoMessage(
        string $projectCode,
        string $milestoneName,
        ?array $lastEstimate,
        float $efficiency
    ): string {
        $motivational = $this->getMotivationalMessage($efficiency);
        
        $text = "<b>📊 Информация о вехе</b>\n\n";
        $text .= "<b>Проект:</b> {$projectCode}\n";
        $text .= "<b>Веха:</b> {$milestoneName}\n\n";
        
        if ($lastEstimate) {
            $percent = $lastEstimate['estimated_percent'];
            $estimatorFio = $lastEstimate['estimator_fio'] ?? 'Неизвестно';
            $estimatedAt = isset($lastEstimate['estimated_at']) 
                ? date('d.m.Y H:i', strtotime($lastEstimate['estimated_at'])) 
                : '';
            
            $text .= "• Выполнение: <b>{$percent}%</b>\n";
            $text .= "• Оценил: {$estimatorFio}\n";
            if ($estimatedAt) {
                $text .= "• Дата оценки: {$estimatedAt}\n";
            }
        }
        
        $text .= "• Эффективность: <b>{$efficiency}%</b>\n\n";
        $text .= "{$motivational['emoji']} {$motivational['message']}";
        
        return $text;
    }
    
    /**
     * Format confirmation message after saving estimate
     */
    public function formatConfirmationMessage(
        string $milestoneName,
        int $percent,
        float $efficiency
    ): string {
        $motivational = $this->getMotivationalMessage($efficiency);
        
        $text = "<b>✅ Оценка сохранена</b>\n\n";
        $text .= "<b>Веха:</b> {$milestoneName}\n";
        $text .= "<b>Выполнение:</b> {$percent}%\n";
        $text .= "<b>Эффективность:</b> {$efficiency}%\n\n";
        $text .= "{$motivational['emoji']} {$motivational['message']}";
        
        return $text;
    }
}
