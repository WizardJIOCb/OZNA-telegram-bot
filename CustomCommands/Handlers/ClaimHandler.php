<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ClaimService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler для управления рекламациями
 */
class ClaimHandler implements CallbackHandlerInterface
{
    private MessageService $messageService;
    private AdminService $adminService;
    private ?ClaimService $claimService = null;
    private ?DatabaseService $databaseService = null;
    private ?TabelEntry $tabelEntry = null;
    private $db;

    /** Маппинг коротких ключей полей → колонки БД */
    private const FIELD_MAP = [
        'project' => 'project_code',
        'regnum'  => 'reg_number',
        'subject' => 'subject',
        'actdate' => 'act_date',
        'author'  => 'act_author',
        'equip'   => 'equipment',
        'serial'  => 'serial_number',
        'desc'    => 'description',
    ];

    /** Маппинг коротких ключей → [emoji, label] */
    private const FIELD_LABELS = [
        'project' => ['📁', 'Проект'],
        'regnum'  => ['🔢', 'Рег. номер'],
        'subject' => ['📌', 'Тема'],
        'actdate' => ['📅', 'Дата акта'],
        'author'  => ['👤', 'Автор акта'],
        'equip'   => ['⚙️', 'Оборудование'],
        'serial'  => ['🏷️', 'Зав. номер'],
        'desc'    => ['📝', 'Описание'],
    ];

