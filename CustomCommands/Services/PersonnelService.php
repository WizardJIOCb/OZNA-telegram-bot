<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

use DateTime;

/**
 * Personnel Service - Business logic for personnel management
 * Handles employee status tracking and calculations
 */
class PersonnelService
{
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * Get all users by functional direction
     * 
     * @param string $functionalDirection - "Мобильная бригада", "Шеф-монтаж", or "Пусконаладка"
     * @return array - Array of users with user_id and fio
     */
    public function getUsersByFunctionalDirection(string $functionalDirection): array
    {
        error_log("PersonnelService::getUsersByFunctionalDirection called with: '{$functionalDirection}'");
        
        $allUsers = $this->databaseService->getAllUsers();
        error_log("PersonnelService::getUsersByFunctionalDirection - Total users in database: " . count($allUsers));
        
        $result = [];
        
        foreach ($allUsers as $user) {
            $userData = $user['data'] ?? [];
            $userFn = $userData['functional_direction'] ?? '';
            
            if ($userFn === $functionalDirection) {
                $result[] = [
                    'user_id' => $user['user_id'],
                    'fio' => $userData['fio'] ?? 'Без ФИО'
                ];
                error_log("PersonnelService::getUsersByFunctionalDirection - Found user: {$user['user_id']}, FN: '{$userFn}'");
            }
        }
        
        error_log("PersonnelService::getUsersByFunctionalDirection - Found " . count($result) . " users for FN: '{$functionalDirection}'");
        return $result;
    }

    /**
     * Get employees currently on site with duration and color indicators
     * 
     * @param string $functionalDirection
     * @return array - Array with user info, days, start_date, project_code, and icon
     */
    public function getOnsiteEmployees(string $functionalDirection): array
    {
        $users = $this->getUsersByFunctionalDirection($functionalDirection);
        $result = [];
        
        foreach ($users as $user) {
            $sessionStart = $this->findFirstEntryInCurrentSession($user['user_id']);
            
            if ($sessionStart === null) {
                continue; // No active work session
            }
            
            $now = new DateTime();
            $days = $this->getDaysBetween($sessionStart, $now);
            
            // Determine color indicator
            if ($days <= 30) {
                $icon = '🟢';
            } elseif ($days <= 45) {
                $icon = '🟡';
            } else {
                $icon = '🔴';
            }
            
            // Get current project code
            $projectCode = $this->getCurrentProjectCode($user['user_id']);
            
            $result[] = [
                'user_id' => $user['user_id'],
                'fio' => $user['fio'],
                'days' => $days,
                'start_date' => $sessionStart->format('d.m.Y'),
                'project_code' => $projectCode,
                'icon' => $icon
            ];
        }
        
        // Sort by days descending (longest first)
        usort($result, function($a, $b) {
            return $b['days'] - $a['days'];
        });
        
        return $result;
    }

    /**
     * Get employees on free shift (vacation time) with duration and color indicators
     * LOGIC: If employee is NOT on-site, they are on free time
     * 
     * @param string $functionalDirection
     * @return array - Array with user info, days, last_work_date, and icon
     */
    public function getFreetimeEmployees(string $functionalDirection): array
    {
        $users = $this->getUsersByFunctionalDirection($functionalDirection);
        $result = [];
        
        foreach ($users as $user) {
            // Check if on-site (has open session)
            $sessionStart = $this->findFirstEntryInCurrentSession($user['user_id']);
            if ($sessionStart !== null) {
                continue; // Employee is on-site, skip
            }
            
            // Employee is NOT on-site = on free time
            // Try to find last continuous work period first (for accurate date)
            $freeTimeStart = $this->findLastContinuousWorkPeriod($user['user_id']);
            
            if ($freeTimeStart === null) {
                // No continuous work period found
                // Check if employee has ANY work activities at all
                $hasWorkActivities = $this->hasAnyWorkActivities($user['user_id']);
                
                if (!$hasWorkActivities) {
                    // Only non-work activities (office work)
                    // Free-time starts at FIRST non-work activity
                    $freeTimeStart = $this->findFirstActivity($user['user_id']);
                } else {
                    // Has some work activities but no continuous period
                    // Use last activity of ANY type
                    $freeTimeStart = $this->findLastActivity($user['user_id']);
                }
                
                if ($freeTimeStart === null) {
                    // No records at all - show with special indicator
                    $result[] = [
                        'user_id' => $user['user_id'],
                        'fio' => $user['fio'],
                        'days' => 999,
                        'last_work_date' => 'Нет данных',
                        'icon' => '⚪'
                    ];
                    continue;
                }
            }
            
            $now = new DateTime();
            $days = $this->getDaysBetween($freeTimeStart, $now);
            
            // Determine color indicator (INVERTED!)
            if ($days <= 14) {
                $icon = '🔴'; // Not enough rest
            } elseif ($days <= 30) {
                $icon = '🟡';
            } else {
                $icon = '🟢'; // Well rested
            }
            
            $result[] = [
                'user_id' => $user['user_id'],
                'fio' => $user['fio'],
                'days' => $days,
                'last_work_date' => $freeTimeStart->format('d.m.Y'),
                'icon' => $icon
            ];
        }
        
        // Sort by days ascending (least rest first - they need it most!)
        usort($result, function($a, $b) {
            return $a['days'] - $b['days'];
        });
        
        return $result;
    }

