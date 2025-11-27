<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

/**
 * Manual Input State Manager
 * 
 * Manages conversation state for manual ID input without relying on database conversations
 * Uses file-based storage as fallback when database conversations are not available
 */
class ManualInputStateManager 
{
    private string $stateDir;
    
    public function __construct() 
    {
        // Use temp_states directory within the workspace
        $this->stateDir = __DIR__ . '/../../temp_states';
        
        // Create directory if it doesn't exist
        if (!is_dir($this->stateDir)) {
            mkdir($this->stateDir, 0755, true);
        }
        
        error_log("ManualInputStateManager: Using state directory: {$this->stateDir}");
    }
    
    /**
     * Set manual input state for user
     */
    public function setState(int $userId, string $inputType, string $projectCode): bool
    {
        $stateFile = $this->getStateFile($userId);
        
        $state = [
            'type' => $inputType, // 'group_id' or 'chat_id'
            'project' => $projectCode,
            'timestamp' => time(),
            'expires_at' => time() + 3600 // 1 hour expiry
        ];
        
        $jsonData = json_encode($state, JSON_UNESCAPED_UNICODE);
        $result = file_put_contents($stateFile, $jsonData, LOCK_EX);
        
        error_log("ManualInputStateManager: Set state for user {$userId}: {$inputType} -> {$projectCode}");
        
        return $result !== false;
    }
    
    /**
     * Get manual input state for user
     */
    public function getState(int $userId): ?array
    {
        $stateFile = $this->getStateFile($userId);
        
        if (!file_exists($stateFile)) {
            return null;
        }
        
        $jsonData = file_get_contents($stateFile);
        $state = json_decode($jsonData, true);
        
        if (!$state || !isset($state['expires_at'])) {
            return null;
        }
        
        // Check if state has expired
        if (time() > $state['expires_at']) {
            $this->clearState($userId);
            return null;
        }
        
        return $state;
    }
    
    /**
     * Clear manual input state for user
     */
    public function clearState(int $userId): bool
    {
        $stateFile = $this->getStateFile($userId);
        
        if (file_exists($stateFile)) {
            $result = unlink($stateFile);
            error_log("ManualInputStateManager: Cleared state for user {$userId}");
            return $result;
        }
        
        return true;
    }
    
    /**
     * Check if user is awaiting manual group ID input
     */
    public function isAwaitingGroupInput(int $userId): bool
    {
        $state = $this->getState($userId);
        return $state !== null && $state['type'] === 'group_id';
    }
    
    /**
     * Check if user is awaiting manual chat ID input
     */
    public function isAwaitingChatInput(int $userId): bool
    {
        $state = $this->getState($userId);
        return $state !== null && $state['type'] === 'chat_id';
    }
    
    /**
     * Get project code for current manual input
     */
    public function getProjectCode(int $userId): ?string
    {
        $state = $this->getState($userId);
        return $state['project'] ?? null;
    }
    
    /**
     * Get state file path for user
     */
    private function getStateFile(int $userId): string
    {
        return $this->stateDir . "/user_{$userId}.json";
    }
    
    /**
     * Clean up expired states (can be called periodically)
     */
    public function cleanupExpiredStates(): int
    {
        $cleaned = 0;
        $files = glob($this->stateDir . '/user_*.json');
        
        foreach ($files as $file) {
            $jsonData = file_get_contents($file);
            $state = json_decode($jsonData, true);
            
            if (!$state || !isset($state['expires_at']) || time() > $state['expires_at']) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}