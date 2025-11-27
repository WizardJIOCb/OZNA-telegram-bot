<?php
/**
 * Script to migrate existing access requests from JSON file to database
 */

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

try {
    echo "Migrating access requests from JSON file to database...\n";
    
    // Path to the access requests JSON file
    $jsonFile = __DIR__ . '/data/access_requests.json';
    
    // Check if the file exists
    if (!file_exists($jsonFile)) {
        echo "Access requests JSON file not found. Nothing to migrate.\n";
        exit(0);
    }
    
    // Read the JSON file
    $jsonData = file_get_contents($jsonFile);
    if ($jsonData === false) {
        echo "Failed to read access requests JSON file.\n";
        exit(1);
    }
    
    // Decode the JSON data
    $accessRequests = json_decode($jsonData, true);
    if (!is_array($accessRequests)) {
        echo "Invalid JSON data in access requests file.\n";
        exit(1);
    }
    
    echo "Found " . count($accessRequests) . " access requests to migrate.\n";
    
    // Create database service
    $dbService = new DatabaseService();
    
    // Counter for migrated requests
    $migratedCount = 0;
    $skippedCount = 0;
    
    // Migrate each access request
    foreach ($accessRequests as $request) {
        // Skip if required fields are missing
        if (!isset($request['user_id']) || !isset($request['user_name']) || !isset($request['chat_id']) || !isset($request['timestamp'])) {
            echo "Skipping request with missing required fields.\n";
            $skippedCount++;
            continue;
        }
        
        // Add the request to the database
        $result = $dbService->addAccessRequest([
            'user_id' => $request['user_id'],
            'user_name' => $request['user_name'] ?? '',
            'user_telegram_name' => $request['user_telegram_name'] ?? '',
            'user_fio' => $request['user_fio'] ?? '',
            'chat_id' => $request['chat_id'],
            'timestamp' => $request['timestamp']
        ]);
        
        if ($result) {
            $migratedCount++;
        } else {
            echo "Failed to migrate request for user ID: " . $request['user_id'] . "\n";
            $skippedCount++;
        }
    }
    
    echo "Migration completed.\n";
    echo "Migrated: $migratedCount requests\n";
    echo "Skipped: $skippedCount requests\n";
    
    // Optionally, backup the original file and remove it
    $backupFile = $jsonFile . '.backup';
    if (copy($jsonFile, $backupFile)) {
        echo "Original JSON file backed up to: $backupFile\n";
        // Uncomment the next line if you want to remove the original file after migration
        // unlink($jsonFile);
        // echo "Original JSON file removed.\n";
    } else {
        echo "Warning: Failed to backup the original JSON file.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}