    /**
     * Find the first entry in the current work session
     * SIMPLIFIED LOGIC:
     * 
     * Employee is ON-SITE if:
     *   - Has at least ONE work activity
     *   - Gap between work activities ≤3 days (non-work activities ignored)
     *   - Last WORK activity was ≤3 days ago
     * 
     * Returns the date of FIRST work activity in the continuous series
     * 
     * @param int $userId
     * @return DateTime|null - Date when current session started, or null if no open session
     */
    public function findFirstEntryInCurrentSession(int $userId): ?DateTime
    {
        $conn = $this->databaseService->getConnection();
        
        // Get all entries in chronological order (oldest first)
        $query = "SELECT dt, message FROM ozna_tabel 
                  WHERE user_id = ? 
                  AND dt >= DATE_SUB(NOW(), INTERVAL 180 DAY)
                  ORDER BY dt ASC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $entries = [];
        while ($row = $result->fetch_assoc()) {
            $entries[] = [
                'dt' => new DateTime($row['dt']),
                'message' => $row['message'],
                'is_work' => $this->isWorkActivity($row['message'])
            ];
        }
        
        if (empty($entries)) {
            return null;
        }
        
        // Find all WORK activities only
        $workActivities = [];
        foreach ($entries as $entry) {
            if ($entry['is_work']) {
                $workActivities[] = $entry['dt'];
            }
        }
        
        if (empty($workActivities)) {
            return null; // No work activities at all
        }
        
        // Check if last work activity was ≤3 days ago
        $now = new DateTime();
        $lastWorkDate = $workActivities[count($workActivities) - 1];
        $daysSinceLastWork = $this->getDaysBetween($lastWorkDate, $now);
        
        if ($daysSinceLastWork > 3) {
            return null; // Work stopped >3 days ago
        }
        
        // Find the start of continuous work session
        // Go backwards from last work activity, stop when gap >3 days
        $sessionStart = $workActivities[count($workActivities) - 1];
        
        for ($i = count($workActivities) - 1; $i > 0; $i--) {
            $currentDate = $workActivities[$i];
            $prevDate = $workActivities[$i - 1];
            
            $gap = $this->getDaysBetween($prevDate, $currentDate);
            
            if ($gap > 3) {
                // Gap too large, session starts at current date
                break;
            }
            
            // Continue going back
            $sessionStart = $prevDate;
        }
        
        return $sessionStart;
    }
    
