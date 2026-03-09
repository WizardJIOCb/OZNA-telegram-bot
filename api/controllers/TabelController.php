<?php

namespace OznaApi\Controllers;

class TabelController
{
    /**
     * GET /tabel/activity-types
     * Returns the cached activity tree JSON.
     */
    public function activityTypes(): void
    {
        $file = __DIR__ . '/../data/activity-tree.json';
        if (!file_exists($file)) {
            http_response_code(500);
            echo json_encode(['error' => 'Activity tree not found']);
            return;
        }
        // Cache for 24 hours (client-side)
        header('Cache-Control: public, max-age=86400');
        readfile($file);
    }

    /**
     * GET /tabel/projects
     * Returns list of active projects.
     */
    public function projects(): void
    {
        // Load projects from data/projects.json (same source as TG bot)
        $projectsFile = dirname(dirname(__DIR__)) . '/data/projects.json';
        $projects = [];

        if (file_exists($projectsFile)) {
            $raw = file_get_contents($projectsFile);
            $projects = json_decode($raw, true) ?? [];
        }

        // Also fetch active/pending projects from database
        $db = getDb();
        $stmt = $db->prepare(
            "SELECT DISTINCT current_name FROM project_moderation 
             WHERE is_active = 1 AND status IN ('active', 'pending') 
             ORDER BY current_name"
        );
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $name = $row['current_name'];
                if (!in_array($name, $projects)) {
                    $projects[] = $name;
                }
            }
        }

        // Smart sort: ОИ-series numerically first, then others alphabetically
        usort($projects, function ($a, $b) {
            $aIsOI = preg_match('/^ОИ(\d+)/', $a, $am);
            $bIsOI = preg_match('/^ОИ(\d+)/', $b, $bm);
            if ($aIsOI && $bIsOI) {
                return (int)$am[1] - (int)$bm[1];
            }
            if ($aIsOI && !$bIsOI) return -1;
            if (!$aIsOI && $bIsOI) return 1;
            return strcmp($a, $b);
        });

        header('Cache-Control: public, max-age=3600');
        echo json_encode($projects, JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /tabel
     * Creates a new tabel entry and sends TG notification.
     *
     * Body: { date, project_code, activity_type, idle_reason?, custom_description? }
     */
    public function create(array $body): void
    {
        $auth = $GLOBALS['auth_user'];
        $telegramId = $auth['telegram_id'] ?? null;

        if (!$telegramId) {
            http_response_code(400);
            echo json_encode(['error' => 'Аккаунт не привязан к Telegram'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validate required fields
        $date         = trim($body['date'] ?? '');
        $projectCode  = trim($body['project_code'] ?? '');
        $activityType = trim($body['activity_type'] ?? '');
        $idleReason   = trim($body['idle_reason'] ?? $body['custom_description'] ?? '');

        if ($date === '' || $activityType === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Укажите дату и тип работ'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Project code required for non-absence entries
        $isAbsence = in_array($activityType, ['Больничный', 'Отпуск']);
        if (!$isAbsence && $projectCode === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Укажите проект'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validate date format (dd.mm.yyyy)
        if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
            http_response_code(400);
            echo json_encode(['error' => 'Неверный формат даты. Используйте дд.мм.гггг'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $fio = $auth['fio'] ?? '';
        $fn  = $auth['fn'] ?? '';

        // Use the existing TabelEntry class to format the message (ensures consistency)
        $botRoot = dirname(dirname(__DIR__));

        // Load shared services
        require_once $botRoot . '/CustomCommands/Services/DatabaseService.php';
        require_once $botRoot . '/CustomCommands/Services/MessageService.php';
        require_once $botRoot . '/CustomCommands/Services/TabelEntry.php';

        $dbService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();

        // Check for duplicate entry
        // Skip duplicate check for entries with descriptions (warranty claims etc.)
        // — they are unique by their claim subject
        $activityMappings = $this->getActivityMappings();
        $activityText = $activityMappings[$activityType] ?? $activityType;

        if (empty($idleReason)) {
            $duplicate = $dbService->checkDuplicateActivityReport($telegramId, $activityText, $date, $projectCode);

            if ($duplicate) {
                http_response_code(409);
                echo json_encode([
                    'error' => 'Такая запись уже существует',
                    'duplicate' => true,
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        // Create MessageService (needed by TabelEntry constructor)
        // We need the Telegram bot initialized for Request::sendMessage to work
        $this->initTelegram($botRoot);

        $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService($dbService);
        $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($dbService, $messageService);

        // Format the message exactly like the TG bot does
        $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason);

        // Prepend globe emoji to mark PWA origin
        $message = "🌐 " . $message;

        // Save to database
        $saved = $dbService->addUserTabel($telegramId, $message);

        if (!$saved) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка сохранения записи'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Send notification to TG group chat
        try {
            $messageService->sendToHistoryChatByFunctionalDirection($message, $fn);
        } catch (\Throwable $e) {
            // Don't fail the request if notification fails
            error_log("PWA Tabel: TG notification failed: " . $e->getMessage());
        }

        // Handle manual milestone confirmation (ПодтВех_*) — save 100% estimate with FIO
        try {
            $milestoneService = $this->getMilestoneService();
            $milestoneService->confirmMilestoneCompletion($projectCode, $activityType, $telegramId, $fio);
        } catch (\Throwable $e) {
            error_log("PWA Tabel: milestone confirmation failed: " . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Запись добавлена',
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Initialize Telegram bot instance (needed for Request::sendMessage in notifications).
     */
    private function initTelegram(string $botRoot): void
    {
        static $initialized = false;
        if ($initialized) return;

        $config = require dirname(__DIR__) . '/config.php';
        $token = $config['telegram']['bot_token'];
        $username = $config['telegram']['bot_username'];

        try {
            if (!class_exists('Longman\TelegramBot\Telegram')) {
                require_once $botRoot . '/vendor/autoload.php';
            }
            new \Longman\TelegramBot\Telegram($token, $username);
            $initialized = true;
        } catch (\Throwable $e) {
            error_log("PWA: Failed to init Telegram: " . $e->getMessage());
        }
    }

    /**
     * Get activity type mappings (subset of TabelEntry's mappings).
     * Used for duplicate checking display text lookup.
     */
    /**
     * GET /tabel/claims?project_code=XXX
     * Returns active/pending claims for a project.
     */
    public function getClaims(string $projectCode): void
    {
        if (trim($projectCode) === '') {
            http_response_code(400);
            echo json_encode(['error' => 'project_code required'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $db = getDb();
        $stmt = $db->prepare(
            "SELECT id, project_code, reg_number, subject, act_date, equipment, 
                    serial_number, act_author, description, status, created_at
             FROM claims 
             WHERE project_code = ? AND status IN ('active', 'pending_moderation')
             ORDER BY created_at DESC
             LIMIT 50"
        );

        if (!$stmt) {
            // Table may not exist
            echo json_encode([], JSON_UNESCAPED_UNICODE);
            return;
        }

        $stmt->bind_param('s', $projectCode);
        $stmt->execute();
        $result = $stmt->get_result();
        $claims = [];
        while ($row = $result->fetch_assoc()) {
            $claims[] = $row;
        }
        $stmt->close();

        echo json_encode($claims, JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /tabel/claims
     * Creates a new claim (user flow, pending_moderation).
     * Body: { project_code, subject, equipment, serial_number, description }
     */
    public function createClaim(array $body): void
    {
        $auth = $GLOBALS['auth_user'];
        $telegramId = $auth['telegram_id'] ?? null;
        $fio = $auth['fio'] ?? '';

        $projectCode  = trim($body['project_code'] ?? '');
        $subject      = trim($body['subject'] ?? '');
        $equipment    = trim($body['equipment'] ?? '');
        $serialNumber = trim($body['serial_number'] ?? '');
        $description  = trim($body['description'] ?? '');

        if ($projectCode === '' || $subject === '' || $equipment === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Укажите проект, тему и оборудование'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $db = getDb();
        $regNumber = '-';
        $actDate = date('d.m.Y');
        $actAuthor = $fio;
        $status = 'pending_moderation';

        $stmt = $db->prepare(
            "INSERT INTO claims (project_code, reg_number, subject, act_date, equipment, serial_number, act_author, description, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $stmt->bind_param('sssssssssi', $projectCode, $regNumber, $subject, $actDate, $equipment, $serialNumber, $actAuthor, $description, $status, $telegramId);
        $success = $stmt->execute();
        $claimId = $stmt->insert_id;
        $stmt->close();

        if (!$success) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка создания рекламации'], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Рекламация создана и ожидает модерации',
            'claim_id' => $claimId,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /tabel/claims/{id}/close
     * Request closure of a claim (sets status to pending_close).
     */
    public function closeClaim(int $claimId, array $body): void
    {
        $auth = $GLOBALS['auth_user'];
        $telegramId = $auth['telegram_id'] ?? null;

        if (!$telegramId) {
            http_response_code(400);
            echo json_encode(['error' => 'Аккаунт не привязан к Telegram'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $db = getDb();

        // Set to pending_close — moderator approves final closure via TG bot
        $stmt = $db->prepare(
            "UPDATE claims SET status='pending_close' WHERE id=? AND status IN ('active', 'pending_moderation')"
        );

        if (!$stmt) {
            error_log("PWA closeClaim: prepare failed: " . $db->error);
            http_response_code(500);
            echo json_encode(['error' => 'Database error'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $stmt->bind_param('i', $claimId);
        $success = $stmt->execute();

        if (!$success) {
            error_log("PWA closeClaim: execute failed: " . $stmt->error);
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $stmt->error], JSON_UNESCAPED_UNICODE);
            $stmt->close();
            return;
        }

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Заявка на закрытие отправлена на модерацию'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Рекламация не найдена или уже закрыта'], JSON_UNESCAPED_UNICODE);
        }
        $stmt->close();
    }

    /**
     * POST /tabel/absence
     * Creates absence entries for a date range (one entry per day).
     * Body: { activity_type: 'Больничный'|'Отпуск', start_date, end_date }
     */
    public function createAbsence(array $body): void
    {
        $auth = $GLOBALS['auth_user'];
        $telegramId = $auth['telegram_id'] ?? null;
        $fio = $auth['fio'] ?? '';
        $fn  = $auth['fn'] ?? '';

        if (!$telegramId) {
            http_response_code(400);
            echo json_encode(['error' => 'Аккаунт не привязан к Telegram'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $activityType = trim($body['activity_type'] ?? '');
        $startDate    = trim($body['start_date'] ?? '');
        $endDate      = trim($body['end_date'] ?? '');

        if (!in_array($activityType, ['Больничный', 'Отпуск'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Неверный тип отсутствия'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $dateFormat = '/^\d{2}\.\d{2}\.\d{4}$/';
        if (!preg_match($dateFormat, $startDate) || !preg_match($dateFormat, $endDate)) {
            http_response_code(400);
            echo json_encode(['error' => 'Неверный формат даты. Используйте дд.мм.гггг'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $start = \DateTime::createFromFormat('d.m.Y', $startDate);
        $end   = \DateTime::createFromFormat('d.m.Y', $endDate);

        if (!$start || !$end || $end < $start) {
            http_response_code(400);
            echo json_encode(['error' => 'Дата окончания не может быть раньше даты начала'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $dayCount = $start->diff($end)->days + 1;
        if ($dayCount > 60) {
            http_response_code(400);
            echo json_encode(['error' => 'Максимальный диапазон — 60 дней'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $emoji = $activityType === 'Больничный' ? '🏥' : '🏖';

        $botRoot = dirname(dirname(__DIR__));
        require_once $botRoot . '/CustomCommands/Services/DatabaseService.php';
        require_once $botRoot . '/CustomCommands/Services/MessageService.php';
        require_once $botRoot . '/CustomCommands/Services/TabelEntry.php';

        $dbService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();

        // Create one entry per day
        $saved = 0;
        $skipped = 0;
        $current = clone $start;

        while ($current <= $end) {
            $dayStr = $current->format('d.m.Y');
            $message = "🌐 <b>{$fio}</b>\r\n{$emoji} {$dayStr}: {$activityType}";

            // Check duplicate
            $pattern = "%{$emoji} {$dayStr}: {$activityType}%";
            $db = getDb();
            $stmt = $db->prepare(
                "SELECT COUNT(*) as cnt FROM ozna_tabel WHERE user_id = ? AND message LIKE ?"
            );
            $stmt->bind_param('is', $telegramId, $pattern);
            $stmt->execute();
            $cnt = $stmt->get_result()->fetch_assoc()['cnt'];
            $stmt->close();

            if ($cnt > 0) {
                $skipped++;
            } else {
                $result = $dbService->addUserTabel($telegramId, $message);
                if ($result) $saved++;
            }

            $current->modify('+1 day');
        }

        // Send one summary notification to TG group
        if ($saved > 0) {
            try {
                $this->initTelegram($botRoot);
                $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService($dbService);
                $summary = "🌐 <b>{$fio}</b>\r\n{$emoji} {$activityType} с {$startDate} по {$endDate} ({$dayCount} дн.)";
                $messageService->sendToHistoryChatByFunctionalDirection($summary, $fn);
            } catch (\Throwable $e) {
                error_log("PWA Absence: TG notification failed: " . $e->getMessage());
            }
        }

        $msg = "Записано дней: {$saved}";
        if ($skipped > 0) {
            $msg .= " (пропущено дублей: {$skipped})";
        }

        echo json_encode([
            'success' => true,
            'message' => $msg,
            'saved' => $saved,
            'skipped' => $skipped,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /tabel/milestone-survey?project_code=X&activity_type=Y
     * Check if milestone survey is needed and return context data.
     */
    public function getMilestoneSurvey(array $params): void
    {
        $projectCode = trim($params['project_code'] ?? '');
        $activityType = trim($params['activity_type'] ?? '');

        if ($projectCode === '' || $activityType === '') {
            echo json_encode(['needs_survey' => false], JSON_UNESCAPED_UNICODE);
            return;
        }

        $milestoneService = $this->getMilestoneService();

        // Map activity to milestone
        $milestoneName = $milestoneService->mapActivityToMilestone($activityType);
        $workType = 'ПНР';
        if ($milestoneName === null) {
            $milestoneName = $milestoneService->mapSMRActivityToSubmilestone($activityType);
            $workType = 'ШМР';
        }

        if ($milestoneName === null) {
            echo json_encode(['needs_survey' => false], JSON_UNESCAPED_UNICODE);
            return;
        }

        $needsSurvey = $milestoneService->shouldShowSurvey($projectCode, $milestoneName, $workType);
        $lastEstimate = $milestoneService->getLastEstimate($projectCode, $milestoneName, $workType);
        $planHours = $milestoneService->getMilestonePlanHours($projectCode, $milestoneName, $workType);
        $factHours = $milestoneService->getMilestoneFactHours($projectCode, $milestoneName, $workType);

        $efficiency = null;
        $motivational = null;
        if ($planHours !== null && $planHours > 0 && $lastEstimate) {
            $efficiency = $milestoneService->calculateEfficiency(
                (float)$lastEstimate['estimated_percent'], $factHours, $planHours
            );
            $motivational = $milestoneService->getMotivationalMessage($efficiency);
        }
        // Also get project-wide efficiency
        $totalEfficiency = $workType === 'ШМР'
            ? $milestoneService->getSMRTotalEfficiency($projectCode)
            : $milestoneService->getPNRTotalEfficiency($projectCode);

        echo json_encode([
            'needs_survey' => $needsSurvey,
            'milestone_name' => $milestoneName,
            'work_type' => $workType,
            'plan_hours' => $planHours,
            'fact_hours' => $factHours,
            'previous_percent' => $lastEstimate['estimated_percent'] ?? 0,
            'previous_estimator' => $lastEstimate['estimator_fio'] ?? null,
            'previous_date' => $lastEstimate ? date('d.m.Y', strtotime($lastEstimate['estimated_at'])) : null,
            'efficiency' => $efficiency,
            'total_efficiency' => $totalEfficiency,
            'motivational' => $motivational,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /tabel/milestone-estimate
     * Save a milestone progress estimate.
     */
    public function saveMilestoneEstimate(array $body): void
    {
        $auth = $GLOBALS['auth_user'];
        $telegramId = $auth['telegram_id'] ?? null;
        $fio = $auth['fio'] ?? '';
        $fn  = $auth['fn'] ?? '';

        $projectCode = trim($body['project_code'] ?? '');
        $activityType = trim($body['activity_type'] ?? '');
        $percent = (int)($body['estimated_percent'] ?? -1);

        if ($projectCode === '' || $activityType === '' || $percent < 0 || $percent > 100) {
            http_response_code(400);
            echo json_encode(['error' => 'Неверные параметры'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $milestoneService = $this->getMilestoneService();

        $milestoneName = $milestoneService->mapActivityToMilestone($activityType);
        $workType = 'ПНР';
        if ($milestoneName === null) {
            $milestoneName = $milestoneService->mapSMRActivityToSubmilestone($activityType);
            $workType = 'ШМР';
        }

        if ($milestoneName === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Активность не связана с вехой'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $planHours = $milestoneService->getMilestonePlanHours($projectCode, $milestoneName, $workType);
        $factHours = $milestoneService->getMilestoneFactHours($projectCode, $milestoneName, $workType);

        $efficiency = 0;
        if ($planHours !== null && $planHours > 0) {
            $efficiency = $milestoneService->calculateEfficiency((float)$percent, $factHours, $planHours);
        }

        $success = $milestoneService->saveEstimate([
            'project_code' => $projectCode,
            'milestone_name' => $milestoneName,
            'work_type' => $workType,
            'estimated_percent' => $percent,
            'estimator_user_id' => $telegramId,
            'estimator_fio' => $fio,
            'plan_hours' => $planHours,
            'fact_hours' => $factHours,
            'efficiency' => $efficiency,
        ]);

        if (!$success) {
            http_response_code(409);
            echo json_encode(['error' => 'Процент не может быть меньше предыдущей оценки'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // At 100%: auto-create completion tabel entry
        if ($percent === 100 && $telegramId) {
            try {
                $botRoot = dirname(dirname(__DIR__));
                require_once $botRoot . '/CustomCommands/Services/DatabaseService.php';
                $dbService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
                $completionLabel = $workType === 'ШМР'
                    ? "Этап шеф-монтажа завершен: {$milestoneName}"
                    : "Этап наладки завершен: {$milestoneName}";
                $date = date('d.m.Y');
                $message = "🌐 <b>{$fio}</b>\r\n{$date}, {$projectCode}\r\n🏆 {$completionLabel}";
                $dbService->addUserTabel($telegramId, $message);
            } catch (\Throwable $e) {
                error_log("PWA milestone 100%: completion entry failed: " . $e->getMessage());
            }
        }

        $motivational = $efficiency > 0
            ? $milestoneService->getMotivationalMessage($efficiency)
            : null;

        echo json_encode([
            'success' => true,
            'efficiency' => $efficiency,
            'motivational' => $motivational,
            'is_complete' => $percent === 100,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /tabel/milestone-alerts
     * Get milestone deviation alerts for the current user's projects.
     */
    public function getMilestoneAlerts(): void
    {
        $auth = $GLOBALS['auth_user'];
        $telegramId = $auth['telegram_id'] ?? null;

        if (!$telegramId) {
            echo json_encode(['alerts' => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        $db = getDb();
        // Get user's recent project codes (last 30 days)
        $stmt = $db->prepare(
            "SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(message, '\n', 2), ', ', -1) as pc
             FROM ozna_tabel
             WHERE user_id = ? AND dt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             AND message LIKE '%,%'"
        );
        $stmt->bind_param('i', $telegramId);
        $stmt->execute();
        $result = $stmt->get_result();

        $projectCodes = [];
        while ($row = $result->fetch_assoc()) {
            $pc = trim($row['pc'] ?? '');
            if ($pc !== '' && preg_match('/^[A-ZА-Яa-zа-я0-9\-_\.]+$/u', $pc)) {
                $projectCodes[] = $pc;
            }
        }
        $stmt->close();

        $milestoneService = $this->getMilestoneService();
        $alerts = [];

        foreach (array_unique($projectCodes) as $pc) {
            $pnrEff = $milestoneService->getPNRTotalEfficiency($pc);
            if ($pnrEff !== null && $pnrEff < 100) {
                $mot = $milestoneService->getMotivationalMessage($pnrEff);
                $alerts[] = [
                    'project_code' => $pc,
                    'work_type' => 'ПНР',
                    'efficiency' => $pnrEff,
                    'level' => $mot['level'],
                    'emoji' => $mot['emoji'],
                    'message' => $mot['message'],
                ];
            }
            $smrEff = $milestoneService->getSMRTotalEfficiency($pc);
            if ($smrEff !== null && $smrEff < 100) {
                $mot = $milestoneService->getMotivationalMessage($smrEff);
                $alerts[] = [
                    'project_code' => $pc,
                    'work_type' => 'ШМР',
                    'efficiency' => $smrEff,
                    'level' => $mot['level'],
                    'emoji' => $mot['emoji'],
                    'message' => $mot['message'],
                ];
            }
        }

        echo json_encode(['alerts' => $alerts], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Instantiate MilestoneProgressService (reusing bot's service).
     */
    private function getMilestoneService(): \Longman\TelegramBot\Commands\SystemCommands\Services\MilestoneProgressService
    {
        $botRoot = dirname(dirname(__DIR__));
        require_once $botRoot . '/CustomCommands/Services/DatabaseService.php';
        require_once $botRoot . '/CustomCommands/Services/MilestoneProgressService.php';
        $dbService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
        return new \Longman\TelegramBot\Commands\SystemCommands\Services\MilestoneProgressService($dbService);
    }

    private function getActivityMappings(): array
    {
        // Load from TabelEntry via reflection to avoid duplicating the array
        $botRoot = dirname(dirname(__DIR__));
        require_once $botRoot . '/CustomCommands/Services/DatabaseService.php';
        require_once $botRoot . '/CustomCommands/Services/MessageService.php';
        require_once $botRoot . '/CustomCommands/Services/TabelEntry.php';

        try {
            $ref = new \ReflectionClass(\Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry::class);
            $prop = $ref->getProperty('activityTypeMappings');
            $prop->setAccessible(true);
            $dbService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
            $msgService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService($dbService);
            $instance = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($dbService, $msgService);
            return $prop->getValue($instance);
        } catch (\Throwable $e) {
            error_log("PWA: Failed to load activity mappings: " . $e->getMessage());
            return [];
        }
    }
}
