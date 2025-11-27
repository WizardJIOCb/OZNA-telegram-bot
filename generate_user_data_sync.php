<?php
/**
 * Generate user data sync SQL script
 * This creates SQL commands to sync user project data from local to server database
 */

echo "=== GENERATING USER DATA SYNC SQL ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $allUsers = $databaseService->getAllUsers();
    
    $sqlCommands = [];
    $sqlCommands[] = "-- User Data Sync Script Generated: " . date('Y-m-d H:i:s');
    $sqlCommands[] = "-- This script syncs user project data from local to server database";
    $sqlCommands[] = "";
    $sqlCommands[] = "USE tg_bot;";
    $sqlCommands[] = "";
    
    $processedUsers = 0;
    $usersWithProjectData = 0;
    
    foreach ($allUsers as $user) {
        $userId = $user['user_id'];
        $userData = $user['data'];
        $processedUsers++;
        
        // Check if user has project-related data
        $hasProjectData = (isset($userData['report_projects']) && !empty($userData['report_projects'])) ||
                         (isset($userData['control_projects']) && !empty($userData['control_projects']));
        
        if ($hasProjectData) {
            $usersWithProjectData++;
            
            // Escape JSON data for SQL
            $jsonData = json_encode($userData, JSON_UNESCAPED_UNICODE);
            $escapedJsonData = addslashes($jsonData);
            
            $fio = $userData['fio'] ?? 'Unknown';
            
            $sqlCommands[] = "-- User: {$fio} (ID: {$userId})";
            $sqlCommands[] = "INSERT INTO ozna_data (user_id, data) VALUES ({$userId}, '{$escapedJsonData}')";
            $sqlCommands[] = "ON DUPLICATE KEY UPDATE data = '{$escapedJsonData}';";
            $sqlCommands[] = "";
        }
    }
    
    $sqlCommands[] = "-- Sync completed: {$usersWithProjectData} users with project data processed out of {$processedUsers} total users";
    
    // Output SQL commands
    foreach ($sqlCommands as $command) {
        echo $command . "\n";
    }
    
    fprintf(STDERR, "\n-- SQL generation completed successfully\n");
    fprintf(STDERR, "-- Processed {$processedUsers} users, {$usersWithProjectData} with project data\n");
    
} catch (Exception $e) {
    echo "-- ERROR: Failed to generate sync SQL: " . $e->getMessage() . "\n";
    fprintf(STDERR, "-- Sync script generation failed\n");
    exit(1);
}

?>