    /**
     * Check if message is a work activity
     * 
     * UPDATED LOGIC (2025-10-20):
     * 
     * WORK activities (start/during/end of shift):
     *   - Дорога (start of shift - travel to site)
     *   - Инструктаж (start of work on site)
     *   - Сдача авансового отчета (end of shift - return from site)
     *   - All actual work activities
     *   - Простои (downtime - still counted as on-site)
     * 
     * NON-WORK activities (office/planning only):
     *   - Текущая деятельность
     *   - Участие в ЗПИ
     *   - Участие в ОПИ
     *   - Разработка программы работ
     *   - Разработка графика работ
     * 
     * @param string $message
     * @return bool
     */
    private function isWorkActivity(string $message): bool
    {
        // Office/remote activities are NOT work
        if (strpos($message, 'Текущая деятельность') !== false) return false;
        if (strpos($message, 'Участие в ЗПИ') !== false) return false;
        if (strpos($message, 'Участие в ОПИ') !== false) return false;
        if (strpos($message, 'Разработка программы работ') !== false) return false;
        if (strpos($message, 'Разработка графика работ') !== false) return false;
        
        // Everything else is WORK, including:
        // - Дорога (travel to site)
        // - Инструктаж (briefing on site)
        // - Сдача авансового отчета (return from site)
        // - Простои (downtime)
        // - All actual work activities
        return true;
    }
    
    /**
     * SCENARIO 1: Find session that starts with Дорога or Инструктаж
     * Requires >0 work activities (minimum 1) AFTER the marker
     * NOTE: Caller already checked that last entry is NOT a closing marker
     * 
     * @param array $entries - All entries in chronological order
     * @return DateTime|null
     */
    private function findSessionWithMarker(array $entries): ?DateTime
    {
        // Look for most recent start marker (Дорога or Инструктаж)
        $markerIndex = -1;
        $markerDate = null;
        
        for ($i = count($entries) - 1; $i >= 0; $i--) {
            $message = $entries[$i]['message'];
            
            if (strpos($message, 'Дорога') !== false || 
                strpos($message, 'Инструктаж') !== false) {
                $markerIndex = $i;
                $markerDate = $entries[$i]['dt'];
                break;
            }
        }
        
        if ($markerIndex === -1) {
            return null; // No start marker found
        }
        
        // Count work activities AFTER the marker
        $workActivitiesAfter = 0;
        for ($i = $markerIndex + 1; $i < count($entries); $i++) {
            if ($entries[$i]['is_work']) {
                $workActivitiesAfter++;
            }
        }
        
        // Requires >0 (minimum 1) work activities after marker
        // If employee passed briefing/road and did ANY work - they are on-site!
        if ($workActivitiesAfter > 0) {
            return $markerDate;
        }
        
        return null;
    }
    
    /**
     * SCENARIO 2: Find continuous work session without markers
     * Requires >4 work activities (minimum 5) with gaps ≤3 days
     * Last WORK activity must be ≤3 days ago
     * 
     * @param array $entries - All entries in chronological order
     * @return DateTime|null
     */
    private function findContinuousWorkSession(array $entries): ?DateTime
    {
        $now = new DateTime();
        
        // Find last WORK activity (not just any activity)
        $lastWorkEntry = null;
        for ($i = count($entries) - 1; $i >= 0; $i--) {
            if ($entries[$i]['is_work']) {
                $lastWorkEntry = $entries[$i];
                break;
            }
        }
        
        if ($lastWorkEntry === null) {
            return null; // No work activities at all
        }
        
        // Check if last WORK activity was ≤3 days ago
        $daysSinceLastWork = $this->getDaysBetween($lastWorkEntry['dt'], $now);
        if ($daysSinceLastWork > 3) {
            return null; // Work session ended
        }
        
        // Find continuous work activities (gaps ≤3 days)
        $continuousWorkStart = null;
        $continuousWorkCount = 0;
        $prevWorkDate = null;
        
        foreach ($entries as $entry) {
            if (!$entry['is_work']) {
                continue; // Skip non-work activities
            }
            
            if ($prevWorkDate !== null) {
                $gap = $this->getDaysBetween($prevWorkDate, $entry['dt']);
                if ($gap > 3) {
                    // Gap too large, restart counting
                    $continuousWorkStart = $entry['dt'];
                    $continuousWorkCount = 1;
                    $prevWorkDate = $entry['dt'];
                    continue;
                }
            }
            
            if ($continuousWorkStart === null) {
                $continuousWorkStart = $entry['dt'];
            }
            
            $continuousWorkCount++;
            $prevWorkDate = $entry['dt'];
        }
        
        // Requires >4 (minimum 5) continuous work activities
        if ($continuousWorkCount > 4) {
            return $continuousWorkStart;
        }
        
        return null;
    }

