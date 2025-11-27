<?php
// Server-side test for manual input functionality
// This script simulates the exact flow that happens when a user enters Group ID

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== SERVER MANUAL INPUT TEST ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Test 1: Check if files exist and are readable
echo "1. Checking file existence:\n";
$files_to_check = [
    'CustomCommands/Services/ManualInputStateManager.php',
    'CustomCommands/GenericmessageCommand.php',
    'CustomCommands/Handlers/ProjectHandler.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $mod_time = filemtime($file);
        echo "✅ $file - Modified: " . date('Y-m-d H:i:s', $mod_time) . "\n";
    } else {
        echo "❌ $file - NOT FOUND\n";
    }
}

echo "\n2. Testing ManualInputStateManager:\n";
try {
    require_once 'CustomCommands/Services/ManualInputStateManager.php';
    
    // Test setting Group ID state
    $test_user_id = 'test_123';
    $test_project_code = 'TEST_PROJECT';
    
    ManualInputStateManager::setState($test_user_id, 'group', $test_project_code);
    echo "✅ setState for group completed\n";
    
    // Test checking state
    $is_awaiting = ManualInputStateManager::isAwaitingGroupInput($test_user_id);
    echo "✅ isAwaitingGroupInput: " . ($is_awaiting ? 'true' : 'false') . "\n";
    
    $project_code = ManualInputStateManager::getProjectCode($test_user_id, 'group');
    echo "✅ getProjectCode: $project_code\n";
    
    // Clean up
    ManualInputStateManager::clearState($test_user_id, 'group');
    echo "✅ clearState completed\n";
    
} catch (Exception $e) {
    echo "❌ ManualInputStateManager error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing state files directly:\n";
$state_dir = 'temp_states';
if (is_dir($state_dir)) {
    echo "✅ State directory exists\n";
    $files = scandir($state_dir);
    echo "Files in state directory: " . count($files) - 2 . "\n"; // -2 for . and ..
    
    // Show recent state files
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $full_path = $state_dir . '/' . $file;
            $mod_time = filemtime($full_path);
            $content = file_get_contents($full_path);
            echo "- $file (modified: " . date('H:i:s', $mod_time) . ") - Content: " . substr($content, 0, 50) . "\n";
        }
    }
} else {
    echo "❌ State directory does not exist\n";
}

echo "\n4. Simulating Group ID input flow:\n";
try {
    // Simulate the exact flow that should happen
    $sim_user_id = 'sim_user_456';
    $sim_project = 'БКНС125';
    
    echo "Step 1: Setting manual input state for Group ID...\n";
    ManualInputStateManager::setState($sim_user_id, 'group', $sim_project);
    
    echo "Step 2: Checking if user is awaiting Group ID input...\n";
    $awaiting = ManualInputStateManager::isAwaitingGroupInput($sim_user_id);
    echo "Result: " . ($awaiting ? "✅ User is awaiting Group ID input" : "❌ User is NOT awaiting Group ID input") . "\n";
    
    if ($awaiting) {
        echo "Step 3: Getting project code...\n";
        $project = ManualInputStateManager::getProjectCode($sim_user_id, 'group');
        echo "Project code: $project\n";
        
        echo "Step 4: Simulating successful Group ID input...\n";
        echo "This is where GenericmessageCommand should send: ✅ ID группы сохранён\n";
        
        echo "Step 5: Clearing state...\n";
        ManualInputStateManager::clearState($sim_user_id, 'group');
        echo "✅ State cleared\n";
    }
    
} catch (Exception $e) {
    echo "❌ Simulation error: " . $e->getMessage() . "\n";
}

echo "\n5. Testing database connection (if available):\n";
try {
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        echo "✅ Database config loaded\n";
        
        // Try to connect (assuming PDO)
        if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            echo "✅ Database connection successful\n";
            
            // Check if user projects table exists
            $stmt = $pdo->query("SHOW TABLES LIKE '%project%'");
            $tables = $stmt->fetchAll();
            echo "Project-related tables found: " . count($tables) . "\n";
            
        } else {
            echo "⚠️ Database constants not properly defined\n";
        }
    } else {
        echo "⚠️ Database config not found\n";
    }
} catch (Exception $e) {
    echo "⚠️ Database test error: " . $e->getMessage() . "\n";
}

echo "\n6. Final diagnosis:\n";
echo "If this test shows all components working but Group ID confirmation still fails,\n";
echo "the issue is likely in the webhook processing or message handling logic.\n";
echo "Check your Telegram bot webhook logs for any errors during Group ID processing.\n";

echo "\n=== TEST COMPLETE ===\n";
?>