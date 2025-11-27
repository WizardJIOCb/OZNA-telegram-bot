<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

/**
 * Export Security Service
 * Handles export key management, CSV password protection, and audit logging
 */
class ExportSecurityService
{
    private DatabaseService $databaseService;
    
    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }
    
    /**
     * Check if user has export key set
     */
    public function hasExportKey(int $userId): bool
    {
        $exportKey = $this->databaseService->getUserExportKey($userId);
        return $exportKey !== null && !empty($exportKey);
    }
    
    /**
     * Get user's export key
     */
    public function getUserExportKey(int $userId): ?string
    {
        return $this->databaseService->getUserExportKey($userId);
    }
    
    /**
     * Set user's export key
     */
    public function setExportKey(int $userId, string $exportKey): bool
    {
        // Validate key: minimum 4 characters
        if (strlen($exportKey) < 4) {
            return false;
        }
        
        return $this->databaseService->setUserExportKey($userId, $exportKey);
    }
    
    /**
     * Verify user's export key
     */
    public function verifyExportKey(int $userId, string $providedKey): bool
    {
        return $this->databaseService->verifyUserExportKey($userId, $providedKey);
    }
    
    /**
     * Password-protect a CSV file using zip with password
     * Returns the path to the password-protected zip file
     */
    public function protectCsvWithPassword(string $csvFilePath, string $password): ?string
    {
        if (!file_exists($csvFilePath)) {
            error_log("ExportSecurityService: CSV file not found: {$csvFilePath}");
            return null;
        }
        
        // Create zip file
        $zipPath = str_replace('.csv', '.zip', $csvFilePath);
        $zip = new \ZipArchive();
        
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            error_log("ExportSecurityService: Failed to create zip file: {$zipPath}");
            return null;
        }
        
        // Add CSV to zip
        $csvFileName = basename($csvFilePath);
        $zip->addFile($csvFilePath, $csvFileName);
        
        // Set password
        $zip->setPassword($password);
        
        // Encrypt the file
        $zip->setEncryptionName($csvFileName, \ZipArchive::EM_AES_256);
        
        $zip->close();
        
        // Remove original CSV file
        unlink($csvFilePath);
        
        error_log("ExportSecurityService: Created password-protected zip: {$zipPath}");
        
        return $zipPath;
    }
    
    /**
     * Log export operation
     */
    public function logExport(
        int $userId,
        string $exportType,
        string $exportScope,
        int $recordsCount,
        string $fileName,
        ?string $projectCode = null,
        ?string $periodStart = null,
        ?string $periodEnd = null,
        ?string $statusFilter = null
    ): bool {
        // Get user FIO
        $userData = $this->databaseService->getUserData($userId);
        $userFio = $userData['fio'] ?? 'Unknown';
        
        return $this->databaseService->logExport(
            $userId,
            $userFio,
            $exportType,
            $exportScope,
            $recordsCount,
            $fileName,
            $projectCode,
            $periodStart,
            $periodEnd,
            $statusFilter
        );
    }
    
    /**
     * Get user's export history
     */
    public function getUserExportHistory(int $userId, int $limit = 20): array
    {
        return $this->databaseService->getUserExportLogs($userId, $limit);
    }
    
    /**
     * Get all export logs (admin only)
     */
    public function getAllExportLogs(int $limit = 100, int $offset = 0): array
    {
        return $this->databaseService->getAllExportLogs($limit, $offset);
    }
    
    /**
     * Get export statistics (admin only)
     */
    public function getExportStats(): array
    {
        return $this->databaseService->getExportStats();
    }
    
    /**
     * Format export history for display
     */
    public function formatExportHistory(array $logs): string
    {
        if (empty($logs)) {
            return "📋 <b>История выгрузок</b>\n\nУ вас пока нет выгрузок.";
        }
        
        $text = "📋 <b>История выгрузок</b>\n\n";
        
        foreach ($logs as $log) {
            $date = date('d.m.Y H:i', strtotime($log['exported_at']));
            $type = $log['export_type'] === 'notes' ? '📌 Заметки' : '📊 Отчёт';
            
            $text .= "🕐 {$date}\n";
            $text .= "{$type} - {$log['export_scope']}\n";
            
            if ($log['project_code']) {
                $text .= "📁 Проект: {$log['project_code']}\n";
            }
            
            $text .= "📝 Записей: {$log['records_count']}\n";
            $text .= "━━━━━━━━━━━━━━━\n\n";
        }
        
        return $text;
    }
    
    /**
     * Format export history with user information (for admin view)
     */
    public function formatExportHistoryWithUsers(array $logs): string
    {
        if (empty($logs)) {
            return "📋 <b>История выгрузок</b>\n\nПока нет выгрузок.";
        }
        
        $text = "📋 <b>История выгрузок</b>\n\n";
        
        foreach ($logs as $log) {
            $date = date('d.m.Y H:i', strtotime($log['exported_at']));
            $type = $log['export_type'] === 'notes' ? '📌 Заметки' : '📊 Отчёт';
            
            // Use user_fio from the log itself (it's stored at export time)
            $userFio = $log['user_fio'] ?? 'Неизвестно';
            
            $text .= "🕐 {$date}\n";
            $text .= "👤 {$userFio}\n";
            $text .= "{$type} - {$log['export_scope']}\n";
            
            if (!empty($log['project_code'])) {
                $text .= "📁 Проект: {$log['project_code']}\n";
            }
            
            $text .= "📝 Записей: {$log['records_count']}\n";
            $text .= "━━━━━━━━━━━━━━━\n\n";
        }
        
        return $text;
    }
    
    /**
     * Generate secure random export key suggestion
     */
    public function generateSecureKey(int $length = 8): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $key = '';
        $max = strlen($characters) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $key .= $characters[random_int(0, $max)];
        }
        
        return $key;
    }
    
    /**
     * Check if user can export (based on access level)
     */
    public function canUserExport(int $userId): bool
    {
        $accessLevel = $this->databaseService->getUserAccessLevel($userId);
        
        // Only users with access level >= SUPERVISOR (2) can export
        return $accessLevel >= DatabaseService::ACCESS_LEVEL_SUPERVISOR;
    }
    
    /**
     * Get export permission message for user
     */
    public function getExportPermissionMessage(int $userId): ?string
    {
        if (!$this->canUserExport($userId)) {
            return "⚠️ <b>Доступ запрещён</b>\n\nВыгрузка данных доступна только Супервайзерам, Администраторам и Суперадминистраторам.\n\nОбратитесь к администратору для получения доступа.";
        }
        
        return null;
    }
}
