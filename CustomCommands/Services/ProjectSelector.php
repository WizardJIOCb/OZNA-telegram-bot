<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

/**
 * Project selector service
 * Manages project code extraction and validation
 */
class ProjectSelector
{
    private array $validProjects;
    private ?DatabaseService $databaseService = null;
    
    public function __construct(?DatabaseService $databaseService = null)
    {
        // Загружаем проекты из того же файла что и AdminService
        $this->loadProjectsFromFile();
        
        // Use provided DatabaseService or create new one
        if ($databaseService) {
            $this->databaseService = $databaseService;
        } else {
            try {
                $this->databaseService = new DatabaseService();
            } catch (\Exception $e) {
                error_log("ProjectSelector: Failed to initialize DatabaseService: " . $e->getMessage());
            }
        }
    }

    /**
     * Extract project code from callback data
     */
    public function extractProjectCode(string $category): ?string
    {
        // Fix encoding issues with category
        $category = $this->fixKnownCyrillicPatterns($category);
        
        if (!str_contains($category, 'ШифрПроекта_')) {
            return null;
        }

        $parts = explode('_', $category);
        $projectCode = end($parts);
        
        return $this->isValidProject($projectCode) ? $projectCode : null;
    }

    /**
     * Check if project code is valid
     * Checks both projects.json and pending projects in database
     */
    public function isValidProject(string $projectCode): bool
    {
        // Check if project exists in projects.json
        if (in_array($projectCode, $this->validProjects)) {
            return true;
        }
        
        // Check if project exists in database (including pending)
        if ($this->databaseService) {
            try {
                $exists = $this->databaseService->projectExists($projectCode);
                if ($exists) {
                    error_log("ProjectSelector: Project '{$projectCode}' found in database (pending/active)");
                    return true;
                }
            } catch (\Exception $e) {
                error_log("ProjectSelector: Error checking project in database: " . $e->getMessage());
            }
        }
        
        error_log("ProjectSelector: Project '{$projectCode}' not found in file or database");
        return false;
    }

    /**
     * Get all valid project codes
     */
    public function getAllProjects(): array
    {
        return $this->validProjects;
    }
    
    /**
     * Get all projects including pending ones from database
     * Used for control projects where supervisor should see all available projects
     */
    public function getAllProjectsIncludingPending(): array
    {
        $projects = $this->validProjects;
        
        if (!$this->databaseService) {
            // Применяем сортировку даже для проектов из файла
            return $this->databaseService ? $this->databaseService->sortProjectsIntelligently($projects) : $projects;
        }
        
        try {
            // Get all pending and active projects from database
            $dbProjects = $this->databaseService->getAllActiveAndPendingProjects();
            
            // Add database projects that aren't in the file yet
            foreach ($dbProjects as $dbProject) {
                if (!in_array($dbProject, $projects)) {
                    $projects[] = $dbProject;
                }
            }
            
            // Применяем умную сортировку
            return $this->databaseService->sortProjectsIntelligently($projects);
            
        } catch (\Exception $e) {
            error_log("ProjectSelector: Error getting pending projects: " . $e->getMessage());
            return $this->databaseService->sortProjectsIntelligently($projects);
        }
    }
    
    /**
     * Get projects available for user (including their pending projects)
     */
    public function getUserProjects(int $userId): array
    {
        if (!$this->databaseService) {
            error_log("ProjectSelector: DatabaseService not available, returning projects from file");
            return $this->validProjects;
        }
        
        try {
            // Получаем проекты из базы (включая pending проекты пользователя)
            $projectsData = $this->databaseService->getUserProjectsWithPending($userId);
            
            $projects = [];
            foreach ($projectsData as $project) {
                $projects[] = $project['project_code'];
            }
            
            // Добавляем проекты из файла, которых нет в базе
            foreach ($this->validProjects as $project) {
                if (!in_array($project, $projects)) {
                    $projects[] = $project;
                }
            }
            
            // Применяем умную сортировку
            $sortedProjects = $this->databaseService->sortProjectsIntelligently($projects);
            
            error_log("ProjectSelector: User {$userId} has " . count($sortedProjects) . " projects available (sorted)");
            return $sortedProjects;
            
        } catch (\Exception $e) {
            error_log("ProjectSelector: Error getting user projects: " . $e->getMessage());
            return $this->databaseService->sortProjectsIntelligently($this->validProjects);
        }
    }