    public function __construct(MessageService $messageService, AdminService $adminService)
    {
        $this->messageService = $messageService;
        $this->adminService = $adminService;
        
        error_log("ClaimHandler: Constructor called");
        
        try {
            $pdo = new \PDO(
                'mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4',
                'WizardJIOCb',
                '7qQ#28051961',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
            
            $this->db = $pdo;
            $this->claimService = new ClaimService($pdo);
            $this->databaseService = new DatabaseService();
            
            error_log("ClaimHandler: PDO connection and ClaimService successfully created");
        } catch (\Exception $e) {
            error_log("ClaimHandler: CRITICAL - Failed to initialize: " . $e->getMessage());
            $this->db = null;
            $this->claimService = null;
        }
    }

    /**
     * Get TabelEntry service (lazy initialization)
     */
    private function getTabelEntry(): ?TabelEntry
    {
        if ($this->tabelEntry === null) {
            try {
                $dbService = new DatabaseService();
                $this->tabelEntry = new TabelEntry($dbService, $this->messageService);
            } catch (\Exception $e) {
                error_log("ClaimHandler::getTabelEntry error: " . $e->getMessage());
                return null;
            }
        }
        return $this->tabelEntry;
    }

    public function canHandle(string $callbackData): bool
    {
        return str_starts_with($callbackData, 'claim_');
    }

    public function getPriority(): int
    {
        return 28;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = (string) $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        $callbackData = $callbackQuery->getData();
        
        error_log("ClaimHandler::handle - callbackData: '{$callbackData}', userId: {$userId}");
        
        // Проверка доступа: раздельная для создания (User+) и управления (Supervisor+)
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        
        $isCreationCallback = in_array($callbackData, ['claim_create', 'claim_ucreate', 'claim_confirm', 'claim_cancel'])
            || str_starts_with($callbackData, 'claim_cprj_p_')
            || str_starts_with($callbackData, 'claim_proj_')
            || str_starts_with($callbackData, 'claim_view_')
            || str_starts_with($callbackData, 'claim_tbl_')
            || str_starts_with($callbackData, 'claim_tblc_')
            || str_starts_with($callbackData, 'claim_close_');
        
        $requiredLevel = $isCreationCallback
            ? DatabaseService::ACCESS_LEVEL_USER
            : DatabaseService::ACCESS_LEVEL_SUPERVISOR;
        
        if ($userAccessLevel < $requiredLevel) {
            $msg = $isCreationCallback
                ? '❌ <b>Доступ запрещён</b>\n\nДля создания рекламаций требуется уровень доступа Пользователь или выше.'
                : '❌ <b>Доступ запрещён</b>\n\nДля управления рекламациями требуется уровень доступа Супервайзер или выше.';
            return $this->messageService->sendMessage($chatId, $msg);
        }
        
        if ($this->claimService === null) {
            return $this->messageService->sendMessage($chatId, '❌ <b>Ошибка</b>\n\nСервис рекламаций недоступен. Проверьте логи.');
        }
        
        // Маршрутизация
        if ($callbackData === 'claim_menu') {
            // Вход через меню управления — сбрасываем warranty контекст
            $conversation->notes['claim_return_warranty'] = false;
            unset($conversation->notes['claim_warranty_back_cb']);
            $conversation->update();
            return $this->showClaimMenu($chatId);
        }
        
        if ($callbackData === 'claim_list') {
            // Вход через список — сбрасываем warranty контекст
            $conversation->notes['claim_return_warranty'] = false;
            unset($conversation->notes['claim_warranty_back_cb']);
            $conversation->update();
            return $this->showClaimsList($chatId, 1);
        }
        
        if (preg_match('/^claim_list_p_(\d+)$/', $callbackData, $matches)) {
            return $this->showClaimsList($chatId, (int) $matches[1]);
        }
        
        if (preg_match('/^claim_bypj_(.+)$/', $callbackData, $matches)) {
            // Backward compat: redirect to new project status selection
            return $this->showProjectStatusSelection($chatId, $matches[1], null);
        }
        
        // ── ИЕРАРХИЧЕСКИЙ СПИСОК (L1-L4) ──
        
        // L2: Проекты внутри БН
        if (preg_match('/^claim_bn_(\d+)$/', $callbackData, $matches)) {
            return $this->showBnProjects($chatId, (int) $matches[1]);
        }
        
        // L3: Выбор статуса для проекта (из БН) — парсим справа: последний _\d+ = bnId
        if (str_starts_with($callbackData, 'claim_pjbn_')) {
            $remaining = substr($callbackData, strlen('claim_pjbn_'));
            $lastUnderscore = strrpos($remaining, '_');
            if ($lastUnderscore !== false) {
                $code = substr($remaining, 0, $lastUnderscore);
                $bnId = (int) substr($remaining, $lastUnderscore + 1);
                return $this->showProjectStatusSelection($chatId, $code, $bnId);
            }
        }
        
        // L3: Выбор статуса для проекта (без БН)
        if (str_starts_with($callbackData, 'claim_pjst_')) {
            $code = substr($callbackData, strlen('claim_pjst_'));
            return $this->showProjectStatusSelection($chatId, $code, null);
        }
        
        // L4: Список рекламаций (из БН) — парсим справа: bnId, status, code
        if (str_starts_with($callbackData, 'claim_pjcb_')) {
            $remaining = substr($callbackData, strlen('claim_pjcb_'));
            // Format: {code}_{a|c}_{bnId}
            $lastUnderscore = strrpos($remaining, '_');
            if ($lastUnderscore !== false) {
                $bnId = (int) substr($remaining, $lastUnderscore + 1);
                $rest = substr($remaining, 0, $lastUnderscore);
                // rest = {code}_{a|c}
                $statusPos = strrpos($rest, '_');
                if ($statusPos !== false) {
                    $statusChar = substr($rest, $statusPos + 1);
                    $code = substr($rest, 0, $statusPos);
                    $status = $statusChar === 'a' ? 'active' : 'closed';
                    return $this->showProjectClaimsByStatus($chatId, $code, $status, $bnId);
                }
            }
        }
        
        // L4: Список рекламаций (без БН) — парсим справа: status, code
        if (str_starts_with($callbackData, 'claim_pjcl_')) {
            $remaining = substr($callbackData, strlen('claim_pjcl_'));
            // Format: {code}_{a|c}
            $lastUnderscore = strrpos($remaining, '_');
            if ($lastUnderscore !== false) {
                $statusChar = substr($remaining, $lastUnderscore + 1);
                $code = substr($remaining, 0, $lastUnderscore);
                $status = $statusChar === 'a' ? 'active' : 'closed';
                return $this->showProjectClaimsByStatus($chatId, $code, $status, null);
            }
        }
        
        if (preg_match('/^claim_view_(\d+)$/', $callbackData, $matches)) {
            return $this->showClaimDetails($chatId, (int) $matches[1], $userId, $conversation);
        }
        
        // Tabel entry from warranty claim
        if (preg_match('/^claim_tbl_(\d+)$/', $callbackData, $matches)) {
            return $this->showTabelConfirmation($chatId, $userId, (int) $matches[1], $conversation);
        }
        if (preg_match('/^claim_tblc_(\d+)$/', $callbackData, $matches)) {
            return $this->confirmTabelEntry($chatId, $userId, (int) $matches[1], $conversation);
        }
        
        if ($callbackData === 'claim_create') {
            return $this->startCreateClaim($chatId, $conversation);
        }
        
        if ($callbackData === 'claim_ucreate') {
            return $this->startUserCreateClaim($chatId, $userId, $conversation);
        }
        
        if (preg_match('/^claim_cprj_p_(\d+)$/', $callbackData, $matches)) {
            return $this->startCreateClaim($chatId, $conversation, (int) $matches[1]);
        }
        
        if (preg_match('/^claim_proj_(.+)$/', $callbackData, $matches)) {
            return $this->selectProject($chatId, $userId, $matches[1], $conversation);
        }
        
        if ($callbackData === 'claim_confirm') {
            return $this->confirmClaim($chatId, $userId, $conversation);
        }
        
        if ($callbackData === 'claim_cancel') {
            return $this->cancelClaim($chatId, $userId, $conversation);
        }
        
        if ($callbackData === 'claim_mod') {
            return $this->showModerationPanel($chatId);
        }
        
        if (preg_match('/^claim_modv_(\d+)$/', $callbackData, $matches)) {
            return $this->showModerationDetails($chatId, (int) $matches[1]);
        }
        
        if (preg_match('/^claim_apr_(\d+)$/', $callbackData, $matches)) {
            return $this->approveClaim($chatId, $userId, (int) $matches[1]);
        }
        
        if (preg_match('/^claim_rejs_(\d+)$/', $callbackData, $matches)) {
            return $this->startRejectClaim($chatId, $userId, (int) $matches[1], $conversation);
        }
        
        // Подтверждение/отклонение закрытия (pending_close)
        if (preg_match('/^claim_aprc_(\d+)$/', $callbackData, $matches)) {
            return $this->approveCloseClaim($chatId, $userId, (int) $matches[1]);
        }
        
        if (preg_match('/^claim_rejc_(\d+)$/', $callbackData, $matches)) {
            return $this->rejectCloseClaimAction($chatId, $userId, (int) $matches[1]);
        }
        
        // ── КОНСТРУКТОР (редактирование) ──
        
        // Очистка awaiting_* состояний при любом callback (как в ProjectHandler)
        // Сбрасываем все текстовые состояния рекламации при нажатии любой кнопки
        $claimAction = $conversation->notes['claim_action'] ?? null;
        if ($claimAction && (str_starts_with($claimAction, 'edit_') || str_starts_with($claimAction, 'user_enter_') || $claimAction === 'enter_close_reason' || $claimAction === 'enter_reject_reason')) {
            error_log("ClaimHandler::handle - Clearing stale claim input state: {$claimAction}");
            $conversation->notes['claim_action'] = null;
            $conversation->notes['claim_edit_id'] = null;
            $conversation->notes['claim_close_id'] = null;
            $conversation->notes['claim_reject_id'] = null;
            $conversation->update();
        }
        
        if (preg_match('/^claim_edit_(\d+)$/', $callbackData, $matches)) {
            return $this->showClaimConstructor($chatId, (int) $matches[1]);
        }
        
        if (preg_match('/^claim_edf_(\w+)_(\d+)$/', $callbackData, $matches)) {
            return $this->showEditField($chatId, (int) $matches[2], $matches[1]);
        }
        
        if (preg_match('/^claim_eds_(\w+)_(\d+)$/', $callbackData, $matches)) {
            return $this->promptEditField($chatId, (int) $matches[2], $matches[1], $conversation);
        }
        
        if (preg_match('/^claim_edprj_(\d+)$/', $callbackData, $matches)) {
            return $this->showEditProjectSelector($chatId, (int) $matches[1], $conversation);
        }
        
        if (preg_match('/^claim_edpp_(\d+)_(\d+)$/', $callbackData, $matches)) {
            return $this->showEditProjectSelector($chatId, (int) $matches[2], $conversation, (int) $matches[1]);
        }
        
        if (str_starts_with($callbackData, 'claim_edps_')) {
            $remaining = substr($callbackData, strlen('claim_edps_'));
            $lastUnderscore = strrpos($remaining, '_');
            if ($lastUnderscore !== false) {
                $projectCode = substr($remaining, 0, $lastUnderscore);
                $claimId = (int) substr($remaining, $lastUnderscore + 1);
                return $this->selectEditProject($chatId, $claimId, $projectCode);
            }
        }
        
        // ── ЗАКРЫТИЕ / ПЕРЕОТКРЫТИЕ ──
        
        if (preg_match('/^claim_close_(\d+)$/', $callbackData, $matches)) {
            return $this->startCloseClaim($chatId, $userId, (int) $matches[1], $conversation);
        }
        
        if (preg_match('/^claim_reopen_(\d+)$/', $callbackData, $matches)) {
            return $this->reopenClaimAction($chatId, $userId, (int) $matches[1]);
        }
        
        // ── УДАЛЕНИЕ ──
        
        if (preg_match('/^claim_del_(\d+)$/', $callbackData, $matches)) {
            return $this->confirmDeleteStep1($chatId, (int) $matches[1]);
        }
        
        if (preg_match('/^claim_delc_(\d+)$/', $callbackData, $matches)) {
            return $this->confirmDeleteStep2($chatId, (int) $matches[1]);
        }
        
        if (preg_match('/^claim_delf_(\d+)$/', $callbackData, $matches)) {
            return $this->executeDeleteClaim($chatId, $userId, (int) $matches[1]);
        }
        
        error_log("ClaimHandler::handle - NO MATCH for: '{$callbackData}'");
        return $this->showClaimMenu($chatId);
    }

    // ──────────────────────────────────────────────
    // ГЛАВНОЕ МЕНЮ
    // ──────────────────────────────────────────────

    private function showClaimMenu(string $chatId): ServerResponse
    {
        $text = "📋 <b>Рекламации</b>\n\n";
        $text .= "Управление рекламациями по проектам.\n";
        $text .= "Выберите действие:";
        
        $pendingCount = $this->claimService->getPendingClaimsCount();
        $pendingCloseCount = $this->claimService->getPendingCloseClaimsCount();
        $totalPending = $pendingCount + $pendingCloseCount;
        $modButtonText = '🔔 На модерации';
        if ($totalPending > 0) {
            $modButtonText .= " ({$totalPending})";
        }
        
        $keyboard = [
            [['text' => '➕ Добавить рекламацию', 'callback_data' => 'claim_create']],
            [['text' => '📄 Все рекламации', 'callback_data' => 'claim_list']],
            [['text' => $modButtonText, 'callback_data' => 'claim_mod']],
            [['text' => '⬅️ Назад', 'callback_data' => 'pm_menu']],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // УРОВЕНЬ 1: ГРУППИРОВКА ПО БН + НЕГРУППИРОВАННЫЕ ПРОЕКТЫ
    // ──────────────────────────────────────────────

    private function showClaimsList(string $chatId, int $page = 1): ServerResponse
    {
        $summary = $this->claimService->getGroupedClaimsSummary();
        
        if (empty($summary)) {
            $text = "📋 <b>Рекламации</b>\n\n";
            $text .= "Активных и закрытых рекламаций нет.";
            $keyboard = [
                [['text' => '➕ Добавить рекламацию', 'callback_data' => 'claim_create']],
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_menu']],
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Агрегируем: БН группы и негруппированные проекты
        $bnGroups = [];   // [bnId => ['name' => '...', 'count' => N]]
        $ungrouped = [];  // [projectCode => count]
        $totalCount = 0;
        
        foreach ($summary as $row) {
            $cnt = (int) $row['cnt'];
            $totalCount += $cnt;
            
            if ($row['bn_id'] !== null) {
                $bnId = (int) $row['bn_id'];
                if (!isset($bnGroups[$bnId])) {
                    $bnGroups[$bnId] = ['name' => $row['bn_name'], 'count' => 0];
                }
                $bnGroups[$bnId]['count'] += $cnt;
            } else {
                $code = $row['project_code'];
                if (!isset($ungrouped[$code])) {
                    $ungrouped[$code] = 0;
                }
                $ungrouped[$code] += $cnt;
            }
        }
        
        // Сортируем БН по имени, проекты по коду
        uasort($bnGroups, fn($a, $b) => strcmp($a['name'], $b['name']));
        ksort($ungrouped);
        
        // Собираем все элементы для пагинации
        $items = [];
        foreach ($bnGroups as $bnId => $info) {
            $items[] = ['type' => 'bn', 'id' => $bnId, 'name' => $info['name'], 'count' => $info['count']];
        }
        foreach ($ungrouped as $code => $count) {
            $items[] = ['type' => 'project', 'code' => $code, 'count' => $count];
        }
        
        // Пагинация
        $perPage = 10;
        $totalItems = count($items);
        $totalPages = (int) ceil($totalItems / $perPage);
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        $pageItems = array_slice($items, $offset, $perPage);
        
        $text = "📋 <b>Все рекламации</b>\n\n";
        $text .= "Всего: {$totalCount}";
        if ($totalPages > 1) {
            $text .= " | Страница {$page}/{$totalPages}";
        }
        $text .= "\nВыберите группу или проект:";
        
        $keyboard = [];
        foreach ($pageItems as $item) {
            if ($item['type'] === 'bn') {
                $buttonText = "🏢 {$item['name']} ({$item['count']})";
                $keyboard[] = [['text' => $buttonText, 'callback_data' => 'claim_bn_' . $item['id']]];
            } else {
                $buttonText = "📁 {$item['code']} ({$item['count']})";
                $callbackData = 'claim_pjst_' . $item['code'];
                if (strlen($callbackData) <= 64) {
                    $keyboard[] = [['text' => $buttonText, 'callback_data' => $callbackData]];
                }
            }
        }
        
        // Навигация по страницам
        if ($totalPages > 1) {
            $navButtons = [];
            if ($page > 1) {
                $navButtons[] = ['text' => '⬅️ Пред.', 'callback_data' => 'claim_list_p_' . ($page - 1)];
            }
            $navButtons[] = ['text' => "📄 {$page}/{$totalPages}", 'callback_data' => 'noop'];
            if ($page < $totalPages) {
                $navButtons[] = ['text' => '➡️ След.', 'callback_data' => 'claim_list_p_' . ($page + 1)];
            }
            $keyboard[] = $navButtons;
        }
        
        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => 'claim_menu']];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // УРОВЕНЬ 2: ПРОЕКТЫ ВНУТРИ БН
    // ──────────────────────────────────────────────

    private function showBnProjects(string $chatId, int $bnId): ServerResponse
    {
        $bnName = $this->claimService->getBusinessUnitName($bnId);
        
        if ($bnName === null) {
            return $this->messageService->sendMessage($chatId, '❌ Бизнес-направление не найдено.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']],
            ]);
        }
        
        $projects = $this->claimService->getProjectClaimCountsByBn($bnId);
        
        if (empty($projects)) {
            $text = "🏢 <b>{$bnName}</b>\n\n";
            $text .= "Рекламаций по проектам этого БН нет.";
            $keyboard = [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']],
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        $totalCount = 0;
        foreach ($projects as $p) {
            $totalCount += (int) $p['cnt'];
        }
        
        $text = "🏢 <b>{$bnName}</b>\n\n";
        $text .= "Рекламаций: {$totalCount}\n";
        $text .= "Выберите проект:";
        
        $keyboard = [];
        foreach ($projects as $p) {
            $buttonText = "📁 {$p['project_code']} ({$p['cnt']})";
            $callbackData = 'claim_pjbn_' . $p['project_code'] . '_' . $bnId;
            if (strlen($callbackData) <= 64) {
                $keyboard[] = [['text' => $buttonText, 'callback_data' => $callbackData]];
            }
        }
        
        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // УРОВЕНЬ 3: ПРОЕКТ — АКТИВНЫЕ РЕКЛАМАЦИИ + КНОПКА ЗАКРЫТЫХ
    // ──────────────────────────────────────────────

    private function showProjectStatusSelection(string $chatId, string $projectCode, ?int $bnId): ServerResponse
    {
        $counts = $this->claimService->getClaimCountsByProjectAndStatus($projectCode);
        $activeClaims = $this->claimService->getClaimsByProject($projectCode, 'active');
        
        $activeCount = $counts['active'];
        $closedCount = $counts['closed'];
        $total = $activeCount + $closedCount;
        
        $backCallback = $bnId !== null ? 'claim_bn_' . $bnId : 'claim_list';
        
        if ($total === 0) {
            $text = "📋 <b>Рекламации — {$projectCode}</b>\n\n";
            $text .= "Рекламаций нет.";
            $keyboard = [
                [['text' => '⬅️ Назад', 'callback_data' => $backCallback]],
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        $text = "📋 <b>Рекламации — {$projectCode}</b>\n\n";
        
        if ($activeCount > 0) {
            $text .= "✅ Активные: {$activeCount}";
        } else {
            $text .= "Активных рекламаций нет.";
        }
        if ($closedCount > 0) {
            $text .= " | 📦 Закрытые: {$closedCount}";
        }
        
        $keyboard = [];
        
        // Сразу показываем активные рекламации
        foreach ($activeClaims as $claim) {
            $icon = $this->getStatusIcon($claim['status']);
            $subject = $claim['subject'] ?? '—';
            $buttonText = "{$icon} {$subject}";
            if (mb_strlen($buttonText) > 40) {
                $buttonText = mb_substr($buttonText, 0, 37) . '...';
            }
            $keyboard[] = [['text' => $buttonText, 'callback_data' => 'claim_view_' . $claim['id']]];
        }
        
        // Кнопка закрытых рекламаций (если есть)
        if ($closedCount > 0) {
            if ($bnId !== null) {
                $cb = 'claim_pjcb_' . $projectCode . '_c_' . $bnId;
            } else {
                $cb = 'claim_pjcl_' . $projectCode . '_c';
            }
            if (strlen($cb) <= 64) {
                $keyboard[] = [['text' => "📦 Закрытые рекламации ({$closedCount})", 'callback_data' => $cb]];
            }
        }
        
        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => $backCallback]];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // УРОВЕНЬ 4: РЕКЛАМАЦИИ ПО ПРОЕКТУ + СТАТУС
    // ──────────────────────────────────────────────

    private function showProjectClaimsByStatus(string $chatId, string $projectCode, string $status, ?int $bnId): ServerResponse
    {
        $claims = $this->claimService->getClaimsByProject($projectCode, $status);
        
        $statusName = $status === 'active' ? 'Активные' : 'Закрытые';
        $statusIcon = $status === 'active' ? '✅' : '📦';
        
        $text = "{$statusIcon} <b>{$statusName} рекламации — {$projectCode}</b>\n\n";
        
        if (empty($claims)) {
            $text .= "Рекламаций нет.";
        } else {
            $text .= "Найдено: " . count($claims);
        }
        
        $keyboard = [];
        foreach ($claims as $claim) {
            $icon = $this->getStatusIcon($claim['status']);
            $subject = $claim['subject'] ?? '—';
            $buttonText = "{$icon} {$subject}";
            if (mb_strlen($buttonText) > 40) {
                $buttonText = mb_substr($buttonText, 0, 37) . '...';
            }
            $keyboard[] = [['text' => $buttonText, 'callback_data' => 'claim_view_' . $claim['id']]];
        }
        
        // Кнопка "Назад" ведёт на уровень 3
        if ($bnId !== null) {
            $backCallback = 'claim_pjbn_' . $projectCode . '_' . $bnId;
        } else {
            $backCallback = 'claim_pjst_' . $projectCode;
        }
        if (strlen($backCallback) > 64) {
            $backCallback = 'claim_list';
        }
        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => $backCallback]];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // ДЕТАЛИ РЕКЛАМАЦИИ
    // ──────────────────────────────────────────────

    private function showClaimDetails(string $chatId, int $claimId, ?int $userId = null, ?Conversation $conversation = null): ServerResponse
    {
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.');
        }
        
        $statusIcon = $this->getStatusIcon($claim['status']);
        $statusName = $this->getStatusName($claim['status']);
        $createdAt = date('d.m.Y H:i', strtotime($claim['created_at']) + 2 * 3600);
        
        $text = "📋 <b>Рекламация</b>\n\n";
        $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n";
        $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
        $text .= "📌 <b>Тема:</b> {$claim['subject']}\n";
        $text .= "📅 <b>Дата акта:</b> {$claim['act_date']}\n";
        $text .= "👤 <b>Автор акта:</b> {$claim['act_author']}\n";
        $text .= "⚙️ <b>Оборудование:</b> {$claim['equipment']}\n";
        $text .= "🏷️ <b>Зав. номер:</b> {$claim['serial_number']}\n";
        $text .= "📝 <b>Описание:</b> {$claim['description']}\n\n";
        $text .= "{$statusIcon} <b>Статус:</b> {$statusName}\n";
        $text .= "👤 <b>Создал:</b> " . ($claim['creator_fio'] ?? 'Неизвестно') . "\n";
        $text .= "📅 <b>Создано:</b> {$createdAt}\n";
        
        if ($claim['moderated_by'] && $claim['moderator_fio']) {
            $moderatedAt = date('d.m.Y H:i', strtotime($claim['moderated_at']) + 2 * 3600);
            $text .= "\n👨‍💼 <b>Модератор:</b> {$claim['moderator_fio']}\n";
            $text .= "📅 <b>Модерация:</b> {$moderatedAt}\n";
            if ($claim['moderation_comment']) {
                $text .= "💬 <b>Комментарий:</b> {$claim['moderation_comment']}\n";
            }
        }
        
        // Информация о закрытии
        if (!empty($claim['closed_by'])) {
            $closedAt = date('d.m.Y H:i', strtotime($claim['closed_at']) + 2 * 3600);
            $text .= "\n🔒 <b>Закрыл:</b> " . ($claim['closer_fio'] ?? 'Неизвестно') . "\n";
            $text .= "📅 <b>Дата закрытия:</b> {$closedAt}\n";
            if (!empty($claim['close_reason'])) {
                $text .= "💬 <b>Причина:</b> {$claim['close_reason']}\n";
            }
        }
        
        // Информация о переоткрытии
        if (!empty($claim['reopened_by'])) {
            $reopenedAt = date('d.m.Y H:i', strtotime($claim['reopened_at']) + 2 * 3600);
            $text .= "\n🔓 <b>Переоткрыл:</b> " . ($claim['reopener_fio'] ?? 'Неизвестно') . "\n";
            $text .= "📅 <b>Дата переоткрытия:</b> {$reopenedAt}\n";
        }
        
        // Check if viewing from warranty context
        $fromWarranty = $conversation && !empty($conversation->notes['claim_return_warranty']);
        $userAccessLevel = $userId ? $this->adminService->getUserAccessLevel($userId) : DatabaseService::ACCESS_LEVEL_SUPERVISOR;
        
        // Кнопки зависят от статуса и контекста
        $keyboard = [];
        
        if ($fromWarranty) {
            // Warranty context: tabel + close for all users
            if ($claim['status'] === 'active') {
                $keyboard[] = [['text' => '📋 Отметить в табель', 'callback_data' => 'claim_tbl_' . $claimId]];
                $keyboard[] = [['text' => '🔒 Закрыть рекламацию', 'callback_data' => 'claim_close_' . $claimId]];
            }
        } else {
            // Regular context: edit/close/reopen for supervisor+
            if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
                if ($claim['status'] === 'active') {
                    $keyboard[] = [['text' => '✏️ Редактировать', 'callback_data' => 'claim_edit_' . $claimId]];
                    $keyboard[] = [['text' => '🔒 Закрыть', 'callback_data' => 'claim_close_' . $claimId]];
                } elseif ($claim['status'] === 'closed') {
                    $keyboard[] = [['text' => '🔓 Открыть заново', 'callback_data' => 'claim_reopen_' . $claimId]];
                }
            }
        }
        
        // Back button depends on context
        if ($fromWarranty) {
            $warrantyBackCb = $conversation->notes['claim_warranty_back_cb'] ?? 'category_Табель';
            $keyboard[] = [['text' => '⬅️ Назад к гарантии', 'callback_data' => $warrantyBackCb]];
        } else {
            $backCallback = 'claim_pjst_' . $claim['project_code'];
            if (strlen($backCallback) > 64) {
                $backCallback = 'claim_list';
            }
            $keyboard[] = [['text' => '⬅️ Назад к проекту', 'callback_data' => $backCallback]];
        }
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // ТАБЕЛИРОВАНИЕ ПО РЕКЛАМАЦИИ
    // ──────────────────────────────────────────────

    private function showTabelConfirmation(string $chatId, int $userId, int $claimId, Conversation $conversation): ServerResponse
    {
        // Supervisors cannot submit tabel entries
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        if ($userAccessLevel === DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            return $this->messageService->sendMessage($chatId, '⚠️ Супервайзеры не могут формировать и направлять отчет в табель.');
        }
        
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.');
        }
        
        $date = $conversation->notes['tabel_data'] ?? date('d.m.Y');
        $projectCode = $claim['project_code'];
        $subject = $claim['subject'] ?? 'Без темы';
        
        $text = "📋 <b>Записать в табель?</b>\n\n";
        $text .= "🛡️ <b>Активность:</b> Работы по гарантии\n";
        $text .= "📌 <b>Рекламация:</b> {$subject}\n";
        $text .= "📁 <b>Проект:</b> {$projectCode}\n";
        $text .= "📅 <b>Дата:</b> {$date}\n";
        
        $keyboard = [
            [['text' => '✅ Да', 'callback_data' => 'claim_tblc_' . $claimId]],
            [['text' => '❌ Нет', 'callback_data' => 'claim_view_' . $claimId]],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function confirmTabelEntry(string $chatId, int $userId, int $claimId, Conversation $conversation): ServerResponse
    {
        // Supervisors cannot submit tabel entries
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        if ($userAccessLevel === DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            return $this->messageService->sendMessage($chatId, '⚠️ Супервайзеры не могут формировать и направлять отчет в табель.');
        }
        
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.');
        }
        
        $tabelEntry = $this->getTabelEntry();
        if ($tabelEntry === null) {
            return $this->messageService->sendMessage($chatId, '❌ Сервис табеля недоступен. Попробуйте позже.');
        }
        
        $fio = $this->adminService->getUserFio($userId);
        if (empty($fio)) {
            $fio = 'Неизвестный пользователь';
        }
        
        $date = $conversation->notes['tabel_data'] ?? date('d.m.Y');
        $projectCode = $claim['project_code'];
        $claimSubject = $claim['subject'] ?? 'Без темы';
        
        $success = $tabelEntry->addEntry(
            $userId,
            $fio,
            $date,
            $projectCode,
            'РабГарантии',
            $chatId,
            $claimSubject
        );
        
        if ($success) {
            $text = "✅ <b>Записано в табель</b>\n\n";
            $text .= "🛡️ Работы по гарантии ({$claimSubject})\n";
            $text .= "📁 Проект: {$projectCode}\n";
            $text .= "📅 Дата: {$date}\n";
        } else {
            $text = "❌ <b>Не удалось записать в табель</b>\n\nПопробуйте позже.";
        }
        
        $keyboard = [];
        $fromWarranty = !empty($conversation->notes['claim_return_warranty']);
        if ($fromWarranty) {
            $warrantyBackCb = $conversation->notes['claim_warranty_back_cb'] ?? 'category_Табель';
            $keyboard[] = [['text' => '⬅️ Назад к гарантии', 'callback_data' => $warrantyBackCb]];
        } else {
            $keyboard[] = [['text' => '⬅️ В меню рекламаций', 'callback_data' => 'claim_menu']];
        }
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // ВИЗАРД: СОЗДАНИЕ РЕКЛАМАЦИИ
    // ──────────────────────────────────────────────

    /**
     * Шаг 1: Выбор проекта
     */
    private function startCreateClaim(string $chatId, Conversation $conversation, int $page = 1): ServerResponse
    {
        // Очищаем предыдущие состояния
        $this->clearClaimState($conversation);
        
        // Получаем список проектов (как в по-проектном отчёте)
        $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector(
            $this->databaseService
        );
        $allProjects = $projectSelector->getAllProjectsIncludingPending();
        $allProjects = $this->databaseService->sortProjectsIntelligently($allProjects);
        
        if (empty($allProjects)) {
            $text = "❌ <b>Нет доступных проектов</b>\n\nСначала добавьте проекты в систему.";
            $keyboard = [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_menu']],
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Пагинация: 33 проекта на страницу, по 3 в ряд
        $perPage = 33;
        $totalProjects = count($allProjects);
        $totalPages = (int) ceil($totalProjects / $perPage);
        if ($page < 1) $page = 1;
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $perPage;
        $projectsOnPage = array_slice($allProjects, $offset, $perPage);
        
        $text = "➕ <b>Создание рекламации</b>\n\n";
        $text .= "Шаг 1 из 8: Выберите проект:\n";
        $text .= "Страница {$page} из {$totalPages} (Всего: {$totalProjects})";
        
        $keyboard = [];
        
        // Кнопки проектов по 3 в ряд
        $row = [];
        foreach ($projectsOnPage as $index => $project) {
            $callbackData = 'claim_proj_' . $project;
            if (strlen($callbackData) <= 64) {
                $row[] = ['text' => $project, 'callback_data' => $callbackData];
            }
            
            if (count($row) == 4 || $index == count($projectsOnPage) - 1) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        
        // Навигация по страницам
        if ($totalPages > 1) {
            $navRow = [];
            if ($page > 1) {
                $navRow[] = ['text' => '⬅️ Назад', 'callback_data' => 'claim_cprj_p_' . ($page - 1)];
            }
            $navRow[] = ['text' => "📖 {$page}/{$totalPages}", 'callback_data' => 'noop'];
            if ($page < $totalPages) {
                $navRow[] = ['text' => '➡️ Вперед', 'callback_data' => 'claim_cprj_p_' . ($page + 1)];
            }
            $keyboard[] = $navRow;
        }
        
        $keyboard[] = [['text' => '❌ Отмена', 'callback_data' => 'claim_menu']];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Шаг 2: Проект выбран, запрос рег. номера
     */
    private function selectProject(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        // Очищаем все предыдущие состояния
        $this->clearAllOtherStates($conversation);
        
        $conversation->notes['claim_action'] = 'enter_reg_number';
        $conversation->notes['claim_project'] = $projectCode;
        $conversation->update();
        
        $text = "➕ <b>Создание рекламации</b>\n\n";
        $text .= "📁 <b>Проект:</b> {$projectCode}\n\n";
        $text .= "Шаг 2 из 8: Введите <b>регистрационный номер</b>:";
        
        $keyboard = [
            [['text' => '❌ Отмена', 'callback_data' => 'claim_cancel']],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // УПРОЩЁННЫЙ ВИЗАРД ДЛЯ ПОЛЬЗОВАТЕЛЕЙ (4 шага)
    // ──────────────────────────────────────────────

    /**
     * Старт упрощённого создания рекламации для Пользователей.
     * Автозаполнение: проект, дата, рег. номер, автор.
     * Ввод: тема, оборудование, зав. номер, описание.
     */
    private function startUserCreateClaim(string $chatId, int $userId, Conversation $conversation): ServerResponse
    {
        // Очищаем предыдущие состояния
        $this->clearClaimState($conversation);
        $this->clearAllOtherStates($conversation);
        
        // Авто-заполнение из контекста табеля
        $projectCode = $conversation->notes['project_code'] ?? null;
        $tabelDate = $conversation->notes['tabel_data'] ?? date('d.m.Y');
        $userFio = $this->adminService->getUserFio($userId);
        
        if (empty($projectCode) || $projectCode === 'Unknown') {
            return $this->messageService->sendMessage($chatId, "❌ <b>Ошибка</b>\n\nНе удалось определить проект.\nПожалуйста, выберите проект в табеле перед добавлением рекламации.", [
                [['text' => '⬅️ Вернуться в табель', 'callback_data' => 'category_Табель']],
            ]);
        }
        
        // Заполняем автоматические поля
        $conversation->notes['claim_project'] = $projectCode;
        $conversation->notes['claim_act_date'] = $tabelDate;
        $conversation->notes['claim_reg_number'] = '-';
        $conversation->notes['claim_act_author'] = !empty($userFio) ? $userFio : 'Не указано';
        $conversation->notes['claim_user_mode'] = true;
        $conversation->notes['claim_action'] = 'user_enter_subject';
        $conversation->update();
        
        $text = "➕ <b>Добавление рекламации</b>\n\n";
        $text .= "📁 <b>Проект:</b> {$projectCode}\n";
        $text .= "📅 <b>Дата:</b> {$tabelDate}\n";
        $text .= "👤 <b>Автор:</b> {$conversation->notes['claim_act_author']}\n\n";
        $text .= "Шаг 1 из 4: Введите <b>тему рекламации</b>:";
        
        $keyboard = [
            [['text' => '❌ Отмена', 'callback_data' => 'claim_cancel']],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    public function handleTextInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        $action = $conversation->notes['claim_action'] ?? null;
        
        error_log("ClaimHandler::handleTextInput - action: {$action}, text: '{$text}'");
        
        switch ($action) {
            case 'enter_reg_number':
                $conversation->notes['claim_reg_number'] = trim($text);
                $conversation->notes['claim_action'] = 'enter_subject';
                $conversation->update();
                return $this->showWizardPrompt($chatId, $conversation, 3, 'тему рекламации');
                
            case 'enter_subject':
                $conversation->notes['claim_subject'] = trim($text);
                $conversation->notes['claim_action'] = 'enter_act_date';
                $conversation->update();
                return $this->showWizardPrompt($chatId, $conversation, 4, 'дату акта');
                
            case 'enter_act_date':
                $conversation->notes['claim_act_date'] = trim($text);
                $conversation->notes['claim_action'] = 'enter_act_author';
                $conversation->update();
                return $this->showWizardPrompt($chatId, $conversation, 5, 'автора акта');
                
            case 'enter_act_author':
                $conversation->notes['claim_act_author'] = trim($text);
                $conversation->notes['claim_action'] = 'enter_equipment';
                $conversation->update();
                return $this->showWizardPrompt($chatId, $conversation, 6, 'наименование оборудования');
                
            case 'enter_equipment':
                $conversation->notes['claim_equipment'] = trim($text);
                $conversation->notes['claim_action'] = 'enter_serial_number';
                $conversation->update();
                return $this->showWizardPrompt($chatId, $conversation, 7, 'заводской номер');
                
            case 'enter_serial_number':
                $conversation->notes['claim_serial_number'] = trim($text);
                $conversation->notes['claim_action'] = 'enter_description';
                $conversation->update();
                return $this->showWizardPrompt($chatId, $conversation, 8, 'краткое описание');
                
            case 'enter_description':
                $conversation->notes['claim_description'] = trim($text);
                $conversation->notes['claim_action'] = null;
                $conversation->update();
                return $this->showConfirmation($chatId, $conversation);
            
            // ── Упрощённый визард для Пользователей (4 шага) ──
            
            case 'user_enter_subject':
                $conversation->notes['claim_subject'] = trim($text);
                $conversation->notes['claim_action'] = 'user_enter_equipment';
                $conversation->update();
                return $this->showWizardPrompt($chatId, $conversation, 2, 'наименование оборудования', 4);
                
            case 'user_enter_equipment':
                $conversation->notes['claim_equipment'] = trim($text);
                $conversation->notes['claim_action'] = 'user_enter_serial';
                $conversation->update();
                return $this->showWizardPrompt($chatId, $conversation, 3, 'заводской номер', 4);
                
            case 'user_enter_serial':
                $conversation->notes['claim_serial_number'] = trim($text);
                $conversation->notes['claim_action'] = 'user_enter_desc';
                $conversation->update();
                return $this->showWizardPrompt($chatId, $conversation, 4, 'краткое описание', 4);
                
            case 'user_enter_desc':
                $conversation->notes['claim_description'] = trim($text);
                $conversation->notes['claim_action'] = null;
                $conversation->update();
                return $this->showConfirmation($chatId, $conversation);
                
            case 'enter_reject_reason':
                return $this->processRejectClaim($chatId, $userId, $text, $conversation);
                
            case 'edit_regnum':
            case 'edit_subject':
            case 'edit_actdate':
            case 'edit_author':
            case 'edit_equip':
            case 'edit_serial':
            case 'edit_desc':
                return $this->processEditFieldInput($chatId, $userId, $text, $conversation);
                
            case 'enter_close_reason':
                return $this->processCloseClaim($chatId, $userId, $text, $conversation);
                
            default:
                error_log("ClaimHandler::handleTextInput - unknown action: {$action}");
                return $this->showClaimMenu($chatId);
        }
    }

    /**
     * Показать prompt визарда с текущей сводкой
     */
    private function showWizardPrompt(string $chatId, Conversation $conversation, int $step, string $fieldName, int $totalSteps = 8): ServerResponse
    {
        $text = "➕ <b>Добавление рекламации</b>\n\n";
        $text .= $this->buildSummary($conversation);
        $text .= "\nШаг {$step} из {$totalSteps}: Введите <b>{$fieldName}</b>:";
        
        $keyboard = [
            [['text' => '❌ Отмена', 'callback_data' => 'claim_cancel']],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Показать сводку перед подтверждением
     */
    private function showConfirmation(string $chatId, Conversation $conversation): ServerResponse
    {
        $text = "➕ <b>Создание рекламации — подтверждение</b>\n\n";
        $text .= $this->buildSummary($conversation);
        $text .= "\n✅ Все данные заполнены. Подтвердите создание рекламации:";
        
        $keyboard = [
            [['text' => '✅ Подтвердить', 'callback_data' => 'claim_confirm']],
            [['text' => '❌ Отмена', 'callback_data' => 'claim_cancel']],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Подтверждение создания рекламации
     */
    private function confirmClaim(string $chatId, int $userId, Conversation $conversation): ServerResponse
    {
        $isUserMode = $conversation->notes['claim_user_mode'] ?? false;
        
        $data = [
            'project_code'  => $conversation->notes['claim_project'] ?? '',
            'reg_number'    => $conversation->notes['claim_reg_number'] ?? '',
            'subject'       => $conversation->notes['claim_subject'] ?? '',
            'act_date'      => $conversation->notes['claim_act_date'] ?? '',
            'equipment'     => $conversation->notes['claim_equipment'] ?? '',
            'serial_number' => $conversation->notes['claim_serial_number'] ?? '',
            'act_author'    => $conversation->notes['claim_act_author'] ?? '',
            'description'   => $conversation->notes['claim_description'] ?? '',
            'status'        => $isUserMode ? 'pending_moderation' : 'active',
            'created_by'    => $userId,
        ];
        
        // Для user_mode рег. номер '-' считаем заполненным
        $requiredFields = ['project_code', 'subject', 'act_date', 'equipment', 'serial_number', 'act_author', 'description'];
        if (!$isUserMode) {
            $requiredFields[] = 'reg_number';
        }
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->clearClaimState($conversation);
                return $this->messageService->sendMessage($chatId, "❌ <b>Ошибка</b>\n\nНе все поля заполнены. Попробуйте создать рекламацию заново.", [
                    [['text' => '➕ Добавить рекламацию', 'callback_data' => 'claim_create']],
                ]);
            }
        }
        
        $claimId = $this->claimService->createClaim($data);
        
        // Очищаем состояние визарда
        $this->clearClaimState($conversation);
        
        if ($claimId === false) {
            return $this->messageService->sendMessage($chatId, '❌ <b>Ошибка</b>\n\nНе удалось создать рекламацию. Проверьте логи.', [
                [['text' => '➕ Попробовать снова', 'callback_data' => 'claim_create']],
            ]);
        }
        
        if ($isUserMode) {
            $text = "✅ <b>Рекламация добавлена!</b>\n\n";
            $text .= "📁 <b>Проект:</b> {$data['project_code']}\n";
            $text .= "📌 <b>Тема:</b> {$data['subject']}\n";
            $text .= "📊 <b>Статус:</b> ⏳ На модерации\n";
            $text .= "\nРекламация будет проверена и утверждена.";
            
            $keyboard = [
                [['text' => '➕ Добавить ещё', 'callback_data' => 'claim_ucreate']],
                [['text' => '⬅️ Вернуться в табель', 'callback_data' => 'category_Табель']],
            ];
        } else {
            $text = "✅ <b>Рекламация создана!</b>\n\n";
            $text .= "📁 <b>Проект:</b> {$data['project_code']}\n";
            $text .= "🔢 <b>Рег. номер:</b> {$data['reg_number']}\n";
            $text .= "📌 <b>Тема:</b> {$data['subject']}\n";
            $text .= "📊 <b>Статус:</b> ✅ Активна\n";
            $text .= "🆔 <b>ID:</b> {$claimId}\n";
            
            $keyboard = [
                [['text' => '📋 Просмотреть', 'callback_data' => 'claim_view_' . $claimId]],
                [['text' => '➕ Добавить ещё', 'callback_data' => 'claim_create']],
                [['text' => '⬅️ В меню рекламаций', 'callback_data' => 'claim_menu']],
            ];
        }
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Отмена создания
     */
    private function cancelClaim(string $chatId, int $userId, Conversation $conversation): ServerResponse
    {
        $isUserMode = $conversation->notes['claim_user_mode'] ?? false;
        
        $this->clearClaimState($conversation);
        
        $text = "⚠️ <b>Создание рекламации отменено</b>";
        
        if ($isUserMode) {
            $keyboard = [
                [['text' => '⬅️ Вернуться в табель', 'callback_data' => 'category_Табель']],
            ];
        } else {
            $keyboard = [
                [['text' => '⬅️ В меню рекламаций', 'callback_data' => 'claim_menu']],
            ];
        }
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // МОДЕРАЦИЯ
    // ──────────────────────────────────────────────

    private function showModerationPanel(string $chatId): ServerResponse
    {
        $pendingClaims = $this->claimService->getPendingClaims();
        $pendingCloseClaims = $this->claimService->getPendingCloseClaims();
        
        $text = "🔔 <b>Рекламации на модерации</b>\n\n";
        
        if (empty($pendingClaims) && empty($pendingCloseClaims)) {
            $text .= "Нет рекламаций, ожидающих модерации.";
            $keyboard = [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_menu']],
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        $keyboard = [];
        $num = 0;
        
        // Ожидают одобрения (новые рекламации)
        if (!empty($pendingClaims)) {
            $text .= "⚠️ <b>Ожидают одобрения:</b> " . count($pendingClaims) . "\n";
            
            foreach ($pendingClaims as $claim) {
                $num++;
                $creatorFio = $claim['creator_fio'] ?? 'Неизвестно';
                $buttonText = "⚠️ {$num}. {$claim['reg_number']} — {$claim['project_code']}";
                if (mb_strlen($buttonText) > 40) {
                    $buttonText = mb_substr($buttonText, 0, 37) . '...';
                }
                $keyboard[] = [['text' => $buttonText, 'callback_data' => 'claim_modv_' . $claim['id']]];
                
                $text .= "\n{$num}. 🔢 {$claim['reg_number']}\n";
                $text .= "   📁 Проект: {$claim['project_code']}\n";
                $text .= "   👤 Создал: {$creatorFio}\n";
            }
        }
        
        // Ожидают подтверждения закрытия
        if (!empty($pendingCloseClaims)) {
            $text .= "\n🔒 <b>Ожидают подтверждения закрытия:</b> " . count($pendingCloseClaims) . "\n";
            
            foreach ($pendingCloseClaims as $claim) {
                $num++;
                $creatorFio = $claim['creator_fio'] ?? 'Неизвестно';
                $buttonText = "🔒 {$num}. {$claim['reg_number']} — {$claim['project_code']}";
                if (mb_strlen($buttonText) > 40) {
                    $buttonText = mb_substr($buttonText, 0, 37) . '...';
                }
                $keyboard[] = [['text' => $buttonText, 'callback_data' => 'claim_modv_' . $claim['id']]];
                
                $text .= "\n{$num}. 🔢 {$claim['reg_number']}\n";
                $text .= "   📁 Проект: {$claim['project_code']}\n";
                $text .= "   👤 Создал: {$creatorFio}\n";
                if (!empty($claim['close_reason'])) {
                    $text .= "   💬 Причина: {$claim['close_reason']}\n";
                }
            }
        }
        
        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => 'claim_menu']];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function showModerationDetails(string $chatId, int $claimId): ServerResponse
    {
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null || !in_array($claim['status'], ['pending_moderation', 'pending_close'])) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена или уже обработана.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_mod']],
            ]);
        }
        
        $createdAt = date('d.m.Y H:i', strtotime($claim['created_at']) + 2 * 3600);
        
        if ($claim['status'] === 'pending_close') {
            // Модерация закрытия
            $text = "🔒 <b>Подтверждение закрытия рекламации</b>\n\n";
            $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n";
            $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
            $text .= "📌 <b>Тема:</b> {$claim['subject']}\n";
            $text .= "📅 <b>Дата акта:</b> {$claim['act_date']}\n";
            $text .= "⚙️ <b>Оборудование:</b> {$claim['equipment']}\n";
            $text .= "🏷️ <b>Зав. номер:</b> {$claim['serial_number']}\n";
            $text .= "📝 <b>Описание:</b> {$claim['description']}\n\n";
            $text .= "👤 <b>Создал:</b> " . ($claim['creator_fio'] ?? 'Неизвестно') . "\n";
            $text .= "📅 <b>Создано:</b> {$createdAt}\n\n";
            $text .= "💬 <b>Причина закрытия:</b> " . ($claim['close_reason'] ?? '—') . "\n";
            
            $keyboard = [
                [['text' => '✅ Подтвердить закрытие', 'callback_data' => 'claim_aprc_' . $claimId]],
                [['text' => '❌ Отклонить закрытие', 'callback_data' => 'claim_rejc_' . $claimId]],
                [['text' => '⬅️ Назад к списку', 'callback_data' => 'claim_mod']],
            ];
        } else {
            // Модерация создания (существующая логика)
            $text = "🔍 <b>Модерация рекламации</b>\n\n";
            $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n";
            $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
            $text .= "📌 <b>Тема:</b> {$claim['subject']}\n";
            $text .= "📅 <b>Дата акта:</b> {$claim['act_date']}\n";
            $text .= "⚙️ <b>Оборудование:</b> {$claim['equipment']}\n";
            $text .= "🏷️ <b>Зав. номер:</b> {$claim['serial_number']}\n";
            $text .= "👤 <b>Автор акта:</b> {$claim['act_author']}\n";
            $text .= "📝 <b>Описание:</b> {$claim['description']}\n\n";
            $text .= "👤 <b>Создал:</b> " . ($claim['creator_fio'] ?? 'Неизвестно') . "\n";
            $text .= "📅 <b>Создано:</b> {$createdAt}\n";
            
            $keyboard = [
                [['text' => '✏️ Редактировать', 'callback_data' => 'claim_edit_' . $claimId]],
                [['text' => '✅ Одобрить', 'callback_data' => 'claim_apr_' . $claimId]],
                [['text' => '❌ Отклонить', 'callback_data' => 'claim_rejs_' . $claimId]],
                [['text' => '⬅️ Назад к списку', 'callback_data' => 'claim_mod']],
            ];
        }
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function approveClaim(string $chatId, int $userId, int $claimId): ServerResponse
    {
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_mod']],
            ]);
        }
        
        // Проверяем заполненность всех обязательных полей
        $requiredFields = [
            'project_code' => 'Проект',
            'reg_number'   => 'Рег. номер',
            'subject'      => 'Тема',
            'act_date'     => 'Дата акта',
            'equipment'    => 'Оборудование',
            'serial_number'=> 'Серийный номер',
            'act_author'   => 'Автор акта',
            'description'  => 'Описание',
        ];
        
        $emptyFields = [];
        foreach ($requiredFields as $field => $label) {
            if (empty($claim[$field]) || trim((string)$claim[$field]) === '' || trim((string)$claim[$field]) === '-') {
                $emptyFields[] = $label;
            }
        }
        
        if (!empty($emptyFields)) {
            $text = "⚠️ <b>Не все обязательные поля заполнены</b>\n\n";
            $text .= "Незаполненные поля:\n";
            foreach ($emptyFields as $fieldLabel) {
                $text .= "• {$fieldLabel}\n";
            }
            $text .= "\nПожалуйста, отредактируйте рекламацию и заполните недостающие данные перед одобрением.";
            
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text, [
                [['text' => '✏️ Редактировать', 'callback_data' => 'claim_edit_' . $claimId]],
                [['text' => '⬅️ Назад к модерации', 'callback_data' => 'claim_modd_' . $claimId]],
            ]);
        }
        
        $success = $this->claimService->approveClaim($claimId, $userId);
        
        if ($success) {
            $text = "✅ <b>Рекламация одобрена</b>\n\n";
            $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
            $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n";
            $text .= "📊 <b>Статус:</b> Активна\n";
        } else {
            $text = "❌ <b>Не удалось одобрить рекламацию</b>\n\nВозможно, она уже была обработана.";
        }
        
        $keyboard = [
            [['text' => '🔔 К списку модерации', 'callback_data' => 'claim_mod']],
            [['text' => '⬅️ В меню рекламаций', 'callback_data' => 'claim_menu']],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function startRejectClaim(string $chatId, int $userId, int $claimId, Conversation $conversation): ServerResponse
    {
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_mod']],
            ]);
        }
        
        // Очищаем другие состояния
        $this->clearAllOtherStates($conversation);
        
        $conversation->notes['claim_action'] = 'enter_reject_reason';
        $conversation->notes['claim_reject_id'] = $claimId;
        $conversation->update();
        
        $text = "❌ <b>Отклонение рекламации</b>\n\n";
        $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
        $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n\n";
        $text .= "Введите <b>причину отклонения</b>:";
        
        $keyboard = [
            [['text' => '❌ Отмена', 'callback_data' => 'claim_mod']],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function processRejectClaim(string $chatId, int $userId, string $reason, Conversation $conversation): ServerResponse
    {
        $claimId = $conversation->notes['claim_reject_id'] ?? null;
        
        if (!$claimId) {
            $this->clearClaimState($conversation);
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: рекламация не найдена.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_mod']],
            ]);
        }
        
        $reason = trim($reason);
        if (mb_strlen($reason) < 3) {
            return $this->messageService->sendMessage($chatId, "❌ <b>Ошибка</b>\n\nПричина отклонения должна содержать минимум 3 символа.\n\nВведите причину:", [
                [['text' => '❌ Отмена', 'callback_data' => 'claim_mod']],
            ]);
        }
        
        $claim = $this->claimService->getClaimById($claimId);
        $success = $this->claimService->rejectClaim($claimId, $userId, $reason);
        
        // Очищаем состояние
        $this->clearClaimState($conversation);
        
        if ($success) {
            $text = "❌ <b>Рекламация отклонена</b>\n\n";
            if ($claim) {
                $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
                $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n";
            }
            $text .= "💬 <b>Причина:</b> {$reason}\n";
        } else {
            $text = "❌ <b>Не удалось отклонить рекламацию</b>\n\nВозможно, она уже была обработана.";
        }
        
        $keyboard = [
            [['text' => '🔔 К списку модерации', 'callback_data' => 'claim_mod']],
            [['text' => '⬅️ В меню рекламаций', 'callback_data' => 'claim_menu']],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // КОНСТРУКТОР: РЕДАКТИРОВАНИЕ РЕКЛАМАЦИИ
    // ──────────────────────────────────────────────

    /**
     * Главное меню конструктора — список полей для редактирования
     */
    private function showClaimConstructor(string $chatId, int $claimId): ServerResponse
    {
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']],
            ]);
        }
        
        if ($claim['status'] !== 'active' && $claim['status'] !== 'pending_moderation') {
            $statusName = $this->getStatusName($claim['status']);
            return $this->messageService->sendMessage($chatId, "⚠️ <b>Редактирование недоступно</b>\n\nРекламация имеет статус: {$statusName}.\nРедактирование доступно только для активных рекламаций и рекламаций на модерации.", [
                [['text' => '⬅️ Назад к рекламации', 'callback_data' => 'claim_view_' . $claimId]],
            ]);
        }
        
        $text = "✏️ <b>Редактирование рекламации</b>\n\n";
        $text .= "🔢 <b>{$claim['reg_number']}</b> — {$claim['project_code']}\n\n";
        $text .= "Выберите поле для редактирования:";
        
        $keyboard = [];
        $fieldOrder = ['project', 'regnum', 'subject', 'actdate', 'author', 'equip', 'serial', 'desc'];
        
        foreach ($fieldOrder as $key) {
            $dbColumn = self::FIELD_MAP[$key];
            $emoji = self::FIELD_LABELS[$key][0];
            $label = self::FIELD_LABELS[$key][1];
            $value = $claim[$dbColumn] ?? '';
            
            // Обрезаем значение для кнопки
            $displayValue = mb_strlen($value) > 25 ? mb_substr($value, 0, 22) . '...' : $value;
            $buttonText = "{$emoji} {$label}: {$displayValue}";
            
            if (mb_strlen($buttonText) > 50) {
                $buttonText = mb_substr($buttonText, 0, 47) . '...';
            }
            
            $keyboard[] = [['text' => $buttonText, 'callback_data' => 'claim_edf_' . $key . '_' . $claimId]];
        }
        
        $keyboard[] = [['text' => '🗑️ Удалить рекламацию', 'callback_data' => 'claim_del_' . $claimId]];
        $keyboard[] = [['text' => '⬅️ Назад к рекламации', 'callback_data' => 'claim_view_' . $claimId]];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Детальный просмотр поля с кнопкой "Изменить"
     */
    private function showEditField(string $chatId, int $claimId, string $fieldKey): ServerResponse
    {
        if (!isset(self::FIELD_MAP[$fieldKey])) {
            return $this->messageService->sendMessage($chatId, '❌ Неизвестное поле.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_edit_' . $claimId]],
            ]);
        }
        
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.');
        }
        
        if ($claim['status'] !== 'active' && $claim['status'] !== 'pending_moderation') {
            return $this->messageService->sendMessage($chatId, '⚠️ Редактирование недоступно. Рекламация не активна.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_view_' . $claimId]],
            ]);
        }
        
        $dbColumn = self::FIELD_MAP[$fieldKey];
        $emoji = self::FIELD_LABELS[$fieldKey][0];
        $label = self::FIELD_LABELS[$fieldKey][1];
        $currentValue = $claim[$dbColumn] ?? '—';
        
        $text = "✏️ <b>Редактирование: {$label}</b>\n\n";
        $text .= "🔢 Рекламация: <b>{$claim['reg_number']}</b>\n\n";
        $text .= "{$emoji} <b>Текущее значение:</b>\n{$currentValue}";
        
        $keyboard = [];
        
        if ($fieldKey === 'project') {
            $keyboard[] = [['text' => '✏️ Изменить проект', 'callback_data' => 'claim_edprj_' . $claimId]];
        } else {
            $keyboard[] = [['text' => '✏️ Изменить', 'callback_data' => 'claim_eds_' . $fieldKey . '_' . $claimId]];
        }
        
        $keyboard[] = [['text' => '⬅️ Назад к конструктору', 'callback_data' => 'claim_edit_' . $claimId]];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Промпт для ввода нового значения поля
     */
    private function promptEditField(string $chatId, int $claimId, string $fieldKey, Conversation $conversation): ServerResponse
    {
        if (!isset(self::FIELD_MAP[$fieldKey]) || $fieldKey === 'project') {
            return $this->messageService->sendMessage($chatId, '❌ Ошибка.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_edit_' . $claimId]],
            ]);
        }
        
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null || ($claim['status'] !== 'active' && $claim['status'] !== 'pending_moderation')) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена или не активна.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']],
            ]);
        }
        
        // Очищаем другие состояния
        $this->clearAllOtherStates($conversation);
        
        $conversation->notes['claim_action'] = 'edit_' . $fieldKey;
        $conversation->notes['claim_edit_id'] = $claimId;
        $conversation->update();
        
        $label = self::FIELD_LABELS[$fieldKey][1];
        $dbColumn = self::FIELD_MAP[$fieldKey];
        $currentValue = $claim[$dbColumn] ?? '—';
        
        $text = "✏️ <b>Изменение: {$label}</b>\n\n";
        $text .= "🔢 Рекламация: <b>{$claim['reg_number']}</b>\n";
        $text .= "📌 Текущее значение: <b>{$currentValue}</b>\n\n";
        $text .= "Введите новое значение для <b>{$label}</b>:";
        
        $keyboard = [
            [['text' => '❌ Отмена', 'callback_data' => 'claim_edf_' . $fieldKey . '_' . $claimId]],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Обработка введённого нового значения поля
     */
    private function processEditFieldInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        $action = $conversation->notes['claim_action'] ?? '';
        $claimId = $conversation->notes['claim_edit_id'] ?? null;
        
        if (!$claimId) {
            $this->clearClaimState($conversation);
            return $this->showClaimMenu($chatId);
        }
        
        // Извлекаем ключ поля из action: edit_subject → subject
        $fieldKey = str_replace('edit_', '', $action);
        
        if (!isset(self::FIELD_MAP[$fieldKey])) {
            $this->clearClaimState($conversation);
            return $this->showClaimConstructor($chatId, $claimId);
        }
        
        $trimmed = trim($text);
        if (mb_strlen($trimmed) < 1) {
            $label = self::FIELD_LABELS[$fieldKey][1];
            return $this->messageService->sendMessage($chatId, "❌ <b>Ошибка</b>\n\nЗначение не может быть пустым.\n\nВведите новое значение для <b>{$label}</b>:", [
                [['text' => '❌ Отмена', 'callback_data' => 'claim_edf_' . $fieldKey . '_' . $claimId]],
            ]);
        }
        
        $dbColumn = self::FIELD_MAP[$fieldKey];
        $success = $this->claimService->updateClaimField($claimId, $dbColumn, $trimmed);
        
        // Очищаем состояние редактирования
        $conversation->notes['claim_action'] = null;
        $conversation->notes['claim_edit_id'] = null;
        $conversation->update();
        
        if ($success) {
            $label = self::FIELD_LABELS[$fieldKey][1];
            $emoji = self::FIELD_LABELS[$fieldKey][0];
            $displayValue = mb_strlen($trimmed) > 50 ? mb_substr($trimmed, 0, 47) . '...' : $trimmed;
            
            $resultText = "✅ <b>Поле обновлено</b>\n\n";
            $resultText .= "{$emoji} <b>{$label}:</b> {$displayValue}";
            
            $keyboard = [
                [['text' => '✏️ Продолжить редактирование', 'callback_data' => 'claim_edit_' . $claimId]],
                [['text' => '📋 К рекламации', 'callback_data' => 'claim_view_' . $claimId]],
            ];
            
            $resultText = $this->ensureUtf8Encoding($resultText);
            return $this->messageService->sendMessage($chatId, $resultText, $keyboard);
        } else {
            return $this->messageService->sendMessage($chatId, "❌ <b>Ошибка</b>\n\nНе удалось обновить поле. Возможно, рекламация была закрыта или удалена.", [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_view_' . $claimId]],
            ]);
        }
    }

    // ──────────────────────────────────────────────
    // КОНСТРУКТОР: РЕДАКТИРОВАНИЕ ПРОЕКТА
    // ──────────────────────────────────────────────

    /**
     * Показать селектор проектов для смены проекта рекламации
     */
    private function showEditProjectSelector(string $chatId, int $claimId, Conversation $conversation, int $page = 1): ServerResponse
    {
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null || ($claim['status'] !== 'active' && $claim['status'] !== 'pending_moderation')) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена или не активна.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']],
            ]);
        }
        
        $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector(
            $this->databaseService
        );
        $allProjects = $projectSelector->getAllProjectsIncludingPending();
        $allProjects = $this->databaseService->sortProjectsIntelligently($allProjects);
        
        if (empty($allProjects)) {
            return $this->messageService->sendMessage($chatId, "❌ <b>Нет доступных проектов</b>", [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_edf_project_' . $claimId]],
            ]);
        }
        
        $perPage = 33;
        $totalProjects = count($allProjects);
        $totalPages = (int) ceil($totalProjects / $perPage);
        if ($page < 1) $page = 1;
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $perPage;
        $projectsOnPage = array_slice($allProjects, $offset, $perPage);
        
        $text = "✏️ <b>Смена проекта рекламации</b>\n\n";
        $text .= "🔢 Рекламация: <b>{$claim['reg_number']}</b>\n";
        $text .= "📁 Текущий проект: <b>{$claim['project_code']}</b>\n\n";
        $text .= "Выберите новый проект:\n";
        $text .= "Страница {$page} из {$totalPages} (Всего: {$totalProjects})";
        
        $keyboard = [];
        
        $row = [];
        foreach ($projectsOnPage as $index => $project) {
            $callbackData = 'claim_edps_' . $project . '_' . $claimId;
            if (strlen($callbackData) <= 64) {
                $row[] = ['text' => $project, 'callback_data' => $callbackData];
            }
            
            if (count($row) == 4 || $index == count($projectsOnPage) - 1) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        
        if ($totalPages > 1) {
            $navRow = [];
            if ($page > 1) {
                $navRow[] = ['text' => '⬅️ Назад', 'callback_data' => 'claim_edpp_' . ($page - 1) . '_' . $claimId];
            }
            $navRow[] = ['text' => "📖 {$page}/{$totalPages}", 'callback_data' => 'noop'];
            if ($page < $totalPages) {
                $navRow[] = ['text' => '➡️ Вперед', 'callback_data' => 'claim_edpp_' . ($page + 1) . '_' . $claimId];
            }
            $keyboard[] = $navRow;
        }
        
        $keyboard[] = [['text' => '❌ Отмена', 'callback_data' => 'claim_edf_project_' . $claimId]];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Сохранить выбранный проект
     */
    private function selectEditProject(string $chatId, int $claimId, string $projectCode): ServerResponse
    {
        $success = $this->claimService->updateClaimField($claimId, 'project_code', $projectCode);
        
        if ($success) {
            $text = "✅ <b>Проект обновлён</b>\n\n";
            $text .= "📁 <b>Новый проект:</b> {$projectCode}";
            
            $keyboard = [
                [['text' => '✏️ Продолжить редактирование', 'callback_data' => 'claim_edit_' . $claimId]],
                [['text' => '📋 К рекламации', 'callback_data' => 'claim_view_' . $claimId]],
            ];
            
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        } else {
            return $this->messageService->sendMessage($chatId, "❌ <b>Ошибка</b>\n\nНе удалось обновить проект. Возможно, рекламация была закрыта.", [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_view_' . $claimId]],
            ]);
        }
    }

    // ──────────────────────────────────────────────
    // ЗАКРЫТИЕ / ПЕРЕОТКРЫТИЕ
    // ──────────────────────────────────────────────

    /**
     * Начать процесс закрытия — запросить причину
     */
    private function startCloseClaim(string $chatId, int $userId, int $claimId, Conversation $conversation): ServerResponse
    {
        error_log("ClaimHandler::startCloseClaim - claimId: {$claimId}, userId: {$userId}");
        
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']],
            ]);
        }
        
        if ($claim['status'] !== 'active') {
            $statusName = $this->getStatusName($claim['status']);
            return $this->messageService->sendMessage($chatId, "⚠️ Можно закрыть только активную рекламацию.\n\nТекущий статус: {$statusName}", [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_view_' . $claimId]],
            ]);
        }
        
        // Очищаем другие состояния
        $this->clearAllOtherStates($conversation);
        
        $conversation->notes['claim_action'] = 'enter_close_reason';
        $conversation->notes['claim_close_id'] = $claimId;
        $conversation->update();
        
        error_log("ClaimHandler::startCloseClaim - State set: claim_action=enter_close_reason, claim_close_id={$claimId}");
        
        $text = "🔒 <b>Закрытие рекламации</b>\n\n";
        $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
        $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n\n";
        $text .= "Введите <b>причину закрытия</b>:";
        
        $keyboard = [
            [['text' => '❌ Отмена', 'callback_data' => 'claim_view_' . $claimId]],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Обработать причину и закрыть рекламацию
     */
    private function processCloseClaim(string $chatId, int $userId, string $reason, Conversation $conversation): ServerResponse
    {
        $claimId = $conversation->notes['claim_close_id'] ?? null;
        
        error_log("ClaimHandler::processCloseClaim - claimId: " . ($claimId ?? 'NULL') . ", userId: {$userId}, reason length: " . mb_strlen(trim($reason)));
        
        if (!$claimId) {
            error_log("ClaimHandler::processCloseClaim - ERROR: claim_close_id is null!");
            $this->clearClaimState($conversation);
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: рекламация не найдена.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']],
            ]);
        }
        
        $claimId = (int) $claimId;
        $reason = trim($reason);
        if (mb_strlen($reason) < 3) {
            return $this->messageService->sendMessage($chatId, "❌ <b>Ошибка</b>\n\nПричина закрытия должна содержать минимум 3 символа.\n\nВведите причину:", [
                [['text' => '❌ Отмена', 'callback_data' => 'claim_view_' . $claimId]],
            ]);
        }
        
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            error_log("ClaimHandler::processCloseClaim - ERROR: claim not found in DB for id={$claimId}");
            $this->clearClaimState($conversation);
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена в базе данных.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']],
            ]);
        }
        
        if ($claim['status'] !== 'active') {
            error_log("ClaimHandler::processCloseClaim - claim {$claimId} status is '{$claim['status']}', expected 'active'");
            $this->clearClaimState($conversation);
            $statusName = $this->getStatusName($claim['status']);
            return $this->messageService->sendMessage($chatId, "⚠️ <b>Не удалось закрыть</b>\n\nРекламация имеет статус: {$statusName}.\nЗакрыть можно только активную рекламацию.", [
                [['text' => '📋 К рекламации', 'callback_data' => 'claim_view_' . $claimId]],
                [['text' => '⬅️ В меню рекламаций', 'callback_data' => 'claim_menu']],
            ]);
        }
        
        error_log("ClaimHandler::processCloseClaim - calling close for claim {$claimId}, userId: {$userId}, reason: '{$reason}'");
        
        // Всегда через модерацию — независимо от уровня доступа
        $success = $this->claimService->requestCloseClaim($claimId, $userId, $reason);
        error_log("ClaimHandler::processCloseClaim - requestCloseClaim result: " . ($success ? 'true' : 'false'));
        
        // Очищаем состояние
        $this->clearClaimState($conversation);
        
        if ($success) {
            $text = "🔔 <b>Запрос на закрытие отправлен</b>\n\n";
            if ($claim) {
                $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
                $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n";
            }
            $text .= "💬 <b>Причина:</b> {$reason}\n\n";
            $text .= "Закрытие будет подтверждено после модерации.";
        } else {
            $text = "❌ <b>Не удалось закрыть рекламацию</b>\n\nВозможно, она уже была закрыта.";
        }
        
        // Навигация зависит от контекста
        $fromWarranty = !empty($conversation->notes['claim_return_warranty']);
        $keyboard = [];
        if ($fromWarranty) {
            $warrantyBackCb = $conversation->notes['claim_warranty_back_cb'] ?? 'category_Табель';
            $keyboard[] = [['text' => '⬅️ Назад к гарантии', 'callback_data' => $warrantyBackCb]];
        } else {
            $keyboard[] = [['text' => '📋 К рекламации', 'callback_data' => 'claim_view_' . $claimId]];
            $keyboard[] = [['text' => '⬅️ В меню рекламаций', 'callback_data' => 'claim_menu']];
        }
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Переоткрыть рекламацию (одно нажатие)
     */
    private function reopenClaimAction(string $chatId, int $userId, int $claimId): ServerResponse
    {
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']],
            ]);
        }
        
        if ($claim['status'] !== 'closed') {
            return $this->messageService->sendMessage($chatId, '⚠️ Можно переоткрыть только закрытую рекламацию.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_view_' . $claimId]],
            ]);
        }
        
        $success = $this->claimService->reopenClaim($claimId, $userId);
        
        if ($success) {
            $text = "🔓 <b>Рекламация переоткрыта</b>\n\n";
            $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
            $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n";
            $text .= "📊 <b>Статус:</b> ✅ Активна\n";
        } else {
            $text = "❌ <b>Не удалось переоткрыть рекламацию</b>";
        }
        
        $keyboard = [
            [['text' => '📋 К рекламации', 'callback_data' => 'claim_view_' . $claimId]],
            [['text' => '⬅️ В меню рекламаций', 'callback_data' => 'claim_menu']],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // МОДЕРАЦИЯ ЗАКРЫТИЯ (pending_close)
    // ──────────────────────────────────────────────

    private function approveCloseClaim(string $chatId, int $userId, int $claimId): ServerResponse
    {
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_mod']],
            ]);
        }
        
        $success = $this->claimService->approveCloseClaim($claimId, $userId);
        
        if ($success) {
            $text = "🔒 <b>Закрытие подтверждено</b>\n\n";
            $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
            $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n";
            $text .= "📌 <b>Тема:</b> {$claim['subject']}\n";
            if (!empty($claim['close_reason'])) {
                $text .= "💬 <b>Причина:</b> {$claim['close_reason']}\n";
            }
        } else {
            $text = "❌ <b>Не удалось подтвердить закрытие</b>\n\nВозможно, рекламация уже обработана.";
        }
        
        $keyboard = [
            [['text' => '🔔 К списку модерации', 'callback_data' => 'claim_mod']],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function rejectCloseClaimAction(string $chatId, int $userId, int $claimId): ServerResponse
    {
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_mod']],
            ]);
        }
        
        $success = $this->claimService->rejectCloseClaim($claimId, $userId);
        
        if ($success) {
            $text = "↩️ <b>Закрытие отклонено</b>\n\n";
            $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
            $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n";
            $text .= "📊 <b>Статус:</b> ✅ Активна (возвращена)\n";
        } else {
            $text = "❌ <b>Не удалось отклонить закрытие</b>\n\nВозможно, рекламация уже обработана.";
        }
        
        $keyboard = [
            [['text' => '🔔 К списку модерации', 'callback_data' => 'claim_mod']],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // УДАЛЕНИЕ (двойное подтверждение)
    // ──────────────────────────────────────────────

    /**
     * Шаг 1: Первое предупреждение
     */
    private function confirmDeleteStep1(string $chatId, int $claimId): ServerResponse
    {
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']],
            ]);
        }
        
        $text = "⚠️ <b>Удаление рекламации</b>\n\n";
        $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
        $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n";
        $text .= "📌 <b>Тема:</b> {$claim['subject']}\n\n";
        $text .= "Вы уверены, что хотите удалить эту рекламацию?\n";
        $text .= "<b>Это действие нельзя будет отменить.</b>";
        
        $keyboard = [
            [['text' => '⚠️ Да, удалить', 'callback_data' => 'claim_delc_' . $claimId]],
            [['text' => '❌ Отмена', 'callback_data' => 'claim_edit_' . $claimId]],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Шаг 2: Финальное подтверждение
     */
    private function confirmDeleteStep2(string $chatId, int $claimId): ServerResponse
    {
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']],
            ]);
        }
        
        $text = "🚨 <b>ОКОНЧАТЕЛЬНОЕ УДАЛЕНИЕ</b>\n\n";
        $text .= "🔢 <b>Рег. номер:</b> {$claim['reg_number']}\n";
        $text .= "📁 <b>Проект:</b> {$claim['project_code']}\n\n";
        $text .= "⚠️ <b>Рекламация будет удалена навсегда!</b>\n";
        $text .= "Восстановить данные будет невозможно.\n\n";
        $text .= "Подтвердите удаление:";
        
        $keyboard = [
            [['text' => '🗑️ УДАЛИТЬ НАВСЕГДА', 'callback_data' => 'claim_delf_' . $claimId]],
            [['text' => '❌ Отмена', 'callback_data' => 'claim_edit_' . $claimId]],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Выполнить удаление после двойного подтверждения
     */
    private function executeDeleteClaim(string $chatId, int $userId, int $claimId): ServerResponse
    {
        $claim = $this->claimService->getClaimById($claimId);
        
        if ($claim === null) {
            return $this->messageService->sendMessage($chatId, '❌ Рекламация не найдена.', [
                [['text' => '⬅️ Назад', 'callback_data' => 'claim_list']],
            ]);
        }
        
        $regNumber = $claim['reg_number'];
        $projectCode = $claim['project_code'];
        
        $success = $this->claimService->deleteClaim($claimId);
        
        if ($success) {
            error_log("ClaimHandler: Claim {$claimId} (reg: {$regNumber}, project: {$projectCode}) permanently deleted by user {$userId}");
            
            $text = "🗑️ <b>Рекламация удалена</b>\n\n";
            $text .= "🔢 <b>Рег. номер:</b> {$regNumber}\n";
            $text .= "📁 <b>Проект:</b> {$projectCode}\n\n";
            $text .= "Рекламация удалена из системы навсегда.";
        } else {
            $text = "❌ <b>Не удалось удалить рекламацию</b>";
        }
        
        $keyboard = [
            [['text' => '📄 К списку рекламаций', 'callback_data' => 'claim_list']],
            [['text' => '⬅️ В меню рекламаций', 'callback_data' => 'claim_menu']],
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    // ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
    // ──────────────────────────────────────────────

    /**
     * Собрать текстовую сводку уже введённых данных
     */
    private function buildSummary(Conversation $conversation): string
    {
        $summary = '';
        
        $fields = [
            ['claim_project', '📁 Проект'],
            ['claim_reg_number', '🔢 Рег. номер'],
            ['claim_subject', '📌 Тема'],
            ['claim_act_date', '📅 Дата акта'],
            ['claim_act_author', '👤 Автор акта'],
            ['claim_equipment', '⚙️ Оборудование'],
            ['claim_serial_number', '🏷️ Зав. номер'],
            ['claim_description', '📝 Описание'],
        ];
        
        foreach ($fields as [$key, $label]) {
            $value = $conversation->notes[$key] ?? null;
            if ($value !== null) {
                $summary .= "{$label}: <b>{$value}</b>\n";
            }
        }
        
        return $summary;
    }

    /**
     * Иконка статуса
     */
    private function getStatusIcon(string $status): string
    {
        return match ($status) {
            'active' => '✅',
            'pending_moderation' => '⏳',
            'pending_close' => '🔒',
            'rejected' => '❌',
            'closed' => '📦',
            default => '❓',
        };
    }

    /**
     * Название статуса
     */
    private function getStatusName(string $status): string
    {
        return match ($status) {
            'active' => 'Активна',
            'pending_moderation' => 'На модерации',
            'pending_close' => 'Ожидает подтверждения закрытия',
            'rejected' => 'Отклонена',
            'closed' => 'Закрыта',
            default => 'Неизвестно',
        };
    }

    /**
     * Очистить все состояния визарда рекламации
     */
    private function clearClaimState(Conversation $conversation): void
    {
        $keys = [
            'claim_action', 'claim_project', 'claim_reg_number', 'claim_subject',
            'claim_act_date', 'claim_equipment', 'claim_serial_number',
            'claim_act_author', 'claim_description', 'claim_reject_id',
            'claim_edit_id', 'claim_close_id', 'claim_user_mode',
        ];
        
        foreach ($keys as $key) {
            $conversation->notes[$key] = null;
        }
        
        $conversation->update();
    }

    /**
     * Очистить все другие состояния ввода, чтобы не конфликтовали
     */
    private function clearAllOtherStates(Conversation $conversation): void
    {
        $conversation->notes['state'] = null;
        $conversation->notes['awaiting_custom_input'] = null;
        $conversation->notes['awaiting_custom_prochee_input'] = null;
        $conversation->notes['awaiting_date_input'] = null;
        $conversation->notes['awaiting_fio_input'] = null;
        $conversation->notes['awaiting_grok_question'] = null;
        $conversation->notes['awaiting_tabel_mgr_date'] = null;
        $conversation->notes['awaiting_absence_start_date'] = null;
        $conversation->notes['awaiting_absence_end_date'] = null;
        $conversation->notes['awaiting_simple_report_start_date'] = null;
        $conversation->notes['awaiting_simple_report_end_date'] = null;
        $conversation->notes['awaiting_project_report_start_date'] = null;
        $conversation->notes['awaiting_project_report_end_date'] = null;
        $conversation->notes['awaiting_notes_export_start_date'] = null;
        $conversation->notes['awaiting_notes_export_end_date'] = null;
        $conversation->notes['awaiting_export_key'] = null;
        $conversation->notes['awaiting_export_key_verify'] = null;
        $conversation->notes['awaiting_milestone_progress'] = null;
        $conversation->notes['admin_action'] = null;
        $conversation->notes['project_mod_action'] = null;
        $conversation->notes['notes_action'] = null;
    }

    /**
     * Обеспечить UTF-8 кодировку
     */
    private function ensureUtf8Encoding(string $text): string
    {
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        return $text;
    }
}
