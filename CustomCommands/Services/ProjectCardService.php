<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

/**
 * Service for managing project cards via CSV files
 */
class ProjectCardService
{
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
        // Auto-migration removed - tables must be created manually
    }

    /**
     * Generate CSV template for project card upload
     */
    public function generateTemplate(): string
    {
        $uploadsDir = __DIR__ . '/../../Uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }
        
        $filename = $uploadsDir . 'project_card_template.csv';
        $file = fopen($filename, 'w');
        
        // UTF-8 BOM for Excel compatibility
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Main info header
        fputcsv($file, ['=== ОСНОВНАЯ ИНФОРМАЦИЯ ==='], ';');
        fputcsv($file, ['Шифр проекта', 'Наименование проекта', 'Заказчик', 'БН', 'ШМР План (часы)', 'ПНР План (часы)', 'ШМР План дата заверш.', 'ПНР План дата заверш.'], ';');
        fputcsv($file, ['ОИ999', 'Пример проекта', 'Водоканал СПб', 'АСН', '240', '160', '31.12.2026', '28.02.2027'], ';');
        fputcsv($file, [], ';');
        
        // SMR milestones header
        fputcsv($file, ['=== ВЕХИ ШМР ==='], ';');
        fputcsv($file, ['Шифр проекта', 'Наименование вехи', 'Плановые часы'], ';');
        fputcsv($file, ['ОИ999', 'Входной контроль', '20'], ';');
        fputcsv($file, ['ОИ999', 'Завоз оборудования на площадку', '40'], ';');
        fputcsv($file, ['ОИ999', 'Расстановка блоков', '80'], ';');
        fputcsv($file, [], ';');
        
        // PNR milestones header
        fputcsv($file, ['=== ВЕХИ ПНР ==='], ';');
        fputcsv($file, ['Шифр проекта', 'Наименование вехи', 'Плановые часы'], ';');
        fputcsv($file, ['ОИ999', 'Электрооборудование', '60'], ';');
        fputcsv($file, ['ОИ999', 'Автоматизация', '50'], ';');
        
        fclose($file);
        
        return $filename;
    }

    /**
     * Parse uploaded CSV file and create project card
     */
    public function importFromCsv(string $filePath, int $userId): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'errors' => [],
            'project_code' => null
        ];

        try {
            $file = fopen($filePath, 'r');
            if (!$file) {
                throw new \Exception('Не удалось открыть файл');
            }
            
            $data = [];
            while (($row = fgetcsv($file, 1000, ';')) !== false) {
                $data[] = $row;
            }
            fclose($file);
            
            // Parse sections
            $mainInfo = null;
            $smrMilestones = [];
            $pnrMilestones = [];
            $currentSection = null;
            
            foreach ($data as $row) {
                if (empty($row[0])) continue;
                
                if (strpos($row[0], '=== ОСНОВНАЯ ИНФОРМАЦИЯ ===') !== false) {
                    $currentSection = 'main';
                    continue;
                } elseif (strpos($row[0], '=== ВЕХИ ШМР ===') !== false) {
                    $currentSection = 'smr';
                    continue;
                } elseif (strpos($row[0], '=== ВЕХИ ПНР ===') !== false) {
                    $currentSection = 'pnr';
                    continue;
                }
                
                // Skip headers
                if (strpos($row[0], 'Шифр проекта') !== false) {
                    continue;
                }
                
                if ($currentSection === 'main' && !$mainInfo && count($row) >= 4) {
                    $mainInfo = $row;
                } elseif ($currentSection === 'smr' && count($row) >= 3) {
                    $smrMilestones[] = $row;
                } elseif ($currentSection === 'pnr' && count($row) >= 3) {
                    $pnrMilestones[] = $row;
                }
            }
            
            if (!$mainInfo) {
                $result['errors'][] = 'Не найдена основная информация о проекте';
                $result['message'] = 'Ошибка: ' . implode(', ', $result['errors']);
                return $result;
            }
            
            $projectCode = trim($mainInfo[0]);
            $projectName = trim($mainInfo[1] ?? '');
            $customerName = trim($mainInfo[2] ?? '');
            $businessUnitName = trim($mainInfo[3] ?? '');
            $smrPlannedHours = floatval($mainInfo[4] ?? 0);
            $pnrPlannedHours = floatval($mainInfo[5] ?? 0);
            $smrPlannedCompletion = trim($mainInfo[6] ?? '');
            $pnrPlannedCompletion = trim($mainInfo[7] ?? '');
            
            // Validation
            if (empty($projectCode)) {
                $result['errors'][] = 'Не указан шифр проекта';
            }
            if (empty($projectName)) {
                $result['errors'][] = 'Не указано наименование проекта';
            }
            
            if ($this->projectCardExists($projectCode)) {
                $result['errors'][] = "Карточка проекта {$projectCode} уже существует";
                $result['message'] = 'Ошибка: ' . implode(', ', $result['errors']);
                return $result;
            }
            
            if (!empty($result['errors'])) {
                $result['message'] = 'Ошибка валидации: ' . implode(', ', $result['errors']);
                return $result;
            }
            
            // Get or create customer
            $customerId = null;
            if (!empty($customerName)) {
                $customerId = $this->getOrCreateCustomer($customerName);
            }
            
            // Get or create business unit
            $businessUnitId = null;
            if (!empty($businessUnitName)) {
                $businessUnitId = $this->getOrCreateBusinessUnit($businessUnitName);
            }
            
            // Create project card
            $cardCreated = $this->createProjectCard(
                $projectCode,
                $projectName,
                $customerId,
                $businessUnitId,
                $smrPlannedHours,
                $pnrPlannedHours,
                $userId,
                $smrPlannedCompletion,
                $pnrPlannedCompletion
            );
            
            if (!$cardCreated) {
                $result['errors'][] = 'Не удалось создать карточку проекта в БД';
                $result['message'] = 'Ошибка: ' . implode(', ', $result['errors']);
                return $result;
            }
            
            // Import SMR milestones
            $smrCount = 0;
            foreach ($smrMilestones as $milestone) {
                if (!empty($milestone[0]) && !empty($milestone[1])) {
                    $this->createMilestone(
                        trim($milestone[0]),
                        'ШМР',
                        trim($milestone[1]),
                        floatval($milestone[2] ?? 0),
                        $smrCount
                    );
                    $smrCount++;
                }
            }
            
            // Import PNR milestones
            $pnrCount = 0;
            foreach ($pnrMilestones as $milestone) {
                if (!empty($milestone[0]) && !empty($milestone[1])) {
                    $this->createMilestone(
                        trim($milestone[0]),
                        'ПНР',
                        trim($milestone[1]),
                        floatval($milestone[2] ?? 0),
                        $pnrCount
                    );
                    $pnrCount++;
                }
            }
            
            $result['success'] = true;
            $result['project_code'] = $projectCode;
            $result['message'] = "✅ Карточка проекта '{$projectCode}' успешно создана!\n\n";
            $result['message'] .= "📊 Загружено вех:\n";
            $result['message'] .= "  • ШМР: {$smrCount}\n";
            $result['message'] .= "  • ПНР: {$pnrCount}\n";
            if (!empty($smrPlannedCompletion)) {
                $result['message'] .= "\n📅 План завершения ШМР: {$smrPlannedCompletion}";
            }
            if (!empty($pnrPlannedCompletion)) {
                $result['message'] .= "\n📅 План завершения ПНР: {$pnrPlannedCompletion}";
            }
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("ProjectCardService::importFromCsv error: " . $e->getMessage());
            $result['errors'][] = $e->getMessage();
            $result['message'] = 'Ошибка при обработке файла: ' . $e->getMessage();
            return $result;
        }
    }

    /**
     * Check if project card exists
     */
    private function projectCardExists(string $projectCode): bool
    {
        $pdo = $this->databaseService->getPdo();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM project_cards WHERE project_code = ?");
        $stmt->execute([$projectCode]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get or create customer
     */
    private function getOrCreateCustomer(string $name): int
    {
        $pdo = $this->databaseService->getPdo();
        
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE name = ?");
        $stmt->execute([$name]);
        $id = $stmt->fetchColumn();
        
        if ($id) {
            return $id;
        }
        
        $stmt = $pdo->prepare("INSERT INTO customers (name) VALUES (?)");
        $stmt->execute([$name]);
        return $pdo->lastInsertId();
    }

    /**
     * Get or create business unit
     */
    private function getOrCreateBusinessUnit(string $name): int
    {
        $pdo = $this->databaseService->getPdo();
        
        $stmt = $pdo->prepare("SELECT id FROM business_units WHERE name = ?");
        $stmt->execute([$name]);
        $id = $stmt->fetchColumn();
        
        if ($id) {
            return $id;
        }
        
        $stmt = $pdo->prepare("INSERT INTO business_units (name) VALUES (?)");
        $stmt->execute([$name]);
        return $pdo->lastInsertId();
    }

    /**
     * Create project card
     */
    private function createProjectCard(
        string $projectCode,
        string $projectName,
        ?int $customerId,
        ?int $businessUnitId,
        float $smrPlannedHours,
        float $pnrPlannedHours,
        int $userId,
        string $smrPlannedCompletion = '',
        string $pnrPlannedCompletion = ''
    ): bool {
        $pdo = $this->databaseService->getPdo();
        
        // Parse dates (dd.mm.yyyy -> yyyy-mm-dd)
        $smrDate = $this->parseDateToSql($smrPlannedCompletion);
        $pnrDate = $this->parseDateToSql($pnrPlannedCompletion);
        
        $stmt = $pdo->prepare("
            INSERT INTO project_cards 
            (project_code, project_name, customer_id, business_unit_id, smr_planned_hours, pnr_planned_hours, created_by, smr_planned_completion, pnr_planned_completion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $projectCode,
            $projectName,
            $customerId,
            $businessUnitId,
            $smrPlannedHours,
            $pnrPlannedHours,
            $userId,
            $smrDate,
            $pnrDate
        ]);
    }

    /**
     * Parse date from dd.mm.yyyy to yyyy-mm-dd for SQL, returns null if invalid
     */
    private function parseDateToSql(string $dateStr): ?string
    {
        if (empty($dateStr)) return null;
        $dt = \DateTime::createFromFormat('d.m.Y', $dateStr);
        if (!$dt) return null;
        return $dt->format('Y-m-d');
    }

    /**
     * Create milestone
     */
    private function createMilestone(
        string $projectCode,
        string $workType,
        string $milestoneName,
        float $plannedHours,
        int $orderIndex
    ): bool {
        $pdo = $this->databaseService->getPdo();
        
        $stmt = $pdo->prepare("
            INSERT INTO project_milestones 
            (project_code, work_type, milestone_name, planned_hours, order_index)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $projectCode,
            $workType,
            $milestoneName,
            $plannedHours,
            $orderIndex
        ]);
    }

    /**
     * Get all project cards
     */
    public function getAllProjectCards(): array
    {
        $pdo = $this->databaseService->getPdo();
        
        $stmt = $pdo->query("
            SELECT 
                pc.*,
                c.name as customer_name,
                bu.name as business_unit_name
            FROM project_cards pc
            LEFT JOIN customers c ON pc.customer_id = c.id
            LEFT JOIN business_units bu ON pc.business_unit_id = bu.id
            ORDER BY pc.created_at DESC
        ");
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get project card by code
     */
    public function getProjectCard(string $projectCode): ?array
    {
        $pdo = $this->databaseService->getPdo();
        
        $stmt = $pdo->prepare("
            SELECT 
                pc.*,
                c.name as customer_name,
                bu.name as business_unit_name
            FROM project_cards pc
            LEFT JOIN customers c ON pc.customer_id = c.id
            LEFT JOIN business_units bu ON pc.business_unit_id = bu.id
            WHERE pc.project_code = ?
        ");
        
        $stmt->execute([$projectCode]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    /**
     * Get milestones for project
     */
    public function getMilestones(string $projectCode, string $workType): array
    {
        $pdo = $this->databaseService->getPdo();
        
        $stmt = $pdo->prepare("
            SELECT * FROM project_milestones 
            WHERE project_code = ? AND work_type = ?
            ORDER BY order_index
        ");
        
        $stmt->execute([$projectCode, $workType]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
