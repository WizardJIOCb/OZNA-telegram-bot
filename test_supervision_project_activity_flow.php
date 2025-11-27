<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';
require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/TabelEntryService.php';
require_once 'CustomCommands/Services/ProjectSelector.php';

use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;
use CustomCommands\Services\MessageService;
use CustomCommands\Handlers\TabelHandler;

class TestSupervisionProjectActivityFlow
{
    private $tabelHandler;
    private $messageService;

    public function __construct()
    {
        $this->messageService = new MessageService();
        $this->tabelHandler = new TabelHandler($this->messageService);
    }

    public function testSupervisionProjectActivityFlow()
    {
        echo "=== Testing Supervision Project Activity Flow ===\n";

        // Create test conversation
        $chatId = '12345';
        $userId = 67890;
        $conversation = new Conversation($userId, $chatId);

        // Step 1: Simulate clicking "Проектная деятельность" button from supervision keyboard
        echo "\n1. User clicks 'Проектная деятельность' button in supervision keyboard\n";
        
        $callbackData = 'category_Табель_ШМ_ПроектнаяДеятельность';
        $callbackQuery = $this->createCallbackQuery($chatId, $userId, $callbackData);

        echo "   Callback: {$callbackData}\n";

        // This should trigger the early routing to handleProjectActivityMenu
        try {
            $response = $this->tabelHandler->handle($callbackQuery);
            echo "   ✅ Project activity menu shown successfully\n";
            
            // Check if is_supervision flag was set
            $conversation = new Conversation($userId, $chatId);
            $isSupervision = isset($conversation->notes['is_supervision']) && $conversation->notes['is_supervision'];
            echo "   is_supervision flag: " . ($isSupervision ? 'true' : 'false') . "\n";
            
            if ($isSupervision) {
                echo "   ✅ is_supervision flag correctly set to true\n";
            } else {
                echo "   ❌ ERROR: is_supervision flag not set!\n";
                return false;
            }
            
        } catch (Exception $e) {
            echo "   ❌ ERROR: " . $e->getMessage() . "\n";
            return false;
        }

        // Step 2: Simulate selecting a project activity (e.g., "Подготовка к ШМР")
        echo "\n2. User selects 'Подготовка к ШМР' from project activity menu\n";
        
        $prepCallbackData = 'category_Табель_Подготовка';
        $prepCallbackQuery = $this->createCallbackQuery($chatId, $userId, $prepCallbackData);

        echo "   Callback: {$prepCallbackData}\n";

        try {
            $response = $this->tabelHandler->handle($prepCallbackQuery);
            echo "   ✅ Date selection shown for 'Подготовка к ШМР'\n";
        } catch (Exception $e) {
            echo "   ❌ ERROR: " . $e->getMessage() . "\n";
            return false;
        }

        // Step 3: Simulate date selection
        echo "\n3. User selects today's date\n";
        
        $dateCallbackData = 'category_Табель_ДатаСегодня';
        $dateCallbackQuery = $this->createCallbackQuery($chatId, $userId, $dateCallbackData);

        echo "   Callback: {$dateCallbackData}\n";

        try {
            $response = $this->tabelHandler->handle($dateCallbackQuery);
            echo "   ✅ Project selection shown\n";
        } catch (Exception $e) {
            echo "   ❌ ERROR: " . $e->getMessage() . "\n";
            return false;
        }

        // Step 4: Simulate project selection
        echo "\n4. User selects a project\n";
        
        $projectCallbackData = 'category_Табель_ШифрПроекта_ОИ834';
        $projectCallbackQuery = $this->createCallbackQuery($chatId, $userId, $projectCallbackData);

        echo "   Callback: {$projectCallbackData}\n";

        try {
            $response = $this->tabelHandler->handle($projectCallbackQuery);
            echo "   ✅ Activities menu should be shown\n";
            
            // Check conversation state again
            $conversation = new Conversation($userId, $chatId);
            $isSupervision = isset($conversation->notes['is_supervision']) && $conversation->notes['is_supervision'];
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            
            echo "   Project code: {$projectCode}\n";
            echo "   is_supervision flag: " . ($isSupervision ? 'true' : 'false') . "\n";
            
            if ($isSupervision) {
                echo "   ✅ SUCCESS: Supervision activities menu should be displayed!\n";
                echo "   ✅ The bot should show supervision keyboard with ШМР activities\n";
                return true;
            } else {
                echo "   ❌ ERROR: is_supervision flag lost during flow!\n";
                return false;
            }
            
        } catch (Exception $e) {
            echo "   ❌ ERROR: " . $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    private function createCallbackQuery($chatId, $userId, $data)
    {
        $chat = new Chat(['id' => $chatId, 'type' => 'private']);
        $user = new User(['id' => $userId, 'is_bot' => false, 'first_name' => 'Test']);
        $message = new Message(['message_id' => 1, 'from' => $user, 'date' => time(), 'chat' => $chat, 'text' => 'test'], 'testbot');
        
        return new CallbackQuery([
            'id' => 'test_callback_' . uniqid(),
            'from' => $user,
            'message' => $message,
            'data' => $data
        ]);
    }
}

// Run the test
$test = new TestSupervisionProjectActivityFlow();
$result = $test->testSupervisionProjectActivityFlow();

if ($result) {
    echo "\n🎉 TEST PASSED: Supervision project activity flow works correctly!\n";
    echo "The supervision activities menu should now appear after project selection.\n";
} else {
    echo "\n💥 TEST FAILED: Issue with supervision project activity flow\n";
}