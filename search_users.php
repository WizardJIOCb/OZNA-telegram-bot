<?php
/**
 * Search users across all departments
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== SEARCHING USERS ACROSS ALL DEPARTMENTS ===\n\n";

try {
    $databaseService = new DatabaseService();
    
    $searchNames = [
        'Гайнутдинов',
        'Шангин',
        'Мамаев',
        'Ахунов'
    ];
    
    $allUsers = $databaseService->getAllUsers();
    
    foreach ($searchNames as $searchName) {
        echo "=== Searching for: {$searchName} ===\n";
        
        $found = false;
        foreach ($allUsers as $user) {
            $userData = $user['data'] ?? [];
            $fio = $userData['fio'] ?? '';
            
            if (stripos($fio, $searchName) !== false) {
                $found = true;
                $userId = $user['user_id'];
                $fn = $userData['functional_direction'] ?? 'Не указано';
                $accessLevel = $userData['access_level'] ?? 0;
                
                echo "  ✓ Found: {$fio}\n";
                echo "    User ID: {$userId}\n";
                echo "    Functional Direction: {$fn}\n";
                echo "    Access Level: {$accessLevel}\n\n";
            }
        }
        
        if (!$found) {
            echo "  ✗ NOT FOUND in any department\n";
            echo "    Trying partial matches...\n";
            
            // Try to find similar names
            foreach ($allUsers as $user) {
                $userData = $user['data'] ?? [];
                $fio = $userData['fio'] ?? '';
                
                if (!empty($fio) && (
                    stripos($fio, substr($searchName, 0, 5)) !== false ||
                    levenshtein(mb_strtolower($searchName), mb_strtolower($fio)) < 5
                )) {
                    $userId = $user['user_id'];
                    $fn = $userData['functional_direction'] ?? 'Не указано';
                    echo "    Similar: {$fio} (ID: {$userId}, FN: {$fn})\n";
                }
            }
        }
        
        echo "\n";
    }
    
    echo "=== SEARCH COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
