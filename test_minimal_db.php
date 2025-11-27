<?php
/**
 * Minimal test to identify the exact issue
 */

echo "=== MINIMAL DATABASE UPDATE TEST ===\n\n";

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    
    $userId = 230379668;
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    echo "1. Getting user data...\n";
    $userData = $databaseService->getUserData($userId);
    echo "   Result: " . (is_array($userData) ? "SUCCESS" : "FAILED") . "\n";
    
    if (is_array($userData)) {
        echo "2. Checking control_projects...\n";
        $controlProjects = $userData['control_projects'] ?? [];
        echo "   Current projects: " . count($controlProjects) . "\n";
        
        foreach ($controlProjects as $code => $settings) {
            echo "   Project {$code}: group_id = " . ($settings['group_id'] ?? 'не настроена') . "\n";
        }
        
        echo "3. Testing simple update...\n";
        $userData['test_timestamp'] = date('Y-m-d H:i:s');
        
        echo "   Calling updateUserData...\n";
        $result = $databaseService->updateUserData($userId, $userData);
        echo "   Update result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
        
        if ($result) {
            echo "   ✓ Database updates are working\n";
        } else {
            echo "   ✗ Database update failed\n";
        }
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>