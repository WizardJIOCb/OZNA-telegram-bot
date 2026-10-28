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
        // Short cache so updates reach users within minutes
        header('Cache-Control: public, max-age=300, must-revalidate');
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

        header('Cache-Control: no-cache');
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

        // Normalize idle leaf keys from PWA activity tree (pnr_idle_*, smr_idle_*, mob_idle_*)
        if (preg_match('/^(pnr|smr|mob)_idle_/', $activityType)) {
            $prefixMap = [
                'pnr' => 'Простой',
                'smr' => 'ШМ_Простой',
                'mob' => 'МобБриг_Простой',
            ];
            $prefix = substr($activityType, 0, 3);
            $rootKey = $prefixMap[$prefix] ?? 'Простой';

            // Resolve idle_reason from activity tree if not already provided
            if (empty($idleReason)) {
                $idleReason = $this->resolveIdleReasonFromTree($activityType) ?? '';
            }

            $activityType = $rootKey;
        }

        // Normalize "Другая причина" idle keys (e.g. Простой_Прочее, ШМ_Простой_Прочее)
        // User's custom text is in idle_reason; map to root idle key
        $idleProcheeMap = [
            'Простой_Прочее' => 'Простой',
            'ШМ_Простой_Прочее' => 'ШМ_Простой',
            'МобБриг_Простой_Прочее' => 'МобБриг_Простой',
        ];
        if (isset($idleProcheeMap[$activityType])) {
            $customText = $idleReason;
            $activityType = $idleProcheeMap[$activityType];
            $idleReason = !empty($customText) ? "Прочее ({$customText})" : 'Прочее (Другая причина)';
        }

        if ($date === '' || $activityType === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Укажите дату и тип работ'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Project code required for non-absence and non-current-activity entries
        $isAbsence = in_array($activityType, ['Больничный', 'Отпуск', 'Донорский день']);
        $currentActivityTypes = [
            'МедОсмотр', 'Аттестация', 'Общая',
            'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_МСО', 'ПодготовкаТКП_АСН',
        ];
        $isCurrentActivity = in_array($activityType, $currentActivityTypes);
        if (!$isAbsence && !$isCurrentActivity && $projectCode === '') {
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

        // Fetch fresh FIO from DB (JWT payload may be stale)
        $fresh = $this->getFreshUserData($auth);
        $fio = $fresh['fio'];
        $fn  = $fresh['fn'];

        // Initialize Telegram + services BEFORE duplicate check
        $this->initTelegram($botRoot);
        $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService($dbService);
        $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($dbService, $messageService);

        // Generate the message FIRST, then check for duplicates using the actual message content
        $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason);

        // Extract activity display text from the generated message for duplicate check
        // This is robust because we check against the EXACT text that will be stored
        $activityText = '';
        if (preg_match('/табель: <b>(.+?)<\/b>/u', $message, $m)) {
            // Standard format: "Добавлена запись в табель: <b>ACTIVITY</b>"
            $activityText = $m[1];
        } elseif (preg_match('/деятельность \(<b>(.+?)<\/b>\)/u', $message, $m)) {
            // Current activity format: "Текущая деятельность (<b>ACTIVITY</b>) добавлена"
            $activityText = $m[1];
        } else {
            // Fallback: use mapping via getter
            $mappings = $tabelEntry->getActivityTypeMappings();
            $activityText = $mappings[$activityType] ?? $activityType;
        }

        // Acquire distributed lock (same format as TG bot) to prevent race conditions
        $lockKey = "ozna_report_{$telegramId}_" . md5("{$activityText}_{$date}_{$projectCode}");
        $db = $dbService->getConnection();
        $lockStmt = $db->prepare("SELECT GET_LOCK(?, 3)");
        $lockStmt->bind_param('s', $lockKey);
        $lockStmt->execute();
        $lockResult = $lockStmt->get_result()->fetch_row()[0];
        $lockStmt->close();

        if (!$lockResult) {
            http_response_code(429);
            echo json_encode([
                'error' => 'Запрос уже обрабатывается, подождите',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Direct duplicate check using flexible date pattern (no comma requirement)
            // This works for both standard format (✅DATE, Проект) and current activity (от <b>DATE</b>:)
            $activityPattern = "%{$activityText}%";
            $datePattern = "%{$date}%";

            if (!empty($projectCode)) {
                $projectPattern = "%{$projectCode}%";
                $dupStmt = $db->prepare(
                    "SELECT COUNT(*) as cnt FROM ozna_tabel WHERE user_id = ? AND message LIKE ? AND message LIKE ? AND message LIKE ?"
                );
                $dupStmt->bind_param('isss', $telegramId, $activityPattern, $datePattern, $projectPattern);
            } else {
                $dupStmt = $db->prepare(
                    "SELECT COUNT(*) as cnt FROM ozna_tabel WHERE user_id = ? AND message LIKE ? AND message LIKE ?"
                );
                $dupStmt->bind_param('iss', $telegramId, $activityPattern, $datePattern);
            }
            $dupStmt->execute();
            $cnt = $dupStmt->get_result()->fetch_assoc()['cnt'];
            $dupStmt->close();

            if ($cnt > 0) {
                http_response_code(409);
                echo json_encode([
                    'error' => 'Такая запись уже существует',
                    'duplicate' => true,
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Prepend globe emoji to mark PWA origin
            $message = "🌐 " . $message;

            // Save to database
            $saved = $dbService->addUserTabel($telegramId, $message);

            if (!$saved) {
                http_response_code(500);
                echo json_encode(['error' => 'Ошибка сохранения записи'], JSON_UNESCAPED_UNICODE);
                return;
            }
        } finally {
            // Always release the lock
            $relStmt = $db->prepare("SELECT RELEASE_LOCK(?)");
            $relStmt->bind_param('s', $lockKey);
            $relStmt->execute();
            $relStmt->close();
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
    /**
     * Resolve idle_reason from activity tree for PWA leaf keys (e.g. pnr_idle_1_def)
     */
    private function resolveIdleReasonFromTree(string $leafKey): ?string
    {
        $treeFile = __DIR__ . '/../data/activity-tree.json';
        if (!file_exists($treeFile)) return null;

        $tree = json_decode(file_get_contents($treeFile), true);
        if (!$tree) return null;

        // Determine which idle root to search in
        $idleRootKey = null;
        if (str_starts_with($leafKey, 'pnr_idle_')) $idleRootKey = 'Простой';
        elseif (str_starts_with($leafKey, 'smr_idle_')) $idleRootKey = 'ШМ_Простой';
        elseif (str_starts_with($leafKey, 'mob_idle_')) $idleRootKey = 'МобБриг_Простой';
        if (!$idleRootKey) return null;

        // Find the idle root node in the tree
        $idleRoot = $this->findNodeByKey($tree, $idleRootKey);
        if (!$idleRoot || empty($idleRoot['children'])) return null;

        // Search within idle root's children for the leaf key
        $path = [];
        if ($this->findNodePath($idleRoot['children'], $leafKey, $path)) {
            if (count($path) === 0) return null;
            if (count($path) === 1) return $this->stripEmoji($path[0]);
            $category = $this->stripEmoji($path[0]);
            $details = array_map([$this, 'stripEmoji'], array_slice($path, 1));
            return $category . ' (' . implode(', ', $details) . ')';
        }

        return null;
    }

    private function findNodeByKey(array $nodes, string $key): ?array
    {
        foreach ($nodes as $node) {
            if (($node['key'] ?? '') === $key) return $node;
            if (!empty($node['children'])) {
                $found = $this->findNodeByKey($node['children'], $key);
                if ($found) return $found;
            }
        }
        return null;
    }

    private function findNodePath(array $nodes, string $targetKey, array &$path): bool
    {
        foreach ($nodes as $node) {
            $key = $node['key'] ?? '';
            $label = $node['label'] ?? $key;

            if ($key === $targetKey) {
                $path[] = $label;
                return true;
            }

            if (!empty($node['children'])) {
                $path[] = $label;
                if ($this->findNodePath($node['children'], $targetKey, $path)) {
                    return true;
                }
                array_pop($path);
            }
        }
        return false;
    }

    private function stripEmoji(string $text): string
    {
        $cleaned = preg_replace('/[\x{1F000}-\x{1FFFF}\x{2300}-\x{23FF}\x{2600}-\x{27BF}\x{FE0E}\x{FE0F}\x{200D}\x{20E3}\x{E0020}-\x{E007F}]/u', '', $text);
        return trim($cleaned);
    }

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
        $fresh = $this->getFreshUserData($auth);
        $fio = $fresh['fio'];

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
        $fresh = $this->getFreshUserData($auth);
        $fio = $fresh['fio'];
        $fn  = $fresh['fn'];

        if (!$telegramId) {
            http_response_code(400);
            echo json_encode(['error' => 'Аккаунт не привязан к Telegram'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $activityType = trim($body['activity_type'] ?? '');
        $startDate    = trim($body['start_date'] ?? '');
        $endDate      = trim($body['end_date'] ?? '');

        if (!in_array($activityType, ['Больничный', 'Отпуск', 'Донорский день'])) {
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

        $emojiMap = ['Больничный' => '🏥', 'Отпуск' => '🏖', 'Донорский день' => '🩸'];
        $emoji = $emojiMap[$activityType] ?? '📋';

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
                if ($dayCount === 1) {
                    $summary = "🌐 <b>{$fio}</b>\r\n{$emoji} {$startDate}: {$activityType}";
                } else {
                    $summary = "🌐 <b>{$fio}</b>\r\n{$emoji} {$activityType} с {$startDate} по {$endDate} ({$dayCount} дн.)";
                }
                $messageService->sendToHistoryChatByFunctionalDirection($summary, $fn);
            } catch (\Throwable $e) {
                error_log("PWA Absence: TG notification failed: " . $e->getMessage());
            }
        }

        $msg = $saved === 1 ? "Запись добавлена" : "Записано дней: {$saved}";
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
        $fresh = $this->getFreshUserData($auth);
        $fio = $fresh['fio'];
        $fn  = $fresh['fn'];

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

    /**
     * Fetch fresh FIO and functional_direction from pwa_users.
     * Falls back to JWT payload if DB lookup fails.
     */
    private function getFreshUserData(array $auth): array
    {
        $fio = $auth['fio'] ?? '';
        $fn  = $auth['fn'] ?? '';
        $pwaUserId = (int) ($auth['pwa_user_id'] ?? 0);

        if ($pwaUserId > 0) {
            try {
                $db = getDb();
                $stmt = $db->prepare("SELECT fio, functional_direction FROM pwa_users WHERE id = ? AND is_active = 1");
                $stmt->bind_param('i', $pwaUserId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                if ($row) {
                    $fio = $row['fio'] ?? $fio;
                    $fn  = $row['functional_direction'] ?? $fn;
                }
            } catch (\Throwable $e) {
                error_log("PWA getFreshUserData: " . $e->getMessage());
            }
        }

        return ['fio' => $fio, 'fn' => $fn];
    }
}
