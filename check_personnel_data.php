<?php
/**
 * Check Personnel Data - Debug Script
 * This script checks what functional_direction values exist in the database
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== PERSONNEL DATA CHECK ===\n\n";

try {
    $databaseService = new DatabaseService();
    
    echo "1. Getting all users from database...\n";
    $allUsers = $databaseService->getAllUsers();
    echo "   Total users in database: " . count($allUsers) . "\n\n";
    
    echo "2. Analyzing functional_direction values...\n";
    $fnCounts = [];
    $usersWithFn = 0;
    $usersWithoutFn = 0;
    
    foreach ($allUsers as $user) {
        $userData = $user['data'] ?? [];
        $fn = $userData['functional_direction'] ?? null;
        
        if (empty($fn) || $fn === 'Не указано') {
            $usersWithoutFn++;
        } else {
            $usersWithFn++;
            if (!isset($fnCounts[$fn])) {
                $fnCounts[$fn] = 0;
            }
            $fnCounts[$fn]++;
        }
    }
    
    echo "   Users WITH functional_direction: {$usersWithFn}\n";
    echo "   Users WITHOUT functional_direction: {$usersWithoutFn}\n\n";
    
    echo "3. Functional Direction Distribution:\n";
    foreach ($fnCounts as $fn => $count) {
        echo "   '{$fn}': {$count} users\n";
    }
    
    echo "\n4. Sample users from each functional direction:\n\n";
    
    // Get sample users for each FN
    foreach (array_keys($fnCounts) as $fn) {
        echo "   === {$fn} ===\n";
        $sampleCount = 0;
        foreach ($allUsers as $user) {
            if ($sampleCount >= 3) break; // Show max 3 samples per FN
            
            $userData = $user['data'] ?? [];
            $userFn = $userData['functional_direction'] ?? '';
            
            if ($userFn === $fn) {
                $fio = $userData['fio'] ?? 'Без ФИО';
                echo "     User ID: {$user['user_id']}, FIO: {$fio}\n";
                $sampleCount++;
            }
        }
        echo "\n";
    }
    
    echo "5. Checking exact string matching:\n";
    $testValues = [
        'Мобильная бригада',
        'Шеф-монтаж',
        'Пусконаладка',
        'Отдел ШМР',
        'Отдел ПНР'
    ];
    
    foreach ($testValues as $testValue) {
        $matchCount = 0;
        foreach ($allUsers as $user) {
            $userData = $user['data'] ?? [];
            $userFn = $userData['functional_direction'] ?? '';
            if ($userFn === $testValue) {
                $matchCount++;
            }
        }
        echo "   '{$testValue}': {$matchCount} matches\n";
    }
    
    echo "\n=== CHECK COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