    /**
     * Find when employee went on free time
     * SIMPLIFIED LOGIC:
     * 
     * Employee is on FREE-TIME if:
     *   - Last WORK activity was >3 days ago
     * 
     * Free-time starts: NEXT DAY after last work activity
     * 
     * IMPORTANT: Non-work activities do NOT interrupt free-time
     *            and do NOT adjust the start date
     * 
     * @param int $userId
     * @return DateTime|null - Date when free time started (day after last work), or null if on-site
     */
    public function findLastContinuousWorkPeriod(int $userId): ?DateTime
    {
        // First check if session is open
        if ($this->findFirstEntryInCurrentSession($userId) !== null) {
            return null; // Employee is on-site, not on free time
        }
        
        $conn = $this->databaseService->getConnection();
        
        // Get all entries in chronological order (oldest first)
        $query = "SELECT dt, message FROM ozna_tabel 
                  WHERE user_id = ? 
                  AND dt >= DATE_SUB(NOW(), INTERVAL 180 DAY)
                  ORDER BY dt ASC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $entries = [];
        while ($row = $result->fetch_assoc()) {
            $entries[] = [
                'dt' => new DateTime($row['dt']),
                'message' => $row['message'],
                'is_work' => $this->isWorkActivity($row['message'])
            ];
        }
        
        if (empty($entries)) {
            return null; // No entries at all
        }
        
        // Find last WORK activity
        $lastWorkDate = null;
        foreach ($entries as $entry) {
            if ($entry['is_work']) {
                $lastWorkDate = $entry['dt'];
            }
        }
        
        if ($lastWorkDate === null) {
            return null; // No work activities
        }
        
        // Free-time starts NEXT DAY after last work
        $freeTimeStart = clone $lastWorkDate;
        $freeTimeStart->modify('+1 day');
        $freeTimeStart->setTime(0, 0, 0); // Set to beginning of day
        
        return $freeTimeStart;
    }
    
    /**
     * SCENARIO 1 (Free-time): Find session closed with Дорога or Аванс
     * Requires >0 work activities (minimum 1) BEFORE the marker
     * 
     * @param array $entries - All entries in chronological order
     * @return DateTime|null
     */
    private function findSessionClosureWithMarker(array $entries): ?DateTime
    {
        // Look for closing marker from the end
        $closingMarkerIndex = -1;
        $closingMarkerDate = null;
        
        for ($i = count($entries) - 1; $i >= 0; $i--) {
            $message = $entries[$i]['message'];
            
            if (strpos($message, 'Дорога') !== false || 
                strpos($message, 'Сдача авансового отчета') !== false) {
                $closingMarkerIndex = $i;
                $closingMarkerDate = $entries[$i]['dt'];
                break;
            }
        }
        
        if ($closingMarkerIndex === -1) {
            return null; // No closing marker found
        }
        
        // Count work activities BEFORE the closing marker
        $workActivitiesBefore = 0;
        for ($i = 0; $i < $closingMarkerIndex; $i++) {
            if ($entries[$i]['is_work']) {
                $workActivitiesBefore++;
            }
        }
        
        // Requires >0 (minimum 1) work activities before marker
        // If employee did ANY work before going home - valid session closure
        if ($workActivitiesBefore > 0) {
            return $closingMarkerDate;
        }
        
        return null;
    }
    
    /**
     * SCENARIO 2 (Free-time): Find stopped work session
     * Had >4 work activities (minimum 5), but last WORK activity >3 days ago
     * 
     * @param array $entries - All entries in chronological order
     * @return DateTime|null
     */
    private function findStoppedWorkSession(array $entries): ?DateTime
    {
        $now = new DateTime();
        
        // Find last WORK activity (not just any activity)
        $lastWorkEntry = null;
        for ($i = count($entries) - 1; $i >= 0; $i--) {
            if ($entries[$i]['is_work']) {
                $lastWorkEntry = $entries[$i];
                break;
            }
        }
        
        if ($lastWorkEntry === null) {
            return null; // No work activities at all
        }
        
        // Check if last WORK activity was >3 days ago
        $daysSinceLastWork = $this->getDaysBetween($lastWorkEntry['dt'], $now);
        if ($daysSinceLastWork <= 3) {
            return null; // Still working recently
        }
        
        // Count continuous work activities before stopping
        $continuousWorkCount = 0;
        $lastWorkDate = null;
        $prevWorkDate = null;
        
        foreach ($entries as $entry) {
            if (!$entry['is_work']) {
                continue;
            }
            
            if ($prevWorkDate !== null) {
                $gap = $this->getDaysBetween($prevWorkDate, $entry['dt']);
                if ($gap > 3) {
                    // Gap too large, restart counting
                    $continuousWorkCount = 1;
                    $lastWorkDate = $entry['dt'];
                    $prevWorkDate = $entry['dt'];
                    continue;
                }
            }
            
            $continuousWorkCount++;
            $lastWorkDate = $entry['dt'];
            $prevWorkDate = $entry['dt'];
        }
        
        // Requires >4 (minimum 5) continuous work activities
        if ($continuousWorkCount > 4) {
            return $lastWorkDate;
        }
        
        return null;
    }

