<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

use DateTime;

/**
 * Service for projects overview and statistics
 * Shows project activity from the perspective of objects/projects
 */
class ProjectsOverviewService
{
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * Get all active projects grouped by functional direction
     * Uses PersonnelService to get on-site employees and their projects
     * Only shows projects with recent activity (within last 5 days)
     * Excludes XXX projects
     * 
     * @return array ['mobile' => [...], 'shmr' => [...], 'pnr' => [...], 'all' => [...]]
     */
    public function getProjectsByFunctionalDirection(): array
    {
        $conn = $this->databaseService->getConnection();
        
        $projectsByFn = [
            'mobile' => [],
            'shmr' => [],
            'pnr' => [],
            'all' => []
        ];
        
        $projectsData = [];
        
        // First, get list of ZPI projects to exclude them
        $zpiQuery = "SELECT DISTINCT
                        message
                      FROM ozna_tabel 
                      WHERE message LIKE '%Участие в ЗПИ%'
                      AND message LIKE '%Проект <b>%</b>%'
                      AND dt >= DATE_SUB(NOW(), INTERVAL 5 DAY)";
        
        $zpiResult = $conn->query($zpiQuery);
        $zpiProjects = [];
        
        if ($zpiResult) {
            while ($row = $zpiResult->fetch_assoc()) {
                if (preg_match('/Проект <b>(.+?)<\/b>/', $row['message'], $matches)) {
                    $zpiProjects[$matches[1]] = true;
                }
            }
        }
        
        // Get projects with recent activity (last 5 days)
        $query = "SELECT 
                    message,
                    user_id,
                    dt
                  FROM ozna_tabel 
                  WHERE message LIKE '%Проект <b>%</b>%'
                  AND message NOT LIKE '%Участие в ЗПИ%'
                  AND dt >= DATE_SUB(NOW(), INTERVAL 5 DAY)
                  ORDER BY dt DESC
                  LIMIT 5000";
        
        $result = $conn->query($query);
        
        if (!$result) {
            return $projectsByFn;
        }
        
        // Get all users to map user IDs to FN
        $allUsers = $this->databaseService->getAllUsers();
        $userFnMap = [];
        
        foreach ($allUsers as $user) {
            $userFnMap[$user['user_id']] = $user['data']['functional_direction'] ?? 'Не указано';
        }
        
        // Get projects for each FN
        $fnMapping = [
            'mobile' => 'Мобильная бригада',
            'shmr' => 'Шеф-монтаж',
            'pnr' => 'Пусконаладка'
        ];
        
        while ($row = $result->fetch_assoc()) {
            // Extract project code using regex (same as PersonnelService)
            if (!preg_match('/Проект <b>(.+?)<\/b>/', $row['message'], $matches)) {
                continue;
            }
            
            $projectCode = $matches[1];
            $userId = $row['user_id'];
            $userFn = $userFnMap[$userId] ?? 'Не указано';
            
            // Skip empty or XXX projects
            if (empty($projectCode) || $projectCode === 'ХХХ') {
                continue;
            }
            
            // Skip ZPI projects (they have separate section)
            if (isset($zpiProjects[$projectCode])) {
                continue;
            }
            
            // Skip Тестировщик
            if ($userFn === 'Тестировщик') {
                continue;
            }
            
            // Find FN key
            $fnKey = null;
            foreach ($fnMapping as $key => $name) {
                if ($userFn === $name) {
                    $fnKey = $key;
                    break;
                }
            }
            
            // Skip if FN not in our mapping
            if ($fnKey === null) {
                continue;
            }
            
            // Initialize project data if not exists
            if (!isset($projectsData[$projectCode])) {
                $projectsData[$projectCode] = [
                    'project_code' => $projectCode,
                    'specialists' => [],
                    'fn_list' => []
                ];
            }
            
            // Add employee to specialists
            $projectsData[$projectCode]['specialists'][$userId] = $userFn;
            
            // Track functional directions
            if (!in_array($userFn, $projectsData[$projectCode]['fn_list'])) {
                $projectsData[$projectCode]['fn_list'][] = $userFn;
            }
            
            // Add to FN category
            if (!in_array($projectCode, $projectsByFn[$fnKey])) {
                $projectsByFn[$fnKey][] = $projectCode;
            }
            
            // Add to "all" category
            if (!in_array($projectCode, $projectsByFn['all'])) {
                $projectsByFn['all'][] = $projectCode;
            }
        }
        
        // Store project data for later use
        $this->projectsCache = $projectsData;
        
        return $projectsByFn;
    }

