<?php
// Debug script to test the PNR callback processing

require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/ProjectSelector.php';
require_once 'CustomCommands/Services/TabelEntry.php';
require_once 'CustomCommands/Services/AdminService.php';
require_once 'CustomCommands/Services/DatabaseService.php';

echo "=== Debug PNR Callback Processing ===\n";

// Mock classes for testing
class MockCallbackQuery {
    public function getMessage() {
        return new class {
            public function getChat() {
                return new class {
                    public function getId() {
                        return 'test_chat_id';
                    }
                };
            }
        };
    }
    
    public function getFrom() {
        return new class {
            public function getId() {
                return 'test_user_id';
            }
        };
    }
    
    public function getData() {
        return 'category_Табель_ПНР';
    }
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

// Test the TabelHandler canHandle method
$callbackData = 'category_Табель_ПНР';
echo "Testing callback data: $callbackData\n";

// Simulate what happens in CallbackHandlerFactory
$category = substr($callbackData, strlen('category_'));
echo "Extracted category: $category\n";

// Test TabelHandler canHandle
function tabelHandlerCanHandle($callbackData) {
    return strpos($callbackData, 'category_Табель') === 0;
}

$canHandle = tabelHandlerCanHandle($callbackData);
echo "TabelHandler canHandle: " . ($canHandle ? 'true' : 'false') . "\n";

// Test the routing logic
function routeTabelCallback($category) {
    echo "Routing category: $category\n";
    
    // Check for commissioning works menu (Пусконаладочные работы)
    if ($category === 'Табель_ПНР') {
        echo "  -> Matched Табель_ПНР\n";
        echo "  -> Would call handleOtherWorkTypes\n";
        return handleOtherWorkTypes('test_chat_id', $category, new MockConversation());
    }
    
    echo "  -> No match found\n";
    return false;
}

function handleOtherWorkTypes($chatId, $category, $conversation) {
    echo "In handleOtherWorkTypes with category: $category\n";
    
    if ($category === 'Табель_ПНР') {
        echo "  -> Matched Табель_ПНР category\n";
        echo "  -> Would call handleCommissioningWorksMenu\n";
        return handleCommissioningWorksMenu($chatId);
    }
    
    echo "  -> Category not matched in handleOtherWorkTypes\n";
    return false;
}

function handleCommissioningWorksMenu($chatId) {
    echo "In handleCommissioningWorksMenu\n";
    echo "  -> Would create commissioning works keyboard\n";
    echo "  -> Would send message with equipment selection menu\n";
    return true;
}

// Test the routing
$result = routeTabelCallback($category);
echo "Routing result: " . ($result ? 'success' : 'failure') . "\n";

echo "\nTest completed!\n";
?>