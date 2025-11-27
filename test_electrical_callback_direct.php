<?php
// Direct test of electrical equipment callback processing

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "=== Direct Electrical Equipment Callback Test ===\n\n";

// Include necessary files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\CallbackqueryCommand;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Telegram;

// Test the exact callback data that should be sent by the electrical equipment button
$callbackData = 'cat_ТабЭлОб';

echo "Testing callback data: {$callbackData}\n";
echo "Length: " . strlen($callbackData) . " bytes\n";
echo "Hex: " . bin2hex($callbackData) . "\n\n";

// Create a mock update with callback query
class MockCallbackQuery extends CallbackQuery {
    private $data;
    
    public function __construct($data) {
        // Create a minimal callback query structure
        $data_array = [
            'id' => '123456789',
            'from' => [
                'id' => 987654321,
                'first_name' => 'TestUser',
                'username' => 'testuser',
                'is_bot' => false
            ],
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => 987654321,
                    'first_name' => 'TestUser',
                    'username' => 'testuser',
                    'is_bot' => false
                ],
                'chat' => [
                    'id' => 987654321,
                    'first_name' => 'TestUser',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'text' => 'Test message'
            ],
            'chat_instance' => '123456789',
            'data' => $data
        ];
        
        parent::__construct($data_array);
        $this->data = $data;
    }
    
    public function getData() {
        return $this->data;
    }
}

class MockUpdate extends Update {
    private $callbackQuery;
    
    public function __construct($callbackData) {
        $this->callbackQuery = new MockCallbackQuery($callbackData);
        
        // Create a minimal update structure
        $data = [
            'update_id' => 123456789,
            'callback_query' => [
                'id' => '123456789',
                'from' => [
                    'id' => 987654321,
                    'first_name' => 'TestUser',
                    'username' => 'testuser',
                    'is_bot' => false
                ],
                'message' => [
                    'message_id' => 1,
                    'from' => [
                        'id' => 987654321,
                        'first_name' => 'TestUser',
                        'username' => 'testuser',
                        'is_bot' => false
                    ],
                    'chat' => [
                        'id' => 987654321,
                        'first_name' => 'TestUser',
                        'username' => 'testuser',
                        'type' => 'private'
                    ],
                    'date' => time(),
                    'text' => 'Test message'
                ],
                'chat_instance' => '123456789',
                'data' => $callbackData
            ]
        ];
        
        parent::__construct($data);
    }
    
    public function getCallbackQuery() {
        return $this->callbackQuery;
    }
}

// Create the update and callback query
$update = new MockUpdate($callbackData);
$callbackQuery = $update->getCallbackQuery();

echo "Created mock update and callback query\n";
echo "Callback query data: " . $callbackQuery->getData() . "\n\n";

// Test the CallbackqueryCommand directly
try {
    echo "Creating CallbackqueryCommand instance...\n";
    
    // Mock the Telegram object
    class MockTelegram {
        public function getCommandsList() {
            return [];
        }
    }
    
    $telegram = new MockTelegram();
    
    // Create the command
    $command = new CallbackqueryCommand($telegram);
    
    echo "Setting update on command...\n";
    // Set the update on the command
    $reflection = new ReflectionClass($command);
    $property = $reflection->getProperty('update');
    $property->setAccessible(true);
    $property->setValue($command, $update);
    
    echo "Executing command...\n";
    // Execute the command
    $result = $command->execute();
    
    echo "Command executed successfully!\n";
    echo "Result: " . (is_object($result) ? get_class($result) : gettype($result)) . "\n";
    
    if (is_object($result) && method_exists($result, 'isOk')) {
        echo "Result isOk: " . ($result->isOk() ? 'YES' : 'NO') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error executing command: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Error executing command: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test completed ===\n";