    /**
     * Get detailed project information
     * Includes last activity date
     * 
     * @param string $projectCode
     * @return array|null
     */
    public function getProjectDetails(string $projectCode): ?array
    {
        $conn = $this->databaseService->getConnection();
        
        // Get all users to map user IDs to FIO and FN
        $allUsers = $this->databaseService->getAllUsers();
        $userMap = [];
        $userFnMap = [];
        
        foreach ($allUsers as $user) {
            $userMap[$user['user_id']] = $user['data']['fio'] ?? 'Неизвестно';
            $userFnMap[$user['user_id']] = $user['data']['functional_direction'] ?? 'Не указано';
        }
        
        // Get all activity for this project (last 5 days)
        $query = "SELECT 
                    user_id,
                    MIN(dt) as first_activity,
                    MAX(dt) as last_activity,
                    COUNT(*) as entry_count
                  FROM ozna_tabel 
                  WHERE message LIKE ?
                  AND dt >= DATE_SUB(NOW(), INTERVAL 5 DAY)
                  GROUP BY user_id
                  ORDER BY first_activity ASC";
        
        $stmt = $conn->prepare($query);
        $searchPattern = "%Проект <b>{$projectCode}</b>%";
        $stmt->bind_param("s", $searchPattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $specialists = [];
        $firstActivityOverall = null;
        $lastActivityOverall = null;
        $fnMap = [];
        
        while ($row = $result->fetch_assoc()) {
            $userId = $row['user_id'];
            $userFn = $userFnMap[$userId] ?? 'Не указано';
            
            // Skip Тестировщик
            if ($userFn === 'Тестировщик') {
                continue;
            }
            
            $firstActivity = new DateTime($row['first_activity']);
            $lastActivity = new DateTime($row['last_activity']);
            
            if ($firstActivityOverall === null || $firstActivity < $firstActivityOverall) {
                $firstActivityOverall = $firstActivity;
            }
            
            if ($lastActivityOverall === null || $lastActivity > $lastActivityOverall) {
                $lastActivityOverall = $lastActivity;
            }
            
            $specialists[] = [
                'user_id' => $userId,
                'fio' => $userMap[$userId],
                'fn' => $userFn,
                'first_activity' => $firstActivity->format('d.m.Y'),
                'entry_count' => $row['entry_count']
            ];
            
            // Track FN for this project
            if (!isset($fnMap[$userFn])) {
                $fnMap[$userFn] = [];
            }
            $fnMap[$userFn][] = $userId;
        }
        
        // Calculate project duration
        $now = new DateTime();
        $durationDays = 0;
        
        if ($firstActivityOverall) {
            $interval = $firstActivityOverall->diff($now);
            $durationDays = $interval->days;
        }
        
        return [
            'project_code' => $projectCode,
            'first_activity' => $firstActivityOverall ? $firstActivityOverall->format('d.m.Y') : 'Н/Д',
            'last_activity' => $lastActivityOverall ? $lastActivityOverall->format('d.m.Y') : 'Н/Д',
            'duration_days' => $durationDays,
            'specialists_count' => count($specialists),
            'specialists' => $specialists,
            'fn_map' => $fnMap
        ];
    }

    /**
     * Get projects for a specific functional direction
     * Only shows projects with recent activity (within last 5 days)
     * Excludes ЗПИ and XXX projects
     * 
     * @param string $fn ('mobile', 'shmr', 'pnr')
     * @return array
     */
    public function getProjectsForFn(string $fn): array
    {
        $fnMapping = [
            'mobile' => 'Мобильная бригада',
            'shmr' => 'Шеф-монтаж',
            'pnr' => 'Пусконаладка'
        ];
        
        $dbFn = $fnMapping[$fn] ?? '';
        if (empty($dbFn)) {
            return [];
        }
        
        $conn = $this->databaseService->getConnection();
        
        // Get all users in this FN
        $allUsers = $this->databaseService->getAllUsers();
        $fnUsers = [];
        
        foreach ($allUsers as $user) {
            $userFn = $user['data']['functional_direction'] ?? '';
            if ($userFn === $dbFn) {
                $fnUsers[] = $user['user_id'];
            }
        }
        
        if (empty($fnUsers)) {
            return [];
        }
        
        // First, get list of ZPI projects to exclude them
        $zpiQuery = "SELECT DISTINCT
                        message
                      FROM ozna_tabel 
                      WHERE message LIKE '%Участие в ЗПИ%'
                      AND message LIKE '%Проект <b>%</b>%'
                      AND dt >= DATE_SUB(NOW(), INTERVAL 5 DAY)";
        
        $zpiResult = $conn->query($zpiQuery);
        $zpiProjects = [];
        
        if ($zpiResult) {
            while ($row = $zpiResult->fetch_assoc()) {
                if (preg_match('/Проект <b>(.+?)<\/b>/', $row['message'], $matches)) {
                    $zpiProjects[$matches[1]] = true;
                }
            }
        }
        
        // Get projects for these users with recent activity (last 5 days)
        $placeholders = implode(',', array_fill(0, count($fnUsers), '?'));
        
        $query = "SELECT 
                    message,
                    dt
                  FROM ozna_tabel 
                  WHERE message LIKE '%Проект <b>%</b>%'
                  AND message NOT LIKE '%Участие в ЗПИ%'
                  AND user_id IN ($placeholders)
                  AND dt >= DATE_SUB(NOW(), INTERVAL 5 DAY)
                  ORDER BY dt DESC";
        
        $stmt = $conn->prepare($query);
        $types = str_repeat('i', count($fnUsers));
        $stmt->bind_param($types, ...$fnUsers);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $projects = [];
        
        while ($row = $result->fetch_assoc()) {
            // Extract project code using regex
            if (!preg_match('/Проект <b>(.+?)<\/b>/', $row['message'], $matches)) {
                continue;
            }
            
            $projectCode = $matches[1];
            
            // Skip XXX projects and empty
            if (empty($projectCode) || $projectCode === 'ХХХ') {
                continue;
            }
            
            // Skip ZPI projects (they have separate section)
            if (isset($zpiProjects[$projectCode])) {
                continue;
            }
            
            // Add unique projects only
            if (!in_array($projectCode, $projects)) {
                $projects[] = $projectCode;
            }
        }
        
        return $projects;
    }

    /**
     * Get summary for a specific FN with specialist count per project
     * 
     * @param string $fn
     * @return array
     */
    public function getFnProjectsSummary(string $fn): array
    {
        $projects = $this->getProjectsForFn($fn);
        $summary = [];
        
        foreach ($projects as $projectCode) {
            $details = $this->getProjectDetails($projectCode);
            if ($details) {
                // Filter specialists for this FN only
                $fnMapping = [
                    'mobile' => 'Мобильная бригада',
                    'shmr' => 'Шеф-монтаж',
                    'pnr' => 'Пусконаладка'
                ];
                
                $dbFn = $fnMapping[$fn] ?? '';
                $fnSpecialists = array_filter($details['specialists'], function($spec) use ($dbFn) {
                    return $spec['fn'] === $dbFn;
                });
                
                $summary[] = [
                    'project_code' => $projectCode,
                    'first_activity' => $details['first_activity'],
                    'last_activity' => $details['last_activity'],
                    'specialists_count' => count($fnSpecialists),
                    'duration_days' => $details['duration_days']
                ];
            }
        }
        
        return $summary;
    }

    /**
     * Get ЗПИ projects (projects with "Участие в ЗПИ" entries)
     * Only shows projects with recent activity (within last 5 days)
     * Excludes XXX projects
     * 
     * @return array
     */
    public function getZpiProjects(): array
    {
        $conn = $this->databaseService->getConnection();
        
        // Get all users to map user IDs to FIO and FN
        $allUsers = $this->databaseService->getAllUsers();
        $userMap = [];
        $userFnMap = [];
        
        foreach ($allUsers as $user) {
            $userMap[$user['user_id']] = $user['data']['fio'] ?? 'Неизвестно';
            $userFnMap[$user['user_id']] = $user['data']['functional_direction'] ?? 'Не указано';
        }
        
        // Query to get projects with "Участие в ЗПИ" entries (last 5 days)
        $query = "SELECT 
                    message,
                    dt,
                    user_id
                  FROM ozna_tabel 
                  WHERE message LIKE '%Участие в ЗПИ%'
                  AND message LIKE '%Проект <b>%</b>%'
                  AND dt >= DATE_SUB(NOW(), INTERVAL 5 DAY)
                  ORDER BY dt DESC";
        
        $result = $conn->query($query);
        
        $zpiProjectsData = [];
        
        while ($row = $result->fetch_assoc()) {
            // Extract project code using regex
            if (!preg_match('/Проект <b>(.+?)<\/b>/', $row['message'], $matches)) {
                continue;
            }
            
            $projectCode = $matches[1];
            $userId = $row['user_id'];
            $userFn = $userFnMap[$userId] ?? 'Не указано';
            
            // Skip XXX projects
            if ($projectCode === 'ХХХ') {
                continue;
            }
            
            // Skip Тестировщик
            if ($userFn === 'Тестировщик') {
                continue;
            }
            
            // Initialize project if not exists
            if (!isset($zpiProjectsData[$projectCode])) {
                $zpiProjectsData[$projectCode] = [
                    'project_code' => $projectCode,
                    'first_activity' => $row['dt'],
                    'last_activity' => $row['dt'],
                    'specialists' => [],
                    'fn_list' => []
                ];
            } else {
                // Update first_activity if this record is earlier
                if ($row['dt'] < $zpiProjectsData[$projectCode]['first_activity']) {
                    $zpiProjectsData[$projectCode]['first_activity'] = $row['dt'];
                }
                // Update last_activity if this record is later
                if ($row['dt'] > $zpiProjectsData[$projectCode]['last_activity']) {
                    $zpiProjectsData[$projectCode]['last_activity'] = $row['dt'];
                }
            }
            
            // Add user to specialists
            $zpiProjectsData[$projectCode]['specialists'][$userId] = [
                'fio' => $userMap[$userId],
                'fn' => $userFn
            ];
            
            // Track functional directions
            if (!in_array($userFn, $zpiProjectsData[$projectCode]['fn_list'])) {
                $zpiProjectsData[$projectCode]['fn_list'][] = $userFn;
            }
        }
        
        // Build result array with details
        $zpiProjects = [];
        
        foreach ($zpiProjectsData as $projectCode => $projectData) {
            // Calculate project duration from first activity
            $firstActivity = new DateTime($projectData['first_activity']);
            $lastActivity = new DateTime($projectData['last_activity']);
            $now = new DateTime();
            $interval = $firstActivity->diff($now);
            $durationDays = $interval->days;
            
            $zpiProjects[] = [
                'project_code' => $projectCode,
                'first_activity' => $firstActivity->format('d.m.Y'),
                'last_activity' => $lastActivity->format('d.m.Y'),
                'duration_days' => $durationDays,
                'specialists_count' => count($projectData['specialists']),
                'specialists' => array_values($projectData['specialists']),
                'fn_map' => $projectData['fn_list']
            ];
        }
        
        return $zpiProjects;
    }

    /**
     * Map functional direction to key
     * 
     * @param string $fn
     * @return string|null
     */
    private function mapFnToKey(string $fn): ?string
    {
        $mapping = [
            'Мобильная бригада' => 'mobile',
            'Шеф-монтаж' => 'shmr',
            'Пусконаладка' => 'pnr'
        ];
        
        return $mapping[$fn] ?? null;
    }

    /**
     * Get days word declension
     * 
     * @param int $days
     * @return string
     */
    public function getDaysWord(int $days): string
    {
        $lastDigit = $days % 10;
        $lastTwoDigits = $days % 100;
        
        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 19) {
            return 'дней';
        }
        
        if ($lastDigit == 1) {
            return 'день';
        }
        
        if ($lastDigit >= 2 && $lastDigit <= 4) {
            return 'дня';
        }
        
        return 'дней';
    }

    private $projectsCache = [];
}
