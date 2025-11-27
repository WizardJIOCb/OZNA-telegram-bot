<?php
// COMPREHENSIVE MASTER DUTIES BUTTON TEST - PRODUCTION ENVIRONMENT
// This test simulates the EXACT production environment to identify the issue

echo "=== COMPREHENSIVE MASTER DUTIES TEST ===\n";
echo "Production Environment Simulation\n";
echo "=========================================\n\n";

// Include the actual TabelHandler class
require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

echo "Step 1: Create TabelHandler instance\n";
echo "------------------------------------\n";

try {
    $messageService = new \CustomCommands\Services\MessageService();
    $tabelEntry = new \CustomCommands\Services\TabelEntry();
    $tabelHandler = new \CustomCommands\Handlers\TabelHandler($messageService, $tabelEntry);
    echo "✅ TabelHandler created successfully\n";
} catch (Exception $e) {
    echo "❌ Error creating TabelHandler: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nStep 2: Test extractCategoryFromCallback method\n";
echo "-----------------------------------------------\n";

$testCallback = 'cat_ТабИсполнОбяз';
echo "Testing callback: {$testCallback}\n";

// Use reflection to access private method
try {
    $reflection = new ReflectionClass($tabelHandler);
    $extractMethod = $reflection->getMethod('extractCategoryFromCallback');
    $extractMethod->setAccessible(true);
    
    $extractedCategory = $extractMethod->invokeArgs($tabelHandler, [$testCallback]);
    echo "✅ Extracted category: '{$extractedCategory}'\n";
    
    $expectedCategory = 'Табель_ИсполнениеОбязМастера';
    if ($extractedCategory === $expectedCategory) {
        echo "✅ CORRECT: Mapping works correctly\n";
    } else {
        echo "❌ INCORRECT: Expected '{$expectedCategory}', got '{$extractedCategory}'\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing extractCategoryFromCallback: " . $e->getMessage() . "\n";
}

echo "\nStep 3: Test canHandle method\n";
echo "-----------------------------\n";

try {
    $canHandle = $tabelHandler->canHandle($testCallback);
    echo "canHandle('{$testCallback}'): " . ($canHandle ? '✅ TRUE' : '❌ FALSE') . "\n";
} catch (Exception $e) {
    echo "❌ Error testing canHandle: " . $e->getMessage() . "\n";
}

echo "\nStep 4: Test isActivityExecution method\n";
echo "---------------------------------------\n";

try {
    $isActivityExecutionMethod = $reflection->getMethod('isActivityExecution');
    $isActivityExecutionMethod->setAccessible(true);
    
    // Test initial button (should be FALSE)
    $isActivityExecution = $isActivityExecutionMethod->invokeArgs($tabelHandler, [$extractedCategory]);
    echo "isActivityExecution('{$extractedCategory}'): " . ($isActivityExecution ? '❌ TRUE (WRONG!)' : '✅ FALSE (CORRECT)') . "\n";
    
    // Test confirmation button (should be TRUE)
    $confirmationCallback = 'cat_ТабИсполнОбяз_yes';
    $confirmationCategory = $extractMethod->invokeArgs($tabelHandler, [$confirmationCallback]);
    echo "Confirmation category: '{$confirmationCategory}'\n";
    
    $isConfirmationExecution = $isActivityExecutionMethod->invokeArgs($tabelHandler, [$confirmationCategory]);
    echo "isActivityExecution('{$confirmationCategory}'): " . ($isConfirmationExecution ? '✅ TRUE (CORRECT)' : '❌ FALSE (WRONG!)') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error testing isActivityExecution: " . $e->getMessage() . "\n";
}

echo "\nStep 5: Test activityTypes array\n";
echo "--------------------------------\n";

try {
    $activityTypesProperty = $reflection->getProperty('activityTypes');
    $activityTypesProperty->setAccessible(true);
    $activityTypes = $activityTypesProperty->getValue($tabelHandler);
    
    $requiredActivity = 'ИсполнениеОбязМастера';
    $hasActivity = in_array($requiredActivity, $activityTypes);
    echo "'{$requiredActivity}' in activityTypes: " . ($hasActivity ? '✅ TRUE' : '❌ FALSE') . "\n";
    
    if (!$hasActivity) {
        echo "Available activity types:\n";
        foreach ($activityTypes as $i => $type) {
            echo "  [{$i}] {$type}\n";
            if ($i > 10) {
                echo "  ... and " . (count($activityTypes) - 10) . " more\n";
                break;
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing activityTypes: " . $e->getMessage() . "\n";
}

echo "\nStep 6: Simulate complete flow\n";
echo "------------------------------\n";

try {
    // Create mock objects
    $mockUser = new User([
        'id' => 123456,
        'is_bot' => false,
        'first_name' => 'Test',
        'username' => 'testuser'
    ]);
    
    $mockChat = new Chat([
        'id' => 789012,
        'type' => 'private'
    ]);
    
    $mockMessage = new Message([
        'message_id' => 1,
        'from' => $mockUser,
        'date' => time(),
        'chat' => $mockChat,
        'text' => 'Test message'
    ], 'test_bot');
    
    $mockCallbackQuery = new CallbackQuery([
        'id' => 'test_callback_123',
        'from' => $mockUser,
        'message' => $mockMessage,
        'data' => $testCallback
    ], 'test_bot');
    
    echo "✅ Mock objects created\n";
    
    // Test the handle method directly
    echo "Testing handle() method with callback: {$testCallback}\n";
    
    // This might fail due to database/conversation dependencies, but let's try
    try {
        $result = $tabelHandler->handle($mockCallbackQuery);
        echo "✅ handle() method executed successfully\n";
        
        // Check if it's a ServerResponse
        if ($result instanceof \Longman\TelegramBot\Entities\ServerResponse) {
            echo "✅ Returned ServerResponse object\n";
            if ($result->isOk()) {
                echo "✅ Response is OK\n";
            } else {
                echo "❌ Response error: " . $result->getDescription() . "\n";
            }
        } else {
            echo "❌ Unexpected return type: " . gettype($result) . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error in handle() method: " . $e->getMessage() . "\n";
        echo "This might be due to database/conversation dependencies\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating mock objects: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "\nCONCLUSION:\n";
echo "If all tests pass but the button still doesn't work in production,\n";
echo "the issue is likely:\n";
echo "1. Database/conversation state issues\n";
echo "2. Error logging not visible\n";
echo "3. Telegram API communication problems\n";
echo "4. Server-side caching/deployment issues\n";
echo "\nRECOMMENDATION:\n";
echo "Check the server error logs during button click to see exact error messages.\n";
?>