    /**
     * Check if user has ANY work activities at all
     * 
     * @param int $userId
     * @return bool
     */
    private function hasAnyWorkActivities(int $userId): bool
    {
        $conn = $this->databaseService->getConnection();
        
        $query = "SELECT message FROM ozna_tabel 
                  WHERE user_id = ? 
                  ORDER BY dt DESC 
                  LIMIT 50";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            if ($this->isWorkActivity($row['message'])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Find FIRST activity date (oldest) for user
     * Used for employees with only non-work activities
     * 
     * @param int $userId
     * @return DateTime|null - First activity date or null if no records
     */
    private function findFirstActivity(int $userId): ?DateTime
    {
        $conn = $this->databaseService->getConnection();
        
        $query = "SELECT dt FROM ozna_tabel 
                  WHERE user_id = ? 
                  ORDER BY dt ASC 
                  LIMIT 1";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return new DateTime($row['dt']);
        }
        
        return null;
    }
    
    /**
     * Find last activity date of any type for user
     * Used as fallback when no work activities exist
     * 
     * @param int $userId
     * @return DateTime|null - Last activity date or null if no records
     */
    private function findLastActivity(int $userId): ?DateTime
    {
        $conn = $this->databaseService->getConnection();
        
        $query = "SELECT dt FROM ozna_tabel 
                  WHERE user_id = ? 
                  ORDER BY dt DESC 
                  LIMIT 1";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return new DateTime($row['dt']);
        }
        
