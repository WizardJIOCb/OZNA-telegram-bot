<?php

/**
 * Class Loading Test Script for BorisTgBot-Neuro Refactored Architecture
 * 
 * This script tests if all refactored classes can be loaded properly.
 * Run this script on your production server to verify the refactoring works.
 * 
 * Usage: php class_loading_test.php
 */

// Include the existing DB class that the bot uses
require_once __DIR__ . '/Custome/DB.php';

echo "🔧 Testing BorisTgBot-Neuro Refactored Architecture...\n";
echo "================================================\n\n";

$errors = [];
$success = [];

// Test 1: Include all required service files
echo "1. Testing service file includes...\n";
$serviceFiles = [
    'Services/CallbackHandlerInterface.php',
    'Services/CallbackHandlerFactory.php', 
    'Services/DatabaseService.php',
    'Services/MessageService.php',
    'Services/ProjectSelector.php',
    'Services/TabelEntry.php'
];

foreach ($serviceFiles as $file) {
    $fullPath = __DIR__ . '/CustomCommands/' . $file;
    if (file_exists($fullPath)) {
        require_once $fullPath;
        $success[] = "   ✓ Included: $file";
    } else {
        $errors[] = "   ❌ Missing: $file";
    }
}

// Test 2: Include all handler files
echo "\n2. Testing handler file includes...\n";
$handlerFiles = [
    'Handlers/ProfileHandler.php',
    'Handlers/TabelHandler.php', 
    'Handlers/HelpHandler.php'
];

foreach ($handlerFiles as $file) {
    $fullPath = __DIR__ . '/CustomCommands/' . $file;
    if (file_exists($fullPath)) {
        require_once $fullPath;
        $success[] = "   ✓ Included: $file";
    } else {
        $errors[] = "   ❌ Missing: $file";
    }
}

// Test 3: Try to instantiate service classes
echo "\n3. Testing service class instantiation...\n";
$serviceClasses = [
    'Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\DatabaseService',
    'Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\MessageService', 
    'Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\ProjectSelector',
    'Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\CallbackHandlerFactory'
];

foreach ($serviceClasses as $className) {
    try {
        $shortName = substr($className, strrpos($className, '\\') + 1);
        
        if ($className === 'Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\TabelEntry') {
            // TabelEntry requires DatabaseService and MessageService
            continue; 
        }
        
        $instance = new $className();
        $success[] = "   ✓ Created: $shortName";
    } catch (Exception $e) {
        $errors[] = "   ❌ Failed to create $shortName: " . $e->getMessage();
    } catch (Error $e) {
        $errors[] = "   ❌ Failed to create $shortName: " . $e->getMessage();
    }
}

// Test 4: Test TabelEntry with dependencies
echo "\n4. Testing TabelEntry with dependencies...\n";
try {
    $dbService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $msgService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($dbService, $msgService);
    $success[] = "   ✓ Created: TabelEntry with dependencies";
} catch (Exception $e) {
    $errors[] = "   ❌ Failed to create TabelEntry: " . $e->getMessage();
} catch (Error $e) {
    $errors[] = "   ❌ Failed to create TabelEntry: " . $e->getMessage();
}

// Test 5: Test handler classes
echo "\n5. Testing handler class instantiation...\n";
try {
    $dbService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $msgService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $projSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
    $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($dbService, $msgService);
    
    $profileHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler($dbService, $msgService);
    $success[] = "   ✓ Created: ProfileHandler";
    
    $tabelHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler($dbService, $msgService, $projSelector, $tabelEntry);
    $success[] = "   ✓ Created: TabelHandler";
    
    $helpHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler($msgService);
    $success[] = "   ✓ Created: HelpHandler";
    
} catch (Exception $e) {
    $errors[] = "   ❌ Failed to create handlers: " . $e->getMessage();
} catch (Error $e) {
    $errors[] = "   ❌ Failed to create handlers: " . $e->getMessage();
}

// Test 6: Test complete integration
echo "\n6. Testing complete integration...\n";
try {
    $factory = new \Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory();
    
    $dbService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $msgService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $projSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
    $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($dbService, $msgService);
    
    $factory->registerHandler(new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler($dbService, $msgService, $projSelector, $tabelEntry));
    $factory->registerHandler(new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler($dbService, $msgService));
    $factory->registerHandler(new \Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler($msgService));
    
    $success[] = "   ✓ Complete integration test passed";
    
} catch (Exception $e) {
    $errors[] = "   ❌ Integration test failed: " . $e->getMessage();
} catch (Error $e) {
    $errors[] = "   ❌ Integration test failed: " . $e->getMessage();
}

// Results
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 TEST RESULTS:\n";
echo str_repeat("=", 50) . "\n";

if (!empty($success)) {
    echo "\n✅ SUCCESSES (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo $msg . "\n";
    }
}

if (!empty($errors)) {
    echo "\n❌ ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo $msg . "\n";
    }
    echo "\n🔧 NEXT STEPS:\n";
    echo "1. Check file paths and permissions\n";
    echo "2. Verify PHP version compatibility\n";
    echo "3. Check for missing dependencies\n";
    echo "4. Review error messages above for specific issues\n";
} else {
    echo "\n🎉 ALL TESTS PASSED!\n";
    echo "The refactored architecture is ready to use.\n";
    echo "\n📋 Architecture Summary:\n";
    echo "- Services: 6 classes loaded\n";
    echo "- Handlers: 3 classes loaded\n";
    echo "- Integration: Complete\n";
    echo "- Ready for production!\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 50) . "\n";