    /**
     * Check if callback data is project selection
     */
    public function isProjectSelection(string $category): bool
    {
        // Fix encoding issues with category
        $category = $this->fixKnownCyrillicPatterns($category);
        
        return str_contains($category, 'ШифрПроекта_');
    }

    /**
     * Get date from callback data
     */
    public function extractDate(string $category): ?string
    {
        // Fix encoding issues with category
        $category = $this->fixKnownCyrillicPatterns($category);
        
        if (str_contains($category, '_Сегодня') || str_ends_with($category, 'Сегодня')) {
            return date("d.m.Y");
        }
        
        if (str_contains($category, '_Вчера') || str_ends_with($category, 'Вчера')) {
            return date("d.m.Y", time() - 86400);
        }
        
        if (str_contains($category, '_Позавчера') || str_ends_with($category, 'Позавчера')) {
            return date("d.m.Y", time() - 172800);
        }
        
        // Handle "Other date" selection
        if (str_contains($category, '_ДругаяДата') || str_ends_with($category, 'ДругаяДата')) {
            return null; // Will be handled by the TabelHandler to ask for custom input
        }
        
        return null;
    }

    /**
     * Check if callback is date selection
     */
    public function isDateSelection(string $category): bool
    {
        // Fix encoding issues with category
        $category = $this->fixKnownCyrillicPatterns($category);
        
        $result = str_contains($category, '_Сегодня') || 
                  str_contains($category, '_Вчера') || 
                  str_contains($category, '_Позавчера') || 
                  str_contains($category, '_ДругаяДата') ||
                  str_ends_with($category, 'Сегодня') ||
                  str_ends_with($category, 'Вчера') ||
                  str_ends_with($category, 'Позавчера') ||
                  str_ends_with($category, 'ДругаяДата');
        error_log("ProjectSelector isDateSelection called with category: {$category}, result: " . ($result ? 'true' : 'false'));
        return $result;
    }
    
    /**
     * Fix known corrupted Cyrillic patterns
     */
    private function fixKnownCyrillicPatterns($str)
    {
        // Replace the known corrupted patterns
        $replacements = [
            'РЁРёС„СЂРџСЂРѕРµРєС‚Р°' => 'ШифрПроекта',
            'РћР834' => 'ОИ834',
            'РћР651' => 'ОИ651',
            'РћР696' => 'ОИ696',
            'РћР795' => 'ОИ795',
            'РћР810' => 'ОИ810',
            'РћР768' => 'ОИ768',
            'РћР837' => 'ОИ837',
            'РћР562' => 'ОИ562',
            'РћР788' => 'ОИ788',
            'РћР860' => 'ОИ860',
            'РћР862' => 'ОИ862',
            'Р‘РљРќРЎ115' => 'БКНС115',
            'Р‘РљРќРЎ120' => 'БКНС120',
            'Р”Р°С‚Р°' => 'Дата',
            'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
            'Р’С‡РµСЂР°' => 'Вчера'
        ];
        
        foreach ($replacements as $corrupted => $fixed) {
            $str = str_replace($corrupted, $fixed, $str);
        }
        
        return $str;
    }
    
    /**
     * Загрузить проекты из файла (тот же метод что в AdminService)
     */
    private function loadProjectsFromFile(): void
    {
        // Статический список как запасной вариант
        $this->validProjects = [
            'ОИ834', 'ОИ651', 'ОИ696', 'ОИ795', 'ОИ810', 'ОИ768', 
            'ОИ837', 'ОИ562', 'ОИ788', 'ОИ860', 'ОИ862', 'БКНС115', 'БКНС120'
        ];
        
        $filePath = __DIR__ . '/../../data/projects.json';
        
        if (file_exists($filePath)) {
            $data = file_get_contents($filePath);
            $projects = json_decode($data, true);
            
            if (is_array($projects) && !empty($projects)) {
                $this->validProjects = $projects;
            }
        }
    }
}