        return null;
    }

    /**
     * Calculate days between two dates
     * 
     * @param DateTime $start
     * @param DateTime $end
     * @return int - Number of days
     */
    public function getDaysBetween(DateTime $start, DateTime $end): int
    {
        $interval = $start->diff($end);
        return (int)$interval->days;
    }

    /**
     * Get correct word form for "день/дня/дней"
     * 
     * @param int $days
     * @return string
     */
    public function getDaysWord(int $days): string
    {
        $lastDigit = $days % 10;
        $lastTwoDigits = $days % 100;
        
        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
            return "дней";
        }
        
        if ($lastDigit == 1) return "день";
        if ($lastDigit >= 2 && $lastDigit <= 4) return "дня";
        return "дней";
    }

    /**
     * Get current project code for user from their last work entry
     * 
     * @param int $userId
     * @return string - Project code or empty string if not found
     */
    public function getCurrentProjectCode(int $userId): string
    {
        $conn = $this->databaseService->getConnection();
        
        // Get last work activity
        $query = "SELECT message FROM ozna_tabel 
                  WHERE user_id = ? 
                  ORDER BY dt DESC 
                  LIMIT 10";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Check if this is a work activity
            if ($this->isWorkActivity($row['message'])) {
                // Extract project code from message
                // Format: "Проект <b>PROJECT_CODE</b>:"
                if (preg_match('/Проект <b>(.+?)<\/b>/', $row['message'], $matches)) {
                    return $matches[1];
                }
                break; // Found work activity but no project code
            }
        }
        
        return ''; // No project found
    }

    /**
     * Get employees currently on vacation or sick leave for a given functional direction.
     * Queries ozna_tabel for absence entries (Отпуск / Больничный) where
     * at least one entry covers today's date.
     *
     * @param string $functionalDirection - "Мобильная бригада", "Шеф-монтаж", or "Пусконаладка"
     * @param string $absenceType - 'Отпуск' or 'Больничный'
     * @return array - Array of ['fio' => ..., 'start_date' => ..., 'end_date' => ..., 'days' => ...]
     */
    public function getAbsenceEmployees(string $functionalDirection, string $absenceType): array
    {
        error_log("PersonnelService::getAbsenceEmployees called - fn: '{$functionalDirection}', type: '{$absenceType}'");

        $users = $this->getUsersByFunctionalDirection($functionalDirection);

        if (empty($users)) {
            return [];
        }

        $emoji = $absenceType === 'Больничный' ? '🏥' : '🏖';
        $today = new DateTime();
        $todayStr = $today->format('d.m.Y');
        $conn = $this->databaseService->getConnection();
        $result = [];

        foreach ($users as $user) {
            $userId = $user['user_id'];

            // Find all absence entries for this user of this type within the last 180 days
            $pattern = "%{$emoji}%: {$absenceType}%";
            $query = "SELECT message, dt FROM tg_bot.ozna_tabel 
                      WHERE user_id = ? AND message LIKE ? 
                      AND dt >= DATE_SUB(NOW(), INTERVAL 180 DAY)
                      ORDER BY dt ASC";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $userId, $pattern);
            $stmt->execute();
            $queryResult = $stmt->get_result();

            $absenceDates = [];

            while ($row = $queryResult->fetch_assoc()) {
                // Extract date from message: "🏥 DD.MM.YYYY: Больничный" or "🏖 DD.MM.YYYY: Отпуск"
                if (preg_match('/' . preg_quote($emoji, '/') . '\s*(\d{2}\.\d{2}\.\d{4}):\s*' . preg_quote($absenceType, '/') . '/', $row['message'], $matches)) {
                    $absenceDates[] = $matches[1];
                }
            }

            if (empty($absenceDates)) {
                continue;
            }

            // Check if today falls within any absence date
            $todayInAbsence = in_array($todayStr, $absenceDates);

            if (!$todayInAbsence) {
                continue; // This user is not currently absent
            }

            // Find continuous range containing today
            // Sort dates chronologically
            usort($absenceDates, function ($a, $b) {
                $dateA = DateTime::createFromFormat('d.m.Y', $a);
                $dateB = DateTime::createFromFormat('d.m.Y', $b);
                return $dateA <=> $dateB;
            });

            $startDate = $absenceDates[0];
            $endDate = $absenceDates[count($absenceDates) - 1];

            // Find the continuous group that includes today
            $groups = [];
            $currentGroup = [$absenceDates[0]];
            for ($i = 1; $i < count($absenceDates); $i++) {
                $prevDt = DateTime::createFromFormat('d.m.Y', $absenceDates[$i - 1]);
                $currDt = DateTime::createFromFormat('d.m.Y', $absenceDates[$i]);
                $gap = $prevDt->diff($currDt)->days;

                if ($gap <= 1) {
                    $currentGroup[] = $absenceDates[$i];
                } else {
                    $groups[] = $currentGroup;
                    $currentGroup = [$absenceDates[$i]];
                }
            }
            $groups[] = $currentGroup;

            // Find the group containing today
            foreach ($groups as $group) {
                if (in_array($todayStr, $group)) {
                    $startDate = $group[0];
                    $endDate = $group[count($group) - 1];
                    break;
                }
            }

            $totalDays = count(array_filter($absenceDates, function ($d) use ($startDate, $endDate) {
                $dt = DateTime::createFromFormat('d.m.Y', $d);
                $s = DateTime::createFromFormat('d.m.Y', $startDate);
                $e = DateTime::createFromFormat('d.m.Y', $endDate);
                return $dt >= $s && $dt <= $e;
            }));

            $result[] = [
                'user_id' => $userId,
                'fio' => $user['fio'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => $totalDays
            ];
        }

        // Sort by end_date ascending (soonest return first)
        usort($result, function ($a, $b) {
            $dateA = DateTime::createFromFormat('d.m.Y', $a['end_date']);
            $dateB = DateTime::createFromFormat('d.m.Y', $b['end_date']);
            return $dateA <=> $dateB;
        });

        error_log("PersonnelService::getAbsenceEmployees - Found " . count($result) . " employees on {$absenceType}");
        return $result;
    }
}
