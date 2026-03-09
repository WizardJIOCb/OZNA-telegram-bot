<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

/**
 * Database service to centralize all database operations
 * Replaces scattered database connections and queries
 */
class DatabaseService
{
    private string $host;
    private string $username; 
    private string $password;
    private string $database;
    private ?\mysqli $connection = null;

    // Константы уровней доступа
    public const ACCESS_LEVEL_GUEST = 0;
    public const ACCESS_LEVEL_USER = 1;
    public const ACCESS_LEVEL_SUPERVISOR = 2;
    public const ACCESS_LEVEL_ADMIN = 3;
    public const ACCESS_LEVEL_SUPER_ADMIN = 4;

    public function __construct(
        string $host = '82.146.42.213',
        string $username = 'WizardJIOCb', 
        string $password = '7qQ#28051961',
        string $database = 'tg_bot'
    ) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }

    /**
     * Get database connection
     */
    public function getConnection(): \mysqli
    {
        if ($this->connection === null) {
            $this->connection = new \mysqli($this->host, $this->username, $this->password, $this->database);
            
            if ($this->connection->connect_error) {
                throw new \Exception("Database connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to UTF-8
            $this->connection->set_charset("utf8mb4");
            
            // Explicitly enable autocommit to ensure changes are immediately visible
            $this->connection->autocommit(TRUE);
            
            error_log("DatabaseService: New connection created with autocommit=TRUE [v2025-01-16-FIX]");
        }
        
        return $this->connection;
    }

    /**
     * Get user data from ozna_data table
     */
    public function getUserData(int $userId): array
    {
        $query = "SELECT * FROM ozna_data WHERE user_id = ?";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            // Insert new user record if not exists
            $insertQuery = "INSERT INTO ozna_data (user_id) VALUES (?)";
            $insertStmt = $this->getConnection()->prepare($insertQuery);
            $insertStmt->bind_param("i", $userId);
            $insertStmt->execute();
            return [];
        }

        $userData = $user ? json_decode($user["data"], true) : [];

        // Debug: Log the user data
        error_log("DatabaseService: User {$userId} raw data: " . print_r($user, true));
        error_log("DatabaseService: User {$userId} decoded data: " . print_r($userData, true));

        // Set default values if not present
        if (!isset($userData["fio"])) $userData["fio"] = "";
        if (!isset($userData["functional_direction"])) $userData["functional_direction"] = "Не указано";
        // Установка уровня доступа по умолчанию (гость)
        if (!isset($userData["access_level"])) {
            $userData["access_level"] = self::ACCESS_LEVEL_GUEST;
            error_log("DatabaseService: User {$userId} access level not set, defaulting to GUEST");
        } else {
            error_log("DatabaseService: User {$userId} access level from database: {$userData["access_level"]}");
        }

        return $userData;
    }

    /**
     * Update user access level
     */
    public function updateUserAccessLevel(int $userId, int $accessLevel): bool
    {
        $userData = $this->getUserData($userId);
        $userData["access_level"] = $accessLevel;
        
        return $this->updateUserData($userId, $userData);
    }

    /**
     * Update user data in JSON format
     */
    public function updateUserData(int $userId, array $userData): bool
    {
        $query = "UPDATE ozna_data SET data = ? WHERE user_id = ?";
        $jsonData = json_encode($userData, JSON_UNESCAPED_UNICODE);
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("si", $jsonData, $userId);
        
        return $stmt->execute();
    }

    /**
     * Set user FIO
     */
    public function setUserFio(int $userId, string $fio): bool
    {
        $userData = $this->getUserData($userId);
        $userData["fio"] = $fio;
        
        return $this->updateUserData($userId, $userData);
    }

    /**
     * Set user functional direction
     */
    public function setUserFunctionalDirection(int $userId, string $direction): bool
    {
        $userData = $this->getUserData($userId);
        $userData["functional_direction"] = $direction;
        
        return $this->updateUserData($userId, $userData);
    }

    /**
     * Get user access level
     */
    public function getUserAccessLevel(int $userId): int
    {
        $userData = $this->getUserData($userId);
        $accessLevel = $userData["access_level"] ?? self::ACCESS_LEVEL_GUEST;
        
        // Debug: Log the user access level
        error_log("DatabaseService: User {$userId} access level: {$accessLevel}");
        
        return $accessLevel;
    }

    /**
     * Check if user is super admin
     */
    public function isUserSuperAdmin(int $userId): bool
    {
        $userData = $this->getUserData($userId);
        return isset($userData["is_super_admin"]) && $userData["is_super_admin"] === true;
    }

    /**
     * Set user as super admin
     */
    public function setSuperAdmin(int $userId, bool $isSuperAdmin = true): bool
    {
        $userData = $this->getUserData($userId);
        $userData["is_super_admin"] = $isSuperAdmin;
        
        return $this->updateUserData($userId, $userData);
    }

    /**
     * Get all super admins
     */
    public function getAllSuperAdmins(): array
    {
        // Get only Super Admins (access_level = 4)
        // Only Super Admins should receive export key reset notifications
        
        // Try multiple query variations to find super admins
        $queries = [
            "SELECT user_id, data FROM ozna_data WHERE JSON_EXTRACT(data, '$.access_level') = 4",
            "SELECT user_id, data FROM ozna_data WHERE JSON_UNQUOTE(JSON_EXTRACT(data, '$.access_level')) = '4'"
        ];
        
        $superAdmins = [];
        
        foreach ($queries as $index => $query) {
            error_log("DatabaseService: Trying query #{$index}: {$query}");
            $result = $this->getConnection()->query($query);
            
            if ($result) {
                $count = 0;
                while ($row = $result->fetch_assoc()) {
                    $superAdmins[] = (int)$row['user_id'];
                    $count++;
                    error_log("DatabaseService: Found super admin: {$row['user_id']}");
                }
                error_log("DatabaseService: Query #{$index} found {$count} super admins");
                
                if ($count > 0) {
                    break; // Found admins, no need to try other queries
                }
            } else {
                error_log("DatabaseService: Query #{$index} failed: " . $this->getConnection()->error);
            }
        }
        
        // Deduplicate
        $superAdmins = array_unique($superAdmins);
        
        error_log("DatabaseService: getAllSuperAdmins found " . count($superAdmins) . " super admins (level 4 only): " . implode(', ', $superAdmins));
        
        return $superAdmins;
    }

    // ========================================
    // EXPORT SECURITY METHODS
    // ========================================
    
    /**
     * Get user's export key
     */
    public function getUserExportKey(int $userId): ?string
    {
        $userData = $this->getUserData($userId);
        return $userData['export_key'] ?? null;
    }
    
    /**
     * Set user's export key
     */
    public function setUserExportKey(int $userId, string $exportKey): bool
    {
        $userData = $this->getUserData($userId);
        $userData['export_key'] = $exportKey;
        $userData['export_key_set_at'] = date('Y-m-d H:i:s');
        
        return $this->updateUserData($userId, $userData);
    }
    
    /**
     * Verify user's export key
     */
    public function verifyUserExportKey(int $userId, string $providedKey): bool
    {
        $storedKey = $this->getUserExportKey($userId);
        
        if ($storedKey === null) {
            return false; // No key set yet
        }
        
        return $storedKey === $providedKey;
    }
    
    /**
     * Log export operation for audit trail
     */
    public function logExport(
        int $userId,
        string $userFio,
        string $exportType,
        string $exportScope,
        int $recordsCount,
        string $fileName,
        ?string $projectCode = null,
        ?string $periodStart = null,
        ?string $periodEnd = null,
        ?string $statusFilter = null
    ): bool {
        try {
            // Use direct query to handle NULL values properly
            $userFio = $this->getConnection()->real_escape_string($userFio);
            $exportType = $this->getConnection()->real_escape_string($exportType);
            $exportScope = $this->getConnection()->real_escape_string($exportScope);
            $fileName = $this->getConnection()->real_escape_string($fileName);
            
            $projectCodeSql = $projectCode ? "'" . $this->getConnection()->real_escape_string($projectCode) . "'" : 'NULL';
            $periodStartSql = $periodStart ? "'" . $this->getConnection()->real_escape_string($periodStart) . "'" : 'NULL';
            $periodEndSql = $periodEnd ? "'" . $this->getConnection()->real_escape_string($periodEnd) . "'" : 'NULL';
            $statusFilterSql = $statusFilter ? "'" . $this->getConnection()->real_escape_string($statusFilter) . "'" : 'NULL';
            
            $query = "INSERT INTO export_logs 
                      (user_id, user_fio, export_type, export_scope, project_code, 
                       period_start, period_end, status_filter, records_count, file_name) 
                      VALUES ({$userId}, '{$userFio}', '{$exportType}', '{$exportScope}', {$projectCodeSql}, 
                              {$periodStartSql}, {$periodEndSql}, {$statusFilterSql}, {$recordsCount}, '{$fileName}')";
            
            $result = $this->getConnection()->query($query);
            
            if ($result) {
                error_log("DatabaseService: Export logged - User: {$userId}, Type: {$exportType}, Scope: {$exportScope}, Records: {$recordsCount}");
            } else {
                error_log("DatabaseService: Failed to log export - " . $this->getConnection()->error);
                error_log("DatabaseService: Query was: {$query}");
            }
            
            return $result;
        } catch (\Exception $e) {
            error_log("DatabaseService: Exception in logExport: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get export logs for a specific user
     */
    public function getUserExportLogs(int $userId, int $limit = 50): array
    {
        try {
            $query = "SELECT * FROM export_logs WHERE user_id = ? ORDER BY exported_at DESC LIMIT ?";
            
            $stmt = $this->getConnection()->prepare($query);
            if (!$stmt) {
                error_log("DatabaseService: Failed to prepare getUserExportLogs query: " . $this->getConnection()->error);
                return [];
            }
            
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $logs = [];
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
            
            return $logs;
        } catch (\Exception $e) {
            error_log("DatabaseService: Error in getUserExportLogs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total count of export logs
     */
    public function getTotalExportLogsCount(): int
    {
        try {
            $query = "SELECT COUNT(*) as total FROM export_logs";
            $result = $this->getConnection()->query($query);
            
            if (!$result) {
                error_log("DatabaseService: Failed to get total export logs count: " . $this->getConnection()->error);
                return 0;
            }
            
            $row = $result->fetch_assoc();
            return (int)($row['total'] ?? 0);
        } catch (\Exception $e) {
            error_log("DatabaseService: Error in getTotalExportLogsCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all export logs (for admins)
     */
    public function getAllExportLogs(int $limit = 100, int $offset = 0): array
    {
        try {
            $query = "SELECT * FROM export_logs ORDER BY exported_at DESC LIMIT ? OFFSET ?";
            
            $stmt = $this->getConnection()->prepare($query);
            if (!$stmt) {
                error_log("DatabaseService: Failed to prepare getAllExportLogs query: " . $this->getConnection()->error);
                return [];
            }
            
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $logs = [];
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
            
            return $logs;
        } catch (\Exception $e) {
            error_log("DatabaseService: Error in getAllExportLogs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get export statistics
     */
    public function getExportStats(): array
    {
        $query = "SELECT 
                    COUNT(*) as total_exports,
                    COUNT(DISTINCT user_id) as unique_users,
                    SUM(records_count) as total_records,
                    export_type,
                    DATE(exported_at) as export_date
                  FROM export_logs 
                  WHERE exported_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  GROUP BY export_type, export_date
                  ORDER BY export_date DESC";
        
        $result = $this->getConnection()->query($query);
        
        $stats = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $stats[] = $row;
            }
        }
        
        return $stats;
    }

    /**
     * Check if user has recent tabel entry (within 10 minutes)
     * Super admins are exempt from this check
     */
    public function hasRecentTabelEntry(int $userId, bool $isSuperAdmin = false): ?array
    {
        // Суперадмины могут отправлять сообщения без ограничения по времени
        if ($isSuperAdmin) {
            return null;
        }
        
        $query = "SELECT COUNT(*) as count, ot.dt FROM ozna_tabel ot 
                  WHERE ot.user_id = ? AND ot.dt > DATE_SUB(NOW(), INTERVAL 600 SECOND)";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return ($row && $row['count'] > 0) ? $row : null;
    }

    /**
     * Check if user already submitted a report for this activity on the given date
     * 
     * @param int $userId User ID
     * @param string $activityType Activity type to check for duplicates
     * @param string $reportDate Report date in format "dd.mm.YYYY" (e.g., "19.10.2025")
     * @param string $projectCode Project code to check (optional for more precise matching)
     * @return array|null Returns the existing report info if found, null otherwise
     */
    public function checkDuplicateActivityReport(int $userId, string $activityType, string $reportDate, string $projectCode = ''): ?array
    {
        // Messages are in format: "✅04.10.2025, Проект XXX: Добавлена запись в табель: 📅 Работы по графику"
        // We need to check for:
        // 1. Same user
        // 2. Same activity type (in message text)
        // 3. Same date (in message text, after ✅ emoji)
        // 4. Optionally same project
        
        $query = "SELECT dt + INTERVAL 2 HOUR as dt, message 
                  FROM ozna_tabel 
                  WHERE user_id = ? 
                  AND message LIKE ? 
                  AND message LIKE ? ";
        
        // Add project filter if provided for more precise duplicate detection
        if (!empty($projectCode)) {
            $query .= " AND message LIKE ? ";
        }
        
        $query .= " ORDER BY dt DESC 
                  LIMIT 1";
        
        $stmt = $this->getConnection()->prepare($query);
        
        // Create patterns to match in the message:
        // 1. Activity type: "📅 Работы по графику"
        $activityPattern = "%{$activityType}%";
        
        // 2. Date pattern: "✅19.10.2025," (the date appears right after the ✅ emoji)
        $datePattern = "%{$reportDate},%";
        
        if (!empty($projectCode)) {
            $projectPattern = "%{$projectCode}%";
            $stmt->bind_param("isss", $userId, $activityPattern, $datePattern, $projectPattern);
        } else {
            $stmt->bind_param("iss", $userId, $activityPattern, $datePattern);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row ? $row : null;
    }

    /**
     * Add user tabel entry
     */
    public function addUserTabel(int $userId, string $message): bool
    {
        // Clean empty usernames
        $message = str_replace("@ ", "", $message);
        
        $query = "INSERT INTO tg_bot.ozna_tabel (user_id, message, dt) VALUES (?, ?, current_timestamp())";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("is", $userId, $message);
        
        return $stmt->execute();
    }

    /**
     * Get user's last 10 tabel entries
     */
    public function getUserLastTabelEntries(int $userId, int $limit = 10): array
    {
        $query = "SELECT dt + INTERVAL 2 HOUR as dt, message FROM ozna_tabel ot 
                  WHERE ot.user_id = ? ORDER BY dt DESC LIMIT ?";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $entries = [];
        while ($row = $result->fetch_assoc()) {
            $entries[] = $row;
        }
        
        return array_reverse($entries);
    }

    /**
     * Get all user's tabel entries for export
     */
    public function getAllUserTabelEntries(int $userId): array
    {
        $query = "SELECT dt + INTERVAL 2 HOUR as dt, message FROM ozna_tabel ot 
                  WHERE ot.user_id = ? ORDER BY dt ASC";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $entries = [];
        while ($row = $result->fetch_assoc()) {
            $entries[] = $row;
        }
        
        return $entries;
    }

    /**
     * Get all tabel entries for export
     */
    public function getAllTabelEntries(): array
    {
        $query = "SELECT ot.user_id, dt + INTERVAL 2 HOUR as dt, message FROM ozna_tabel ot ORDER BY dt ASC";
        
        $result = $this->getConnection()->query($query);
        
        $entries = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $entries[] = $row;
            }
        }
        
        return $entries;
    }

    /**
     * Get user's project activity for export
     */
    public function getUserProjectActivity(int $userId): array
    {
        $query = "SELECT dt + INTERVAL 2 HOUR as dt, message FROM ozna_tabel ot 
                  WHERE ot.user_id = ? AND (message LIKE 'ОИ%' OR message LIKE 'БКНС%') 
                  ORDER BY dt ASC";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $entries = [];
        while ($row = $result->fetch_assoc()) {
            $entries[] = $row;
        }
        
        return $entries;
    }

    /**
     * Get tabel entries for a specific project with optional date range
     */
    public function getProjectTabelEntries(string $projectCode, ?string $startDate, string $endDate): array
    {
        $query = "SELECT ot.user_id, dt + INTERVAL 2 HOUR as dt, message 
                  FROM ozna_tabel ot 
                  WHERE message LIKE ?";
        
        $params = ["%{$projectCode}%"];
        $types = "s";
        
        if ($startDate !== null) {
            $query .= " AND dt >= ?";
            $params[] = $startDate;
            $types .= "s";
        }
        
        $query .= " AND dt <= ? ORDER BY dt ASC";
        $params[] = $endDate;
        $types .= "s";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $entries = [];
        while ($row = $result->fetch_assoc()) {
            $entries[] = $row;
        }
        
        return $entries;
    }

    /**
     * Get tabel entries for simple report by date range
     */
    public function getSimpleReportEntries(string $startDate, string $endDate): array
    {
        $query = "SELECT ot.user_id, dt + INTERVAL 2 HOUR as dt, message 
                  FROM ozna_tabel ot 
                  WHERE dt >= ? AND dt <= ? 
                  ORDER BY dt ASC";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $entries = [];
        while ($row = $result->fetch_assoc()) {
            $entries[] = $row;
        }
        
        return $entries;
    }

    /**
     * Get all users with their data
     */
    public function getAllUsers(): array
    {
        $query = "SELECT user_id, data FROM ozna_data";
        $result = $this->getConnection()->query($query);
        
        $users = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $users[] = [
                    'user_id' => (int)$row['user_id'],
                    'data' => json_decode($row['data'] ?? '{}', true) ?: []
                ];
            }
        }
        
        return $users;
    }

    /**
     * Create access requests table if it doesn't exist
     */
    public function createAccessRequestsTable(): bool
    {
        $query = "
            CREATE TABLE IF NOT EXISTS access_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT NOT NULL,
                user_name VARCHAR(255) NOT NULL,
                user_telegram_name VARCHAR(255),
                user_fio VARCHAR(255),
                chat_id BIGINT NOT NULL,
                timestamp INT NOT NULL,
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                processed_by BIGINT NULL,
                processed_at INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        return $this->getConnection()->query($query);
    }

    /**
     * Add a new access request
     */
    public function addAccessRequest(array $requestData): bool
    {
        $query = "INSERT INTO access_requests 
                  (user_id, user_name, user_telegram_name, user_fio, chat_id, timestamp) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param(
            "issssi",
            $requestData['user_id'],
            $requestData['user_name'],
            $requestData['user_telegram_name'],
            $requestData['user_fio'],
            $requestData['chat_id'],
            $requestData['timestamp']
        );
        
        return $stmt->execute();
    }

    /**
     * Get all pending access requests
     */
    public function getPendingAccessRequests(): array
    {
        try {
            $query = "SELECT * FROM access_requests WHERE status = 'pending' ORDER BY timestamp DESC";
            $result = $this->getConnection()->query($query);
            
            $requests = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $requests[] = $row;
                }
            }
            
            return $requests;
        } catch (\Exception $e) {
            error_log("DatabaseService: Error getting pending access requests: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get access request by ID
     */
    public function getAccessRequestById(int $id): ?array
    {
        $query = "SELECT * FROM access_requests WHERE id = ?";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        
        return $request ?: null;
    }

    /**
     * Get access request by user ID
     */
    public function getAccessRequestByUserId(int $userId): ?array
    {
        $query = "SELECT * FROM access_requests WHERE user_id = ? AND status = 'pending' ORDER BY timestamp DESC LIMIT 1";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        
        return $request ?: null;
    }

    /**
     * Approve access request
     */
    public function approveAccessRequest(int $id, int $processedBy): bool
    {
        $query = "UPDATE access_requests SET status = 'approved', processed_by = ?, processed_at = ? WHERE id = ?";
        $stmt = $this->getConnection()->prepare($query);
        $processedAt = time();
        $stmt->bind_param("iii", $processedBy, $processedAt, $id);
        
        return $stmt->execute();
    }

    /**
     * Reject access request
     */
    public function rejectAccessRequest(int $id, int $processedBy): bool
    {
        $query = "UPDATE access_requests SET status = 'rejected', processed_by = ?, processed_at = ? WHERE id = ?";
        $stmt = $this->getConnection()->prepare($query);
        $processedAt = time();
        $stmt->bind_param("iii", $processedBy, $processedAt, $id);
        
        return $stmt->execute();
    }

    /**
     * Clear all pending access requests
     */
    public function clearPendingAccessRequests(): bool
    {
        $query = "DELETE FROM access_requests WHERE status = 'pending'";
        return $this->getConnection()->query($query);
    }

    // ===== PROJECT MODERATION METHODS =====

    /**
     * Добавить проект на модерацию
     */
    public function addPendingProject(string $projectCode, int $userId): bool|int
    {
        // Приводим к верхнему регистру и обрезаем пробелы (как в AdminService::addProject)
        $projectCode = trim(strtoupper($projectCode));
        
        try {
            $conn = $this->getConnection();
            
            // CRITICAL FIX: Delete old inactive entries with same name to avoid unique key conflicts
            // This allows users to re-add previously rejected projects
            $deleteQuery = "DELETE FROM project_moderation 
                            WHERE UPPER(current_name) = UPPER(?) 
                            AND is_active = 0";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("s", $projectCode);
            $deleteStmt->execute();
            
            if ($deleteStmt->affected_rows > 0) {
                error_log("DatabaseService: Deleted {$deleteStmt->affected_rows} old inactive entries for '{$projectCode}' before re-adding");
            }
            
            // Now insert the new pending project
            $query = "INSERT INTO project_moderation 
                      (project_code, original_name, current_name, status, added_by, added_at) 
                      VALUES (?, ?, ?, 'pending', ?, NOW())";
            
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                error_log("DatabaseService: Failed to prepare statement: " . $conn->error);
                return false;
            }
            
            $stmt->bind_param("sssi", $projectCode, $projectCode, $projectCode, $userId);
            
            if ($stmt->execute()) {
                $insertId = $conn->insert_id;
                error_log("DatabaseService: Project '{$projectCode}' added to moderation by user {$userId}, ID: {$insertId}");
                return $insertId;
            }
            
            // CRITICAL: Log the exact error if INSERT fails
            $errorCode = $stmt->errno;
            $errorMsg = $stmt->error;
            error_log("DatabaseService: Failed to add project '{$projectCode}' to moderation.");
            error_log("DatabaseService: MySQL Error Code: {$errorCode}");
            error_log("DatabaseService: MySQL Error Message: {$errorMsg}");
            
            // Check if it's a duplicate key error (error code 1062)
            if ($errorCode === 1062) {
                error_log("DatabaseService: DUPLICATE KEY ERROR - Project '{$projectCode}' likely already exists with same status!");
                // This should have been caught by projectExists() check earlier
                // If we're here, there's a race condition or logic error
                throw new \Exception("Duplicate project: '{$projectCode}' already exists (MySQL error 1062)");
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log("DatabaseService: Exception while adding project: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Получить проекты на модерации
     */
    public function getPendingProjects(): array
    {
        $query = "SELECT pm.*, 
                         od.data->>'$.fio' as user_fio,
                         (SELECT COUNT(*) FROM ozna_tabel WHERE message LIKE CONCAT('%', pm.current_name, '%') AND user_id = pm.added_by) as tabel_count
                  FROM project_moderation pm
                  LEFT JOIN ozna_data od ON pm.added_by = od.user_id
                  WHERE pm.status = 'pending' AND pm.is_active = 1
                  ORDER BY pm.added_at ASC";
        
        $result = $this->getConnection()->query($query);
        $projects = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $projects[] = $row;
            }
        }
        
        return $projects;
    }

    /**
     * Получить количество проектов на модерации
     */
    public function getPendingProjectsCount(): int
    {
        $query = "SELECT COUNT(*) as count 
                  FROM project_moderation 
                  WHERE status = 'pending' AND is_active = 1";
        
        $result = $this->getConnection()->query($query);
        $row = $result->fetch_assoc();
        
        return (int)($row['count'] ?? 0);
    }

    /**
     * Получить проект на модерации по ID
     */
    public function getModerationProjectById(int $projectId): ?array
    {
        $query = "SELECT pm.*, 
                         od.data->>'$.fio' as user_fio,
                         (SELECT COUNT(*) FROM ozna_tabel WHERE message LIKE CONCAT('%', pm.current_name, '%') AND user_id = pm.added_by) as tabel_count
                  FROM project_moderation pm
                  LEFT JOIN ozna_data od ON pm.added_by = od.user_id
                  WHERE pm.id = ?";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        $project = $result->fetch_assoc();
        
        if ($project) {
            error_log("DatabaseService: getModerationProjectById - Project: {$project['current_name']}, User: {$project['added_by']}, Tabel count: {$project['tabel_count']}");
        }
        
        return $project;
    }

    /**
     * Одобрить проект
     */
    public function approveProject(int $projectId, int $moderatorId): bool
    {
        // Получаем информацию о проекте
        $project = $this->getModerationProjectById($projectId);
        if (!$project) {
            error_log("DatabaseService: Project ID {$projectId} not found for approval");
            return false;
        }
        
        $projectName = $project['current_name'];
        
        // Удаляем старые неактивные записи с таким же названием, чтобы избежать конфликта unique key
        $deleteQuery = "DELETE FROM project_moderation 
                        WHERE UPPER(current_name) = UPPER(?) 
                        AND is_active = 0 
                        AND id != ?";
        $deleteStmt = $this->getConnection()->prepare($deleteQuery);
        $deleteStmt->bind_param("si", $projectName, $projectId);
        $deleteStmt->execute();
        
        if ($deleteStmt->affected_rows > 0) {
            error_log("DatabaseService: Deleted {$deleteStmt->affected_rows} old inactive entries for '{$projectName}'");
        }
        
        // Обновляем статус в базе
        $query = "UPDATE project_moderation 
                  SET status = 'active', 
                      moderated_by = ?, 
                      moderated_at = NOW() 
                  WHERE id = ?";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("ii", $moderatorId, $projectId);
        
        $success = $stmt->execute();
        
        if (!$success) {
            error_log("DatabaseService: Failed to update project status for ID {$projectId}");
            return false;
        }
        
        error_log("DatabaseService: Project ID {$projectId} approved by moderator {$moderatorId}");
        
        // Добавляем проект в projects.json
        try {
            $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService(
                new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService()
            );
            
            // Проверяем, есть ли уже такой проект
            if (!$adminService->projectExists($projectName)) {
                $addSuccess = $adminService->addProject($projectName);
                if ($addSuccess) {
                    error_log("DatabaseService: Project '{$projectName}' added to projects.json");
                } else {
                    error_log("DatabaseService: Failed to add project '{$projectName}' to projects.json");
                }
            } else {
                error_log("DatabaseService: Project '{$projectName}' already exists in projects.json");
            }
        } catch (\Exception $e) {
            error_log("DatabaseService: Error adding project to projects.json: " . $e->getMessage());
            // Не отменяем одобрение, даже если не удалось добавить в JSON
        }
        
        return true;
    }

    /**
     * Переименовать проект и обновить все записи табеля
     */
    public function renameProject(int $projectId, string $newName, int $moderatorId, string $comment = ''): bool
    {
        // Приводим к верхнему регистру и обрезаем пробелы
        $newName = trim(strtoupper($newName));
        
        // Начинаем транзакцию
        $this->getConnection()->begin_transaction();
        
        try {
            // Получаем старое название и ID пользователя
            $query = "SELECT current_name, added_by FROM project_moderation WHERE id = ?";
            $stmt = $this->getConnection()->prepare($query);
            $stmt->bind_param("i", $projectId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if (!$row) {
                throw new \Exception("Project not found");
            }
            
            $oldName = $row['current_name'];
            $userId = $row['added_by'];
            
            // Удаляем старые неактивные записи с новым названием, чтобы избежать конфликта unique key
            $deleteQuery = "DELETE FROM project_moderation 
                            WHERE UPPER(current_name) = UPPER(?) 
                            AND is_active = 0 
                            AND id != ?";
            $deleteStmt = $this->getConnection()->prepare($deleteQuery);
            $deleteStmt->bind_param("si", $newName, $projectId);
            $deleteStmt->execute();
            
            if ($deleteStmt->affected_rows > 0) {
                error_log("DatabaseService: Deleted {$deleteStmt->affected_rows} old inactive entries for new name '{$newName}' before rename");
            }
            
            // Обновляем проект
            $query = "UPDATE project_moderation 
                      SET current_name = ?, 
                          status = 'renamed', 
                          moderated_by = ?, 
                          moderated_at = NOW(),
                          moderation_comment = ? 
                      WHERE id = ?";
            
            $stmt = $this->getConnection()->prepare($query);
            $stmt->bind_param("sisi", $newName, $moderatorId, $comment, $projectId);
            $stmt->execute();
            
            // Обновляем только записи табеля этого пользователя
            // Используем REPLACE для замены кода проекта в тексте сообщения
            $query = "UPDATE ozna_tabel SET message = REPLACE(message, ?, ?) WHERE message LIKE CONCAT('%', ?, '%') AND user_id = ?";
            $stmt = $this->getConnection()->prepare($query);
            $stmt->bind_param("sssi", $oldName, $newName, $oldName, $userId);
            $stmt->execute();
            $affectedRows = $stmt->affected_rows;
            
            // Фиксируем транзакцию
            $this->getConnection()->commit();
            
            error_log("DatabaseService: Project ID {$projectId} renamed from '{$oldName}' to '{$newName}' by moderator {$moderatorId}. Updated {$affectedRows} tabel entries for user {$userId}.");
            
            // Переименовываем в projects.json
            try {
                $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService(
                    new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService()
                );
                
                // Удаляем старое название
                if ($adminService->projectExists($oldName)) {
                    $adminService->deleteProject($oldName);
                    error_log("DatabaseService: Removed old project '{$oldName}' from projects.json");
                }
                
                // Добавляем новое название
                if (!$adminService->projectExists($newName)) {
                    $adminService->addProject($newName);
                    error_log("DatabaseService: Added new project '{$newName}' to projects.json");
                }
            } catch (\Exception $e) {
                error_log("DatabaseService: Error updating projects.json during rename: " . $e->getMessage());
                // Не отменяем переименование
            }
            
            return true;
            
        } catch (\Exception $e) {
            // Откатываем при ошибке
            $this->getConnection()->rollback();
            error_log("DatabaseService: Error renaming project: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Отклонить проект
     */
    public function rejectProject(int $projectId, int $moderatorId, string $reason): bool
    {
        // Получаем информацию о проекте перед удалением старых записей
        $project = $this->getModerationProjectById($projectId);
        if (!$project) {
            error_log("DatabaseService: Project ID {$projectId} not found for rejection");
            return false;
        }
        
        $projectName = $project['current_name'];
        
        // Удаляем старые неактивные записи с таким же названием, чтобы избежать конфликта unique key
        $deleteQuery = "DELETE FROM project_moderation 
                        WHERE UPPER(current_name) = UPPER(?) 
                        AND is_active = 0 
                        AND id != ?";
        $deleteStmt = $this->getConnection()->prepare($deleteQuery);
        $deleteStmt->bind_param("si", $projectName, $projectId);
        $deleteStmt->execute();
        
        if ($deleteStmt->affected_rows > 0) {
            error_log("DatabaseService: Deleted {$deleteStmt->affected_rows} old inactive entries for '{$projectName}' before rejection");
        }
        
        $query = "UPDATE project_moderation 
                  SET status = 'rejected', 
                      moderated_by = ?, 
                      moderated_at = NOW(),
                      moderation_comment = ?,
                      is_active = 0
                  WHERE id = ?";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("isi", $moderatorId, $reason, $projectId);
        
        $success = $stmt->execute();
        
        if ($success) {
            error_log("DatabaseService: Project ID {$projectId} rejected by moderator {$moderatorId}. Reason: {$reason}");
        }
        
        return $success;
    }

    /**
     * Проверить, существует ли проект с таким названием
     * CRITICAL: Checks BOTH project_moderation table AND projects.json file
     */
    public function projectExists(string $projectCode): bool
    {
        // Приводим к верхнему регистру для сравнения
        $projectCode = trim(strtoupper($projectCode));
        
        // Check 1: Database (project_moderation table)
        $query = "SELECT COUNT(*) as count 
                  FROM project_moderation 
                  WHERE UPPER(current_name) = ? 
                  AND status IN ('pending', 'active', 'renamed')
                  AND is_active = 1";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("s", $projectCode);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $dbCount = $row['count'] ?? 0;
        
        if ($dbCount > 0) {
            error_log("DatabaseService::projectExists('{$projectCode}'): Found in DATABASE, count={$dbCount}");
            return true;
        }
        
        // Check 2: projects.json file (legacy projects)
        try {
            $projectsFile = __DIR__ . '/../../data/projects.json';
            if (file_exists($projectsFile)) {
                $jsonContent = file_get_contents($projectsFile);
                $projects = json_decode($jsonContent, true);
                
                if (is_array($projects)) {
                    // Normalize all projects to uppercase for comparison
                    $normalizedProjects = array_map(function($p) {
                        return trim(strtoupper($p));
                    }, $projects);
                    
                    if (in_array($projectCode, $normalizedProjects)) {
                        error_log("DatabaseService::projectExists('{$projectCode}'): Found in PROJECTS.JSON file");
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("DatabaseService::projectExists: Error reading projects.json: " . $e->getMessage());
            // Don't fail if we can't read the file, just continue
        }
        
        error_log("DatabaseService::projectExists('{$projectCode}'): NOT FOUND in either database or JSON file");
        return false;
    }

    /**
     * Получить все активные проекты (для селектора проектов)
     */
    public function getAllActiveProjects(): array
    {
        $query = "SELECT current_name as project_code, status 
                  FROM project_moderation 
                  WHERE status IN ('active', 'renamed')
                  AND is_active = 1
                  ORDER BY added_at DESC";
        
        $result = $this->getConnection()->query($query);
        $projects = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $projects[] = $row['project_code'];
            }
        }
        
        return $projects;
    }

    /**
     * Умная сортировка проектов:
     * 1. ОИ проекты группами: ОИ1ХХ, ОИ2ХХ, ОИ3ХХ и т.д.
     * 2. Внутри каждой группы - по возрастанию номера
     * 3. Другие шифры (БКНС, ИП и т.д.) - в алфавитно-цифровом порядке в конце
     */
    public function sortProjectsIntelligently(array $projects): array
    {
        usort($projects, function($a, $b) {
            // Приводим к верхнему регистру для сравнения
            $a = strtoupper(trim($a));
            $b = strtoupper(trim($b));
            
            // Проверяем, является ли проект типом "ОИ"
            $aIsOI = preg_match('/^ОИ(\d+)/', $a, $aMatches);
            $bIsOI = preg_match('/^ОИ(\d+)/', $b, $bMatches);
            
            // Если оба проекта типа "ОИ"
            if ($aIsOI && $bIsOI) {
                $aNum = (int)$aMatches[1];
                $bNum = (int)$bMatches[1];
                
                // Определяем группу (первая цифра числа)
                $aGroup = (int)($aNum / 100); // 651 -> 6, 834 -> 8, 1234 -> 12
                $bGroup = (int)($bNum / 100);
                
                // Сначала сортируем по группе
                if ($aGroup !== $bGroup) {
                    return $aGroup - $bGroup;
                }
                
                // Внутри группы - по номеру
                if ($aNum !== $bNum) {
                    return $aNum - $bNum;
                }
                
                // Если номера одинаковые, сравниваем полностью (для случаев ОИ420-02, ОИ420-04)
                return strcmp($a, $b);
            }
            
            // Если только первый - "ОИ", он идет раньше
            if ($aIsOI) {
                return -1;
            }
            
            // Если только второй - "ОИ", он идет раньше
            if ($bIsOI) {
                return 1;
            }
            
            // Оба НЕ "ОИ" - алфавитно-цифровая сортировка
            return strcmp($a, $b);
        });
        
        return $projects;
    }

    /**
     * Получить все активные и pending проекты (для списка проектов на контроле)
     */
    public function getAllActiveAndPendingProjects(): array
    {
        $conn = $this->getConnection();
        
        // Use SQL_NO_CACHE to prevent MySQL query cache
        $query = "SELECT SQL_NO_CACHE DISTINCT current_name 
                  FROM project_moderation 
                  WHERE status IN ('pending', 'active', 'renamed')
                  AND is_active = 1
                  AND status != 'rejected'";
        
        $result = $conn->query($query);
        $projects = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $projects[] = $row['current_name'];
            }
            $result->free();
        }
        
        // Применяем умную сортировку
        return $this->sortProjectsIntelligently($projects);
    }

    /**
     * Получить проекты пользователя (включая его pending-проекты)
     */
    public function getUserProjectsWithPending(int $userId): array
    {
        $query = "SELECT current_name as project_code, status 
                  FROM project_moderation 
                  WHERE (added_by = ? AND status IN ('pending', 'active', 'renamed'))
                     OR status IN ('active', 'renamed')
                  AND is_active = 1
                  ORDER BY 
                    CASE WHEN added_by = ? AND status = 'pending' THEN 0 ELSE 1 END,
                    added_at DESC";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
        
        return $projects;
    }

    /**
     * Удалить проект из базы данных (пометить как неактивный)
     */
    public function deleteProjectFromDatabase(string $projectCode): bool
    {
        $projectCode = trim(strtoupper($projectCode));
        
        // Just deactivate the project without changing status to avoid unique key conflicts
        $query = "UPDATE project_moderation 
                  SET is_active = 0
                  WHERE UPPER(current_name) = ?
                  AND is_active = 1";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("s", $projectCode);
        $success = $stmt->execute();
        
        if ($success && $stmt->affected_rows > 0) {
            error_log("DatabaseService: Project '{$projectCode}' marked as inactive in database");
            return true;
        }
        
        return false;
    }

    // ==================== TABEL MANAGEMENT METHODS ====================

    /**
     * Получить записи табеля с фильтрацией
     * 
     * @param array $filters Фильтры: user_id, project_code, date_from, date_to, search
     * @param int $limit Максимальное количество записей
     * @param int $offset Смещение для пагинации
     * @return array Массив записей
     */
    public function getTabelEntriesFiltered(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $query = "SELECT ot.id, ot.user_id, ot.dt + INTERVAL 2 HOUR as dt, ot.message,
                         od.data->>'$.fio' as user_fio
                  FROM ozna_tabel ot
                  LEFT JOIN ozna_data od ON ot.user_id = od.user_id
                  WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Фильтр по пользователю
        if (!empty($filters['user_id'])) {
            $query .= " AND ot.user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }
        
        // Фильтр по проекту
        if (!empty($filters['project_code'])) {
            $query .= " AND ot.message LIKE ?";
            $params[] = $filters['project_code'] . "%";
            $types .= "s";
        }
        
        // Фильтр по дате в тексте сообщения
        // Поддерживаемые форматы:
        // 1. Проектные: ✅ДД.ММ.ГГГГ, Проект
        // 2. Текущая деятельность: от <b>ДД.ММ.ГГГГ</b>:
        // 3. Отпуск/Больничный: 🏖 ДД.ММ.ГГГГ: или 🏥 ДД.ММ.ГГГГ:
        if (!empty($filters['message_date'])) {
            $dateObj = \DateTime::createFromFormat('Y-m-d', $filters['message_date']);
            if ($dateObj) {
                $searchDate = $dateObj->format('d.m.Y');
                // Ищем дату в любом из форматов (ДД.ММ.ГГГГ может быть после ✅, 🏖, 🏥 или в тегах <b>)
                $query .= " AND ot.message LIKE ?";
                $params[] = "%" . $searchDate . "%";
                $types .= "s";
            }
        }
        
        // Фильтр по диапазону дат в тексте сообщения
        if (!empty($filters['message_date_from']) && !empty($filters['message_date_to'])) {
            $dateFrom = \DateTime::createFromFormat('Y-m-d', $filters['message_date_from']);
            $dateTo = \DateTime::createFromFormat('Y-m-d', $filters['message_date_to']);
            
            if ($dateFrom && $dateTo) {
                // Генерируем условия для каждой даты в диапазоне (макс 62 дня)
                $daysDiff = $dateFrom->diff($dateTo)->days;
                
                if ($daysDiff <= 62) {
                    $dateConditions = [];
                    $current = clone $dateFrom;
                    while ($current <= $dateTo) {
                        $searchDate = $current->format('d.m.Y');
                        $dateConditions[] = "ot.message LIKE ?";
                        $params[] = "%" . $searchDate . "%";
                        $types .= "s";
                        $current->modify('+1 day');
                    }
                    
                    if (!empty($dateConditions)) {
                        $query .= " AND (" . implode(" OR ", $dateConditions) . ")";
                    }
                } else {
                    // Для больших диапазонов используем поиск по dt
                    $query .= " AND DATE(ot.dt + INTERVAL 2 HOUR) >= ? AND DATE(ot.dt + INTERVAL 2 HOUR) <= ?";
                    $params[] = $filters['message_date_from'];
                    $params[] = $filters['message_date_to'];
                    $types .= "ss";
                }
            }
        }
        
        // Фильтр по дате отправки (dt) - для обратной совместимости
        if (!empty($filters['date_from']) && empty($filters['message_date'])) {
            $query .= " AND DATE(ot.dt + INTERVAL 2 HOUR) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        
        // Фильтр по дате отправки до
        if (!empty($filters['date_to']) && empty($filters['message_date']) && empty($filters['message_date_from'])) {
            $query .= " AND DATE(ot.dt + INTERVAL 2 HOUR) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }
        
        // Поиск по тексту
        if (!empty($filters['search'])) {
            $query .= " AND ot.message LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "s";
        }
        
        $query .= " ORDER BY ot.dt DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $this->getConnection()->prepare($query);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $entries = [];
        while ($row = $result->fetch_assoc()) {
            $entries[] = $row;
        }
        
        return $entries;
    }

    /**
     * Получить общее количество записей с фильтрацией
     */
    public function getTabelEntriesCount(array $filters = []): int
    {
        $query = "SELECT COUNT(*) as cnt FROM ozna_tabel ot WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($filters['user_id'])) {
            $query .= " AND ot.user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }
        
        if (!empty($filters['project_code'])) {
            $query .= " AND ot.message LIKE ?";
            $params[] = $filters['project_code'] . "%";
            $types .= "s";
        }
        
        // Фильтр по дате в тексте сообщения (универсальный поиск ДД.ММ.ГГГГ)
        if (!empty($filters['message_date'])) {
            $dateObj = \DateTime::createFromFormat('Y-m-d', $filters['message_date']);
            if ($dateObj) {
                $searchDate = $dateObj->format('d.m.Y');
                $query .= " AND ot.message LIKE ?";
                $params[] = "%" . $searchDate . "%";
                $types .= "s";
            }
        }
        
        // Фильтр по диапазону дат в тексте сообщения
        if (!empty($filters['message_date_from']) && !empty($filters['message_date_to'])) {
            $dateFrom = \DateTime::createFromFormat('Y-m-d', $filters['message_date_from']);
            $dateTo = \DateTime::createFromFormat('Y-m-d', $filters['message_date_to']);
            
            if ($dateFrom && $dateTo) {
                $daysDiff = $dateFrom->diff($dateTo)->days;
                
                if ($daysDiff <= 62) {
                    $dateConditions = [];
                    $current = clone $dateFrom;
                    while ($current <= $dateTo) {
                        $searchDate = $current->format('d.m.Y');
                        $dateConditions[] = "ot.message LIKE ?";
                        $params[] = "%" . $searchDate . "%";
                        $types .= "s";
                        $current->modify('+1 day');
                    }
                    
                    if (!empty($dateConditions)) {
                        $query .= " AND (" . implode(" OR ", $dateConditions) . ")";
                    }
                } else {
                    $query .= " AND DATE(ot.dt + INTERVAL 2 HOUR) >= ? AND DATE(ot.dt + INTERVAL 2 HOUR) <= ?";
                    $params[] = $filters['message_date_from'];
                    $params[] = $filters['message_date_to'];
                    $types .= "ss";
                }
            }
        }
        
        if (!empty($filters['date_from']) && empty($filters['message_date']) && empty($filters['message_date_from'])) {
            $query .= " AND DATE(ot.dt + INTERVAL 2 HOUR) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        
        if (!empty($filters['date_to']) && empty($filters['message_date']) && empty($filters['message_date_from'])) {
            $query .= " AND DATE(ot.dt + INTERVAL 2 HOUR) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND ot.message LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "s";
        }
        
        $stmt = $this->getConnection()->prepare($query);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['cnt'];
    }

    /**
     * Получить запись табеля по ID
     */
    public function getTabelEntryById(int $entryId): ?array
    {
        $query = "SELECT ot.id, ot.user_id, ot.dt + INTERVAL 2 HOUR as dt, ot.message,
                         od.data->>'$.fio' as user_fio
                  FROM ozna_tabel ot
                  LEFT JOIN ozna_data od ON ot.user_id = od.user_id
                  WHERE ot.id = ?";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("i", $entryId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc() ?: null;
    }

    /**
     * Удалить запись табеля
     */
    public function deleteTabelEntry(int $entryId): bool
    {
        $query = "DELETE FROM ozna_tabel WHERE id = ?";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("i", $entryId);
        $success = $stmt->execute();
        
        if ($success && $stmt->affected_rows > 0) {
            error_log("DatabaseService: Tabel entry #{$entryId} deleted");
            return true;
        }
        
        return false;
    }

    /**
     * Обновить запись табеля (изменить проект в сообщении)
     */
    public function updateTabelEntryProject(int $entryId, string $oldProjectCode, string $newProjectCode): bool
    {
        // Сначала получаем текущее сообщение
        $entry = $this->getTabelEntryById($entryId);
        if (!$entry) {
            return false;
        }
        
        // Заменяем код проекта в сообщении
        $newMessage = str_replace($oldProjectCode, $newProjectCode, $entry['message']);
        
        $query = "UPDATE ozna_tabel SET message = ? WHERE id = ?";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("si", $newMessage, $entryId);
        $success = $stmt->execute();
        
        if ($success && $stmt->affected_rows > 0) {
            error_log("DatabaseService: Tabel entry #{$entryId} project updated from '{$oldProjectCode}' to '{$newProjectCode}'");
            return true;
        }
        
        return false;
    }

    /**
     * Обновить запись табеля (изменить дату в сообщении)
     */
    public function updateTabelEntryDate(int $entryId, string $oldDate, string $newDate): bool
    {
        // Сначала получаем текущее сообщение
        $entry = $this->getTabelEntryById($entryId);
        if (!$entry) {
            return false;
        }
        
        // Форматируем даты для поиска и замены (формат ДД.ММ.ГГГГ)
        $oldDateFormatted = date('d.m.Y', strtotime($oldDate));
        $newDateFormatted = date('d.m.Y', strtotime($newDate));
        
        // Заменяем дату в сообщении
        $newMessage = str_replace($oldDateFormatted, $newDateFormatted, $entry['message']);
        
        // Проверяем что замена произошла
        if ($newMessage === $entry['message']) {
            error_log("DatabaseService: updateTabelEntryDate - date not found in message for entry #{$entryId}");
            return false;
        }
        
        $query = "UPDATE ozna_tabel SET message = ? WHERE id = ?";
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param("si", $newMessage, $entryId);
        $success = $stmt->execute();
        
        if ($success && $stmt->affected_rows > 0) {
            error_log("DatabaseService: Tabel entry #{$entryId} date updated from '{$oldDateFormatted}' to '{$newDateFormatted}'");
            return true;
        }
        
        return false;
    }

    /**
     * Получить список уникальных пользователей с записями в табеле
     */
    public function getTabelUsers(): array
    {
        $query = "SELECT DISTINCT ot.user_id, 
                         COALESCE(od.data->>'$.fio', CONCAT('ID: ', ot.user_id)) as fio
                  FROM ozna_tabel ot
                  LEFT JOIN ozna_data od ON ot.user_id = od.user_id
                  ORDER BY fio ASC";
        
        $result = $this->getConnection()->query($query);
        
        if (!$result) {
            error_log("DatabaseService::getTabelUsers - Query error: " . $this->getConnection()->error);
            return [];
        }
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        error_log("DatabaseService::getTabelUsers - Found " . count($users) . " users");
        return $users;
    }

    /**
     * Получить список уникальных проектов из записей табеля
     */
    public function getTabelProjects(): array
    {
        // Извлекаем код проекта из начала сообщения (до первого пробела)
        // Используем простой SUBSTRING_INDEX без REGEXP для совместимости
        $query = "SELECT DISTINCT SUBSTRING_INDEX(message, ' ', 1) as project_code
                  FROM ozna_tabel
                  WHERE message IS NOT NULL AND message != ''
                  ORDER BY project_code ASC";
        
        $result = $this->getConnection()->query($query);
        
        if (!$result) {
            error_log("DatabaseService::getTabelProjects - Query error: " . $this->getConnection()->error);
            return [];
        }
        
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $code = trim($row['project_code']);
            // Фильтруем только валидные коды проектов (минимум 2 символа, без спецсимволов)
            if (mb_strlen($code) >= 2 && !preg_match('/[<>"\']/', $code)) {
                $projects[] = $code;
            }
        }
        
        $projects = array_unique($projects);
        error_log("DatabaseService::getTabelProjects - Found " . count($projects) . " projects");
        return $projects;
    }

    /**
     * Получить список уникальных дат из записей табеля
     */
    public function getTabelDates(): array
    {
        $query = "SELECT DISTINCT DATE(dt + INTERVAL 2 HOUR) as entry_date
                  FROM ozna_tabel
                  ORDER BY entry_date DESC
                  LIMIT 60";
        
        $result = $this->getConnection()->query($query);
        
        if (!$result) {
            error_log("DatabaseService::getTabelDates - Query error: " . $this->getConnection()->error);
            return [];
        }
        
        $dates = [];
        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['entry_date'];
        }
        
        error_log("DatabaseService::getTabelDates - Found " . count($dates) . " dates");
        return $dates;
    }
}