<?php
/**
 * Simple database connectivity test
 */

echo "=== TESTING DATABASE CONNECTIVITY ===\n\n";

try {
    echo "1. Loading DatabaseService...\n";
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    
    echo "2. Creating DatabaseService instance...\n";
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    echo "   DatabaseService created successfully\n";
    
    echo "3. Testing getUserData...\n";
    $userId = 230379668;
    $userData = $databaseService->getUserData($userId);
    
    if (is_array($userData)) {
        echo "   ✓ getUserData returned array\n";
        echo "   Keys: " . implode(', ', array_keys($userData)) . "\n";
        
        // Check if control_projects exists
        if (isset($userData['control_projects'])) {
            echo "   ✓ control_projects exists\n";
            echo "   Current projects: " . count($userData['control_projects']) . "\n";
        } else {
            echo "   ✓ control_projects doesn't exist (will be created)\n";
        }
        
    } else {
        echo "   ✗ getUserData failed or returned non-array: " . gettype($userData) . "\n";
    }
    
    echo "\n4. Testing updateUserData...\n";
    
    // Make a simple test update
    $testData = $userData ?? [];
    $testData['last_test'] = date('Y-m-d H:i:s');
    
    $updateResult = $databaseService->updateUserData($userId, $testData);
    echo "   Update result: " . ($updateResult ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    if ($updateResult) {
        echo "   ✓ Database is working correctly\n";
    } else {
        echo "   ✗ Database update failed\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "✗ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DATABASE TEST COMPLETE ===\n";
?>