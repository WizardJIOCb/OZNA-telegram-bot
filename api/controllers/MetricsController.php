<?php

namespace OznaApi\Controllers;

use OznaApi\Services\MetricsCacheService;

/**
 * MetricsController — PWA Metrics module API.
 *
 * All endpoints require access_level >= 2 (Supervisor, Admin, Superadmin).
 */
class MetricsController
{
    private function requireLevel(int $level): bool
    {
        $authMw = $GLOBALS['auth_middleware'] ?? null;
        if ($authMw && !$authMw->requireLevel($level)) {
            return false;
        }
        return true;
    }

    private function getCacheService(): MetricsCacheService
    {
        $db = getDb();
        $botRoot = dirname(__DIR__, 2);
        require_once $botRoot . '/CustomCommands/Services/DatabaseService.php';
        require_once $botRoot . '/CustomCommands/Services/MilestoneProgressService.php';

        $dbService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
        $milestoneService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MilestoneProgressService($dbService);

        return new MetricsCacheService($db, $milestoneService);
    }

    /**
     * GET /metrics/bn-summary
     */
    public function bnSummary(): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();

        $result = $db->query("
            SELECT business_unit_name, COUNT(DISTINCT project_code) as cnt
            FROM metrics_cache
            GROUP BY business_unit_name
        ");

        $rawCounts = [];
        while ($row = $result->fetch_assoc()) {
            $rawCounts[$row['business_unit_name'] ?? ''] = (int)$row['cnt'];
        }

        $groups = [
            'nko'   => ['name' => 'БН НКО', 'count' => 0],
            'opnvg' => ['name' => 'БН ОПНВГ', 'count' => 0],
            'mso'   => ['name' => 'БН МСО', 'count' => 0],
            'unsorted' => ['name' => 'Ошибка сортировки', 'count' => 0],
        ];

        foreach ($rawCounts as $bnName => $count) {
            $group = MetricsCacheService::resolveBNGroup($bnName ?: null);
            $groups[$group]['count'] += $count;
        }

        $result = $db->query("
            SELECT is_active, COUNT(DISTINCT project_code) as cnt 
            FROM metrics_cache WHERE has_missing_data = 1
            GROUP BY is_active
        ");
        $missingInfo = ['active' => 0, 'inactive' => 0];
        while ($row = $result->fetch_assoc()) {
            if ($row['is_active']) {
                $missingInfo['active'] = (int)$row['cnt'];
            } else {
                $missingInfo['inactive'] = (int)$row['cnt'];
            }
        }

        echo json_encode([
            'groups' => $groups,
            'missing_info' => $missingInfo,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /metrics/projects?group=nko|opnvg|mso|unsorted|missing_active|missing_inactive
     */
    public function projects(string $group): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();
        $userId = $GLOBALS['auth_user']['pwa_user_id'] ?? 0;

        $where = '';
        switch ($group) {
            case 'nko':
                $where = "mc.business_unit_name = 'НКО'";
                break;
            case 'opnvg':
                $where = "mc.business_unit_name IN ('ОПНВГ', 'ГАЗ')";
                break;
            case 'mso':
                $where = "mc.business_unit_name IN ('МСО', 'АСН')";
                break;
            case 'unsorted':
                $where = "(mc.business_unit_name IS NULL OR mc.business_unit_name NOT IN ('НКО','ОПНВГ','ГАЗ','МСО','АСН'))";
                break;
            case 'missing_active':
                $where = "mc.has_missing_data = 1 AND mc.is_active = 1";
                break;
            case 'missing_inactive':
                $where = "mc.has_missing_data = 1 AND mc.is_active = 0";
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid group'], JSON_UNESCAPED_UNICODE);
                return;
        }

        $sql = "
            SELECT 
                mc.project_code,
                MAX(mc.project_name) as project_name,
                MAX(mc.customer_name) as customer_name,
                MAX(mc.business_unit_name) as business_unit_name,
                GROUP_CONCAT(DISTINCT mc.work_type ORDER BY FIELD(mc.work_type, 'СМР','ШМР','ПНР')) as work_types,
                MAX(mc.stage_progress) as best_progress,
                SUM(mc.planned_hours_days) as total_plan,
                SUM(mc.fact_hours_days) as total_fact,
                SUM(COALESCE(mc.aggregate_forecast_fact, mc.fact_hours_days)) as total_forecast_fact,
                MAX(mc.has_missing_data) as has_missing_data,
                mf.id as fav_id
            FROM metrics_cache mc
            LEFT JOIN metrics_favorites mf 
                ON mf.project_code = mc.project_code AND mf.pwa_user_id = ?
            WHERE {$where}
            GROUP BY mc.project_code, mf.id
            ORDER BY (mf.id IS NOT NULL) DESC, mc.project_code ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $totalPlan = $row['total_plan'] ? (float)$row['total_plan'] : 0;
            $totalFact = $row['total_fact'] ? (float)$row['total_fact'] : 0;
            $totalForecastFact = $row['total_forecast_fact'] ? (float)$row['total_forecast_fact'] : 0;

            $summaryCurrentEff = ($totalPlan > 0 && $totalFact > 0) 
                ? round($totalPlan / $totalFact * 100, 1) : null;
            $summaryForecastEff = ($totalPlan > 0 && $totalForecastFact > 0)
                ? round($totalPlan / $totalForecastFact * 100, 1) : null;

            $projects[] = [
                'project_code'       => $row['project_code'],
                'project_name'       => $row['project_name'] ?? '',
                'customer_name'      => $row['customer_name'],
                'business_unit_name' => $row['business_unit_name'],
                'work_types'         => $row['work_types'] ? explode(',', $row['work_types']) : [],
                'best_progress'      => $row['best_progress'] !== null ? (float)$row['best_progress'] : null,
                'worst_efficiency'   => $summaryCurrentEff,
                'worst_forecast_efficiency' => $summaryForecastEff,
                'is_favorite'        => $row['fav_id'] !== null,
                'has_missing_data'   => (bool)$row['has_missing_data'],
            ];
        }
        $stmt->close();

        echo json_encode(['projects' => $projects], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /metrics/project/{code}
     */
    public function project(string $projectCode): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();
        $userId = $GLOBALS['auth_user']['pwa_user_id'] ?? 0;

        $stmt = $db->prepare("SELECT * FROM metrics_cache WHERE project_code = ?");
        $stmt->bind_param('s', $projectCode);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();

        if (empty($rows)) {
            $svc = $this->getCacheService();
            $svc->refreshProject($projectCode);

            $stmt = $db->prepare("SELECT * FROM metrics_cache WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $stmt->close();
        }

        if (empty($rows)) {
            http_response_code(404);
            echo json_encode(['error' => 'Проект не найден'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $stmt = $db->prepare("SELECT id FROM metrics_favorites WHERE pwa_user_id = ? AND project_code = ?");
        $stmt->bind_param('is', $userId, $projectCode);
        $stmt->execute();
        $isFavorite = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        // Load project card for historical fact data
        $stmtCard = $db->prepare("SELECT smr_historical_fact, pnr_historical_fact, cmr_historical_fact FROM project_cards WHERE project_code = ?");
        $stmtCard->bind_param('s', $projectCode);
        $stmtCard->execute();
        $cardRow = $stmtCard->get_result()->fetch_assoc();
        $stmtCard->close();

        $first = $rows[0];
        $project = [
            'project_code'        => $first['project_code'],
            'project_name'        => $first['project_name'] ?? '',
            'customer_name'       => $first['customer_name'],
            'business_unit_name'  => $first['business_unit_name'],
            'project_manager_fio' => $first['project_manager_fio'],
            'is_favorite'         => $isFavorite,
        ];

        $workTypes = [];
        $prefixMap = ['ШМР' => 'smr', 'ПНР' => 'pnr', 'СМР' => 'cmr'];
        foreach ($rows as $row) {
            $milestones = json_decode($row['milestone_details'] ?? '[]', true) ?: [];
            $auxiliary = json_decode($row['auxiliary_data'] ?? '[]', true) ?: [];
            $pfx = $prefixMap[$row['work_type']] ?? '';
            $workTypes[$row['work_type']] = [
                'planned_completion'      => $row['planned_completion'] ? date('d.m.Y', strtotime($row['planned_completion'])) : null,
                'planned_hours_days'      => $row['planned_hours_days'] !== null ? (float)$row['planned_hours_days'] : null,
                'fact_hours_days'         => $row['fact_hours_days'] !== null ? (float)$row['fact_hours_days'] : null,
                'stage_progress'          => $row['stage_progress'] !== null ? (float)$row['stage_progress'] : null,
                'current_efficiency'      => $row['current_efficiency'] !== null ? (float)$row['current_efficiency'] : null,
                'forecast_efficiency'     => $row['forecast_efficiency'] !== null ? (float)$row['forecast_efficiency'] : null,
                'forecast_remaining_days' => $row['forecast_remaining_days'] !== null ? (float)$row['forecast_remaining_days'] : null,
                'forecast_completion'     => $row['forecast_completion'],
                'milestones'              => $milestones,
                'auxiliary'               => $auxiliary,
                'historical_fact'         => $cardRow && $cardRow["{$pfx}_historical_fact"] !== null ? (float)$cardRow["{$pfx}_historical_fact"] : null,
            ];
        }

        echo json_encode([
            'project' => $project,
            'work_types' => $workTypes,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /metrics/project-card/{code}
     */
    public function projectCard(string $projectCode): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();

        $stmt = $db->prepare("SELECT * FROM project_cards WHERE project_code = ?");
        $stmt->bind_param('s', $projectCode);
        $stmt->execute();
        $card = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$card) {
            $stmt = $db->prepare("
                SELECT project_code FROM project_moderation WHERE project_code = ? AND is_active = 1
                UNION SELECT ? as project_code FROM ozna_tabel WHERE message LIKE CONCAT('%', ?, '%') LIMIT 1
            ");
            $stmt->bind_param('sss', $projectCode, $projectCode, $projectCode);
            $stmt->execute();
            $exists = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if (!$exists) {
                http_response_code(404);
                echo json_encode(['error' => 'Проект не найден'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $card = [
                'project_code' => $projectCode, 'project_name' => '',
                'customer_id' => null, 'business_unit_id' => null, 'project_manager_id' => null,
                'smr_planned_hours' => null, 'smr_planned_completion' => null,
                'smr_mobilization' => null, 'smr_project_activity' => null, 'smr_master_duties' => null,
                'pnr_planned_hours' => null, 'pnr_planned_completion' => null,
                'pnr_mobilization' => null, 'pnr_project_activity' => null,
                'cmr_planned_completion' => null, 'cmr_mobilization' => null,
                'cmr_project_activity' => null, 'cmr_forecast_enabled' => null, 'name_1c' => null,
            ];
        }

        // Lookups — all from dedicated reference tables (same as TG constructor)
        $customers = [];
        $result = $db->query("SELECT id, name FROM customers ORDER BY name");
        while ($row = $result->fetch_assoc()) {
            $customers[] = ['id' => (int)$row['id'], 'name' => $row['name']];
        }

        $businessUnits = [];
        $result = $db->query("SELECT id, name FROM business_units ORDER BY name");
        while ($row = $result->fetch_assoc()) {
            $businessUnits[] = ['id' => (int)$row['id'], 'name' => $row['name']];
        }

        $managers = [];
        $result = $db->query("SELECT id, name FROM project_managers ORDER BY name");
        while ($row = $result->fetch_assoc()) {
            $managers[] = ['id' => (int)$row['id'], 'name' => $row['name']];
        }

        $cardOut = [
            'project_code'          => $card['project_code'],
            'project_name'          => $card['project_name'] ?? '',
            'customer_id'           => $card['customer_id'] !== null ? (int)$card['customer_id'] : null,
            'business_unit_id'      => $card['business_unit_id'] !== null ? (int)$card['business_unit_id'] : null,
            'project_manager_id'    => $card['project_manager_id'] !== null ? (int)$card['project_manager_id'] : null,
            'smr_planned_hours'     => $card['smr_planned_hours'] !== null ? (float)$card['smr_planned_hours'] : null,
            'smr_planned_completion'=> $card['smr_planned_completion'],
            'smr_mobilization'      => $card['smr_mobilization'] !== null ? (int)$card['smr_mobilization'] : null,
            'smr_project_activity'  => $card['smr_project_activity'] !== null ? (int)$card['smr_project_activity'] : null,
            'smr_master_duties'     => $card['smr_master_duties'] !== null ? (int)$card['smr_master_duties'] : null,
            'pnr_planned_hours'     => $card['pnr_planned_hours'] !== null ? (float)$card['pnr_planned_hours'] : null,
            'pnr_planned_completion'=> $card['pnr_planned_completion'],
            'pnr_mobilization'      => $card['pnr_mobilization'] !== null ? (int)$card['pnr_mobilization'] : null,
            'pnr_project_activity'  => $card['pnr_project_activity'] !== null ? (int)$card['pnr_project_activity'] : null,
            'cmr_planned_completion'=> $card['cmr_planned_completion'],
            'cmr_mobilization'      => $card['cmr_mobilization'] !== null ? (int)$card['cmr_mobilization'] : null,
            'cmr_project_activity'  => $card['cmr_project_activity'] !== null ? (int)$card['cmr_project_activity'] : null,
            'cmr_forecast_enabled'  => $card['cmr_forecast_enabled'] !== null ? (int)$card['cmr_forecast_enabled'] : null,
            'name_1c'               => $card['name_1c'],
        ];

        echo json_encode([
            'card' => $cardOut,
            'lookups' => [
                'customers' => $customers,
                'business_units' => $businessUnits,
                'managers' => $managers,
            ],
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * PUT /metrics/project-card/{code}
     */
    public function updateProjectCard(string $projectCode, array $body): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();

        $allowedFields = ['customer_id', 'business_unit_id', 'project_manager_id', 'name_1c', 'project_name'];
        $updates = [];
        $params = [];
        $types = '';

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $body)) {
                $updates[] = "{$field} = ?";
                $val = $body[$field];
                if ($val === '' || $val === null) {
                    $params[] = null;
                    $types .= 's';
                } elseif (in_array($field, ['customer_id', 'business_unit_id', 'project_manager_id'])) {
                    $params[] = (int)$val;
                    $types .= 'i';
                } else {
                    $params[] = (string)$val;
                    $types .= 's';
                }
            }
        }

        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'Нет полей для обновления'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $this->ensureProjectCardExists($projectCode, $db);

        $sql = "UPDATE project_cards SET " . implode(', ', $updates) . " WHERE project_code = ?";
        $types .= 's';
        $params[] = $projectCode;

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();

        $svc = $this->getCacheService();
        $svc->refreshProject($projectCode);

        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    }

    /**
     * PUT /metrics/project-card/{code}/process/{workType}
     */
    public function updateProcessCard(string $projectCode, string $workType, array $body): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();

        $fieldMap = [
            'ШМР' => ['smr_planned_hours'=>'d','smr_planned_completion'=>'s','smr_mobilization'=>'i','smr_project_activity'=>'i','smr_master_duties'=>'i'],
            'ПНР' => ['pnr_planned_hours'=>'d','pnr_planned_completion'=>'s','pnr_mobilization'=>'i','pnr_project_activity'=>'i'],
            'СМР' => ['cmr_planned_completion'=>'s','cmr_mobilization'=>'i','cmr_project_activity'=>'i','cmr_forecast_enabled'=>'i'],
        ];

        if (!isset($fieldMap[$workType])) {
            http_response_code(400);
            echo json_encode(['error' => 'Неверный тип работ'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $allowedFields = $fieldMap[$workType];
        $updates = [];
        $params = [];
        $types = '';

        foreach ($allowedFields as $field => $type) {
            if (array_key_exists($field, $body)) {
                $updates[] = "{$field} = ?";
                $val = $body[$field];
                if ($val === '' || $val === null) {
                    $params[] = null;
                    $types .= 's';
                } else {
                    switch ($type) {
                        case 'i': $params[] = (int)$val; $types .= 'i'; break;
                        case 'd': $params[] = (float)$val; $types .= 'd'; break;
                        default: $params[] = (string)$val; $types .= 's';
                    }
                }
            }
        }

        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'Нет полей для обновления'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $this->ensureProjectCardExists($projectCode, $db);

        $sql = "UPDATE project_cards SET " . implode(', ', $updates) . " WHERE project_code = ?";
        $types .= 's';
        $params[] = $projectCode;

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();

        $svc = $this->getCacheService();
        $svc->refreshProject($projectCode);

        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /metrics/favorites
     */
    public function toggleFavorite(array $body): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();
        $userId = $GLOBALS['auth_user']['pwa_user_id'] ?? 0;
        $projectCode = trim($body['project_code'] ?? '');

        if (empty($projectCode)) {
            http_response_code(400);
            echo json_encode(['error' => 'project_code required'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $stmt = $db->prepare("SELECT id FROM metrics_favorites WHERE pwa_user_id = ? AND project_code = ?");
        $stmt->bind_param('is', $userId, $projectCode);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {
            $stmt = $db->prepare("DELETE FROM metrics_favorites WHERE id = ?");
            $stmt->bind_param('i', $existing['id']);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['is_favorite' => false], JSON_UNESCAPED_UNICODE);
        } else {
            $stmt = $db->prepare("INSERT INTO metrics_favorites (pwa_user_id, project_code) VALUES (?, ?)");
            $stmt->bind_param('is', $userId, $projectCode);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['is_favorite' => true], JSON_UNESCAPED_UNICODE);
        }
    }

    private function ensureProjectCardExists(string $projectCode, \mysqli $db): void
    {
        $stmt = $db->prepare("SELECT id FROM project_cards WHERE project_code = ?");
        $stmt->bind_param('s', $projectCode);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if (!$exists) {
            $pName = '';
            $stmt = $db->prepare("SELECT current_name FROM project_moderation WHERE project_code = ? LIMIT 1");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if ($row) $pName = $row['current_name'] ?? '';
            $stmt->close();

            $userId = $GLOBALS['auth_user']['pwa_user_id'] ?? 0;
            $stmt = $db->prepare("INSERT INTO project_cards (project_code, project_name, created_by) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $projectCode, $pName, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    /**
     * Returns canonical milestone template for a work type.
     * Must match the TG bot's ProjectHandler definitions exactly.
     */
    private function getMilestoneTemplate(string $workType): array
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

    /**
     * Ensures all template milestones exist for a project/work_type.
     * Missing milestones are inserted with NULL planned_hours.
     * Existing rows are never touched or deleted.
     */
    private function ensureTemplateMilestones(string $projectCode, string $workType, \mysqli $db): void
    {
        $template = $this->getMilestoneTemplate($workType);
        if (empty($template)) return;

        $this->ensureProjectCardExists($projectCode, $db);

        // Get existing milestone names
        $stmt = $db->prepare("SELECT milestone_name FROM project_milestones WHERE project_code = ? AND work_type = ?");
        $stmt->bind_param('ss', $projectCode, $workType);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing = [];
        while ($row = $result->fetch_assoc()) {
            $existing[$row['milestone_name']] = true;
        }
        $stmt->close();

        // Insert missing template milestones
        $insertStmt = $db->prepare(
            "INSERT INTO project_milestones (project_code, work_type, milestone_name, planned_hours, is_not_applicable, unit, order_index) VALUES (?, ?, ?, NULL, 0, 'days', ?)"
        );
        foreach ($template as $idx => $name) {
            if (!isset($existing[$name])) {
                $insertStmt->bind_param('sssi', $projectCode, $workType, $name, $idx);
                $insertStmt->execute();
            }
        }
        $insertStmt->close();
    }

    // ── Lookup CRUD (fillable lists, same as TG constructor) ──

    /** POST /metrics/lookups/customer */
    public function createCustomer(array $body): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();
        $name = trim($body['name'] ?? '');
        if ($name === '') { http_response_code(400); echo json_encode(['error' => 'Название обязательно'], JSON_UNESCAPED_UNICODE); return; }

        $stmt = $db->prepare("SELECT id FROM customers WHERE name = ?");
        $stmt->bind_param('s', $name); $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($existing) { echo json_encode(['id' => (int)$existing['id'], 'name' => $name], JSON_UNESCAPED_UNICODE); return; }

        $stmt = $db->prepare("INSERT INTO customers (name) VALUES (?)");
        $stmt->bind_param('s', $name); $stmt->execute();
        $newId = $stmt->insert_id; $stmt->close();
        echo json_encode(['id' => $newId, 'name' => $name], JSON_UNESCAPED_UNICODE);
    }

    /** POST /metrics/lookups/business-unit */
    public function createBusinessUnit(array $body): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();
        $name = trim($body['name'] ?? '');
        if ($name === '') { http_response_code(400); echo json_encode(['error' => 'Название обязательно'], JSON_UNESCAPED_UNICODE); return; }

        $stmt = $db->prepare("SELECT id FROM business_units WHERE name = ?");
        $stmt->bind_param('s', $name); $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($existing) { echo json_encode(['id' => (int)$existing['id'], 'name' => $name], JSON_UNESCAPED_UNICODE); return; }

        $stmt = $db->prepare("INSERT INTO business_units (name) VALUES (?)");
        $stmt->bind_param('s', $name); $stmt->execute();
        $newId = $stmt->insert_id; $stmt->close();
        echo json_encode(['id' => $newId, 'name' => $name], JSON_UNESCAPED_UNICODE);
    }

    /** POST /metrics/lookups/manager */
    public function createManager(array $body): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();
        $name = trim($body['name'] ?? '');
        if ($name === '') { http_response_code(400); echo json_encode(['error' => 'ФИО обязательно'], JSON_UNESCAPED_UNICODE); return; }

        $stmt = $db->prepare("SELECT id FROM project_managers WHERE name = ?");
        $stmt->bind_param('s', $name); $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($existing) { echo json_encode(['id' => (int)$existing['id'], 'name' => $name], JSON_UNESCAPED_UNICODE); return; }

        $userId = $GLOBALS['auth_user']['pwa_user_id'] ?? 0;
        $stmt = $db->prepare("INSERT INTO project_managers (name, created_by) VALUES (?, ?)");
        $stmt->bind_param('si', $name, $userId); $stmt->execute();
        $newId = $stmt->insert_id; $stmt->close();
        echo json_encode(['id' => $newId, 'name' => $name], JSON_UNESCAPED_UNICODE);
    }

    // ── Milestones CRUD (same data as TG constructor) ──

    /** GET /metrics/milestones/{code}/{workType} */
    public function milestones(string $projectCode, string $workType): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();

        // Auto-create missing template milestones
        $this->ensureTemplateMilestones($projectCode, $workType, $db);

        $stmt = $db->prepare("
            SELECT id, milestone_name, planned_hours, is_not_applicable, unit, order_index
            FROM project_milestones WHERE project_code = ? AND work_type = ?
            ORDER BY order_index, milestone_name
        ");
        $stmt->bind_param('ss', $projectCode, $workType);
        $stmt->execute();
        $result = $stmt->get_result();

        $milestones = [];
        while ($row = $result->fetch_assoc()) {
            $milestones[] = [
                'id'                => (int)$row['id'],
                'milestone_name'    => $row['milestone_name'],
                'planned_hours'     => $row['planned_hours'] !== null ? (float)$row['planned_hours'] : null,
                'is_not_applicable' => (bool)$row['is_not_applicable'],
                'unit'              => $row['unit'] ?? 'days',
            ];
        }
        $stmt->close();

        $prefix = match ($workType) { 'ШМР' => 'smr', 'ПНР' => 'pnr', 'СМР' => 'cmr', default => '' };
        $additional = [];
        if ($prefix) {
            $stmt = $db->prepare("SELECT * FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode); $stmt->execute();
            $card = $stmt->get_result()->fetch_assoc(); $stmt->close();
            if ($card) {
                $additional['mobilization'] = $card["{$prefix}_mobilization"] !== null ? (int)$card["{$prefix}_mobilization"] : null;
                $additional['project_activity'] = $card["{$prefix}_project_activity"] !== null ? (int)$card["{$prefix}_project_activity"] : null;
                if ($prefix === 'smr') $additional['master_duties'] = $card['smr_master_duties'] !== null ? (int)$card['smr_master_duties'] : null;
                $additional['planned_completion'] = $card["{$prefix}_planned_completion"];
                $additional['historical_fact'] = $card["{$prefix}_historical_fact"] !== null ? (float)$card["{$prefix}_historical_fact"] : null;
            }
        }

        echo json_encode(['milestones' => $milestones, 'additional' => $additional, 'work_type' => $workType], JSON_UNESCAPED_UNICODE);
    }

    /** PUT /metrics/milestones/{code}/{workType} */
    public function updateMilestones(string $projectCode, string $workType, array $body): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();
        $this->ensureProjectCardExists($projectCode, $db);

        // Update individual milestones
        if (isset($body['milestones']) && is_array($body['milestones'])) {
            foreach ($body['milestones'] as $m) {
                if (!isset($m['id'])) continue;
                $id = (int)$m['id'];
                $plannedHours = isset($m['planned_hours']) && $m['planned_hours'] !== '' && $m['planned_hours'] !== null ? (float)$m['planned_hours'] : null;
                $isNA = !empty($m['is_not_applicable']) ? 1 : 0;
                $unit = ($m['unit'] ?? 'days') === 'hours' ? 'hours' : 'days';

                $stmt = $db->prepare("UPDATE project_milestones SET planned_hours = ?, is_not_applicable = ?, unit = ? WHERE id = ? AND project_code = ? AND work_type = ?");
                $stmt->bind_param('dissss', $plannedHours, $isNA, $unit, $id, $projectCode, $workType);
                $stmt->execute(); $stmt->close();
            }
        }

        // Update additional work fields in project_cards
        if (isset($body['additional']) && is_array($body['additional'])) {
            $a = $body['additional'];
            $prefix = match ($workType) { 'ШМР' => 'smr', 'ПНР' => 'pnr', 'СМР' => 'cmr', default => '' };
            if ($prefix) {
                $updates = []; $params = []; $types = '';

                if (array_key_exists('mobilization', $a)) {
                    $updates[] = "{$prefix}_mobilization = ?";
                    $params[] = $a['mobilization'] !== null && $a['mobilization'] !== '' ? (int)$a['mobilization'] : null;
                    $types .= 'i';
                }
                if (array_key_exists('project_activity', $a)) {
                    $updates[] = "{$prefix}_project_activity = ?";
                    $params[] = $a['project_activity'] !== null && $a['project_activity'] !== '' ? (int)$a['project_activity'] : null;
                    $types .= 'i';
                }
                if ($prefix === 'smr' && array_key_exists('master_duties', $a)) {
                    $updates[] = "smr_master_duties = ?";
                    $params[] = $a['master_duties'] !== null && $a['master_duties'] !== '' ? (int)$a['master_duties'] : null;
                    $types .= 'i';
                }
                if (array_key_exists('planned_completion', $a)) {
                    $updates[] = "{$prefix}_planned_completion = ?";
                    $dateVal = $a['planned_completion'];
                    if ($dateVal && preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $dateVal)) {
                        $dt = \DateTime::createFromFormat('d.m.Y', $dateVal);
                        $dateVal = $dt ? $dt->format('Y-m-d') : null;
                    }
                    $params[] = $dateVal ?: null;
                    $types .= 's';
                }
                if (array_key_exists('historical_fact', $a)) {
                    $updates[] = "{$prefix}_historical_fact = ?";
                    $params[] = $a['historical_fact'] !== null && $a['historical_fact'] !== '' ? (float)$a['historical_fact'] : null;
                    $types .= 'd';
                }

                if (!empty($updates)) {
                    // Recalculate planned_hours = (milestone_days + additional_days) * 11
                    if ($prefix === 'smr' || $prefix === 'pnr') {
                        $stmt = $db->prepare("SELECT SUM(CASE WHEN is_not_applicable = 0 AND planned_hours IS NOT NULL THEN CASE WHEN unit = 'hours' THEN planned_hours / 11 ELSE planned_hours END ELSE 0 END) as total_days FROM project_milestones WHERE project_code = ? AND work_type = ?");
                        $stmt->bind_param('ss', $projectCode, $workType); $stmt->execute();
                        $totalDays = (float)($stmt->get_result()->fetch_assoc()['total_days'] ?? 0); $stmt->close();

                        $stmtCard = $db->prepare("SELECT * FROM project_cards WHERE project_code = ?");
                        $stmtCard->bind_param('s', $projectCode); $stmtCard->execute();
                        $curCard = $stmtCard->get_result()->fetch_assoc(); $stmtCard->close();

                        $mobDays = array_key_exists('mobilization', $a) ? ((int)($a['mobilization'] ?? 0)) : ((int)($curCard["{$prefix}_mobilization"] ?? 0));
                        $paDays = array_key_exists('project_activity', $a) ? ((int)($a['project_activity'] ?? 0)) : ((int)($curCard["{$prefix}_project_activity"] ?? 0));
                        $mdDays = 0;
                        if ($prefix === 'smr') $mdDays = array_key_exists('master_duties', $a) ? ((int)($a['master_duties'] ?? 0)) : ((int)($curCard['smr_master_duties'] ?? 0));

                        $updates[] = "{$prefix}_planned_hours = ?";
                        $params[] = ($totalDays + $mobDays + $paDays + $mdDays) * 11;
                        $types .= 'd';
                    }

                    $sql = "UPDATE project_cards SET " . implode(', ', $updates) . " WHERE project_code = ?";
                    $types .= 's'; $params[] = $projectCode;
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param($types, ...$params); $stmt->execute(); $stmt->close();
                }
            }
        }

        $svc = $this->getCacheService();
        $svc->refreshProject($projectCode);
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    }

    // ── Milestone Confirmation ──────────────────────────────────

    /** Valid СМР top-level milestone names */
    private static array $cmrTopLevelNames = [
        'Электромонтажные работы',
        'Слесарные работы',
        'Сварочные работы',
        'Работы по теплоизоляции',
        'Малярные работы',
    ];

    /** 4 sub-milestone suffixes for each СМР top-level */
    private static array $cmrSubSuffixes = [
        'Досборка', 'Реализация ТР', 'Устранение замечаний РК', 'Работы по гарантии',
    ];

    /**
     * POST /metrics/milestone-confirm
     * Body: { project_code, work_type, milestone_name }
     */
    public function confirmMilestone(array $body): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();

        $projectCode = trim($body['project_code'] ?? '');
        $workType = trim($body['work_type'] ?? '');
        $milestoneName = trim($body['milestone_name'] ?? '');

        if (!$projectCode || !$workType || !$milestoneName) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!in_array($workType, ['ШМР', 'ПНР', 'СМР'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid work_type'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $telegramId = $GLOBALS['auth_user']['telegram_id'] ?? null;
        $fio = $GLOBALS['auth_user']['fio'] ?? 'PWA User';
        $pwaUserId = $GLOBALS['auth_user']['pwa_user_id'] ?? 0;

        if ($workType === 'ШМР' || $workType === 'ПНР') {
            if (!$telegramId) {
                http_response_code(400);
                echo json_encode(['error' => 'Аккаунт не привязан к Telegram. Привяжите аккаунт для подтверждения вех.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $stageMarker = ($workType === 'ШМР') ? 'Этап шеф-монтажа завершен' : 'Этап наладки завершен';
            $funcDir = ($workType === 'ШМР') ? 'Шеф-монтаж' : 'Пусконаладка';

            // Check not already confirmed (idempotent)
            $checkStmt = $db->prepare("
                SELECT COUNT(*) as cnt FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message LIKE CONCAT('%', ?, '%')
                AND (JSON_EXTRACT(od.data, '$.functional_direction') = ?
                     OR ot.message LIKE '%[Конструктор]%')
            ");
            $checkStmt->bind_param('ssss', $projectCode, $stageMarker, $milestoneName, $funcDir);
            $checkStmt->execute();
            $alreadyConfirmed = (int)$checkStmt->get_result()->fetch_assoc()['cnt'] > 0;
            $checkStmt->close();

            if (!$alreadyConfirmed) {
                // Insert tabel message (exact TG bot format)
                $date = date('d.m.Y');
                $message = "<b>{$fio}</b>\r\n✅{$date}, Проект <b>{$projectCode}</b>: {$stageMarker} ({$milestoneName}) [Конструктор]";

                $insertStmt = $db->prepare("INSERT INTO ozna_tabel (user_id, message) VALUES (?, ?)");
                $insertStmt->bind_param('is', $telegramId, $message);
                $insertStmt->execute();
                $insertStmt->close();
            }

            // UPSERT milestone_progress_estimates to 100%
            $this->upsertMilestoneEstimate($db, $projectCode, $workType, $milestoneName, 100, $telegramId, $fio);

        } elseif ($workType === 'СМР') {
            // Validate top-level name
            if (!in_array($milestoneName, self::$cmrTopLevelNames)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid СМР milestone name'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // UPSERT all 4 sub-milestones to 100%
            $userId = $telegramId ?: $pwaUserId;
            foreach (self::$cmrSubSuffixes as $suffix) {
                $subName = $milestoneName . ' - ' . $suffix;
                $this->upsertMilestoneEstimate($db, $projectCode, 'СМР', $subName, 100, $userId, $fio);
            }
        }

        // Refresh cache
        $svc = $this->getCacheService();
        $svc->refreshProject($projectCode);

        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /metrics/milestone-unconfirm
     * Body: { project_code, work_type, milestone_name }
     */
    public function unconfirmMilestone(array $body): void
    {
        if (!$this->requireLevel(2)) return;
        $db = getDb();

        $projectCode = trim($body['project_code'] ?? '');
        $workType = trim($body['work_type'] ?? '');
        $milestoneName = trim($body['milestone_name'] ?? '');

        if (!$projectCode || !$workType || !$milestoneName) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($workType === 'ШМР' || $workType === 'ПНР') {
            $stageMarker = ($workType === 'ШМР') ? 'Этап шеф-монтажа завершен' : 'Этап наладки завершен';
            $funcDir = ($workType === 'ШМР') ? 'Шеф-монтаж' : 'Пусконаладка';

            // Delete tabel confirmation records
            $delStmt = $db->prepare("
                DELETE ot FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message LIKE CONCAT('%', ?, '%')
                AND (JSON_EXTRACT(od.data, '$.functional_direction') = ?
                     OR ot.message LIKE '%[Конструктор]%')
            ");
            $delStmt->bind_param('ssss', $projectCode, $stageMarker, $milestoneName, $funcDir);
            $delStmt->execute();
            $delStmt->close();

            // Delete milestone estimate
            $delEst = $db->prepare("
                DELETE FROM milestone_progress_estimates
                WHERE project_code = ? AND work_type = ? AND milestone_name = ?
            ");
            $delEst->bind_param('sss', $projectCode, $workType, $milestoneName);
            $delEst->execute();
            $delEst->close();

        } elseif ($workType === 'СМР') {
            if (!in_array($milestoneName, self::$cmrTopLevelNames)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid СМР milestone name'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Delete all 4 sub-milestone estimates
            foreach (self::$cmrSubSuffixes as $suffix) {
                $subName = $milestoneName . ' - ' . $suffix;
                $delEst = $db->prepare("
                    DELETE FROM milestone_progress_estimates
                    WHERE project_code = ? AND work_type = 'СМР' AND milestone_name = ?
                ");
                $delEst->bind_param('ss', $projectCode, $subName);
                $delEst->execute();
                $delEst->close();
            }
        }

        // Refresh cache
        $svc = $this->getCacheService();
        $svc->refreshProject($projectCode);

        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    }

    /**
     * UPSERT a milestone progress estimate to a given percent.
     */
    private function upsertMilestoneEstimate(\mysqli $db, string $projectCode, string $workType, string $milestoneName, int $percent, $userId, string $fio): void
    {
        $stmt = $db->prepare("
            INSERT INTO milestone_progress_estimates
                (project_code, milestone_name, work_type, estimated_percent, previous_percent, estimator_user_id, estimator_fio, estimated_at)
            VALUES (?, ?, ?, ?, 0, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                previous_percent = estimated_percent,
                estimated_percent = VALUES(estimated_percent),
                estimator_user_id = VALUES(estimator_user_id),
                estimator_fio = VALUES(estimator_fio),
                estimated_at = NOW()
        ");
        $stmt->bind_param('ssssis', $projectCode, $milestoneName, $workType, $percent, $userId, $fio);
        $stmt->execute();
        $stmt->close();
    }
}
