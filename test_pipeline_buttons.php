<?php

// Focused test for the specific supervision works buttons
echo "=== Focused Test for Pipeline Installation Buttons ===\n\n";

// Include the actual TabelHandler file to test the exact methods
require_once 'c:\Users\user\Documents\projects\CustomCommands\Handlers\TabelHandler.php';

// Mock the necessary dependencies
class MockAdminService {
    public function getUserAccessLevel($userId) { return 2; } // Regular user
    public function isSuperAdmin($userId) { return false; }
}

class MockDatabaseService {
    public function hasRecentTabelEntry($userId, $isSuperAdmin) { return false; }
}

class MockMessageService {
    public function sendMessage($chatId, $text) { 
        echo "  📤 Sending message: " . substr($text, 0, 100) . "...\n";
        return true; 
    }
    
    public function createConfirmationKeyboard($callback) {
        echo "  🎯 Creating confirmation keyboard for: $callback\n";
        return [];
    }
}

class MockTabelEntry {
    public function getConfirmationMessage($activityType, $projectCode) {
        echo "  ✅ Getting confirmation message for activity: '$activityType', project: '$projectCode'\n";
        return "Test confirmation message for $activityType";
    }
}

class MockProjectSelector {
    public function isDateSelection($category) { return false; }
    public function isProjectSelection($category) { return false; }
}

class MockCallbackQuery {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getMessage() {
        return new MockMessage();
    }
    
    public function getFrom() {
        return new MockUser();
    }
}

class MockMessage {
    public function getChat() {
        return new MockChat();
    }
}

class MockChat {
    public function getId() {
        return 12345;
    }
}

class MockUser {
    public function getId() {
        return 67890;
    }
}

class MockConversation {
    public $notes = ['project_code' => 'TEST_PROJECT'];
    
    public function update() {
        // Mock update
    }
}

// Test the specific problematic buttons
$testButtons = [
    'cat_ШМР_МонтТруб_РабПоГр',  // "Работы по графику" 
    'cat_ШМР_МонтТруб_ПодтВех'   // "Подтвердить достижение вехи"
];

try {
    // Create a mock TabelHandler instance
    $handler = new \TabelHandler(
        new MockAdminService(),
        new MockDatabaseService(), 
        new MockMessageService(),
        new MockTabelEntry(),
        new MockProjectSelector()
    );
    
    foreach ($testButtons as $buttonCallback) {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "Testing button: '$buttonCallback'\n";
        echo str_repeat("=", 60) . "\n";
        
        // Test canHandle
        $canHandle = $handler->canHandle(new MockCallbackQuery($buttonCallback));
        echo "1. canHandle result: " . ($canHandle ? '✅ YES' : '❌ NO') . "\n";
        
        if ($canHandle) {
            // Test extractCategoryFromCallback using reflection
            $reflection = new ReflectionClass($handler);
            $extractMethod = $reflection->getMethod('extractCategoryFromCallback');
            $extractMethod->setAccessible(true);
            
            $extractedCategory = $extractMethod->invoke($handler, $buttonCallback);
            echo "2. Extracted category: '$extractedCategory'\n";
            
            // Test isActivityConfirmation
            $isConfirmationMethod = $reflection->getMethod('isActivityConfirmation');
            $isConfirmationMethod->setAccessible(true);
            
            $isConfirmation = $isConfirmationMethod->invoke($handler, $extractedCategory);
            echo "3. isActivityConfirmation result: " . ($isConfirmation ? '✅ YES' : '❌ NO') . "\n";
            
            if ($isConfirmation) {
                echo "4. 🎯 This button should show confirmation dialog!\n";
                
                // Try to call the handler
                echo "5. Calling handler...\n";
                try {
                    $mockCallbackQuery = new MockCallbackQuery($buttonCallback);
                    $mockConversation = new MockConversation();
                    
                    $result = $handler->handle($mockCallbackQuery, $extractedCategory, $mockConversation);
                    echo "6. ✅ Handler executed successfully\n";
                } catch (Exception $e) {
                    echo "6. ❌ Handler error: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Test error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Test completed.\n";
?>