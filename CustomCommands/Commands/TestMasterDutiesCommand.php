<?php

namespace CustomCommands\Commands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use CustomCommands\Services\MessageService;
use CustomCommands\Handlers\TabelHandler;
use CustomCommands\Services\DatabaseService;
use CustomCommands\Services\TabelEntry;
use CustomCommands\Services\ProjectSelector;
use CustomCommands\Services\AdminService;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;

class TestMasterDutiesCommand extends UserCommand
{
    protected $name = 'testmaster';
    protected $description = 'Test Master Duties button functionality';
    protected $usage = '/testmaster';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();

        $text = "🧪 MASTER DUTIES BUTTON TEST\n";
        $text .= "============================\n\n";

        try {
            // Create services
            $databaseService = new DatabaseService();
            $messageService = new MessageService();
            $projectSelector = new ProjectSelector($databaseService);
            $tabelEntry = new TabelEntry($databaseService, $messageService);
            $adminService = new AdminService($databaseService);
            
            // Create TabelHandler
            $tabelHandler = new TabelHandler(
                $databaseService,
                $messageService, 
                $projectSelector,
                $tabelEntry,
                $adminService
            );

            $text .= "✅ Services initialized successfully\n\n";

            // Test 1: Check if button callback can be handled
            $buttonCallback = 'cat_ТабИсполнОбяз';
            $canHandle = $tabelHandler->canHandle($buttonCallback);
            $text .= "1. TabelHandler.canHandle('{$buttonCallback}'): " . ($canHandle ? "✅ TRUE" : "❌ FALSE") . "\n";

            if ($canHandle) {
                // Test 2: Create mock conversation with supervision context
                $conversation = new Conversation($userId, $chatId, 'tabel');
                $conversation->notes = [
                    'project_code' => 'TEST_PROJECT',
                    'tabel_data' => date('d.m.Y'),
                    'is_supervision' => true,
                    'department' => 'ШМР'
                ];
                $conversation->update();
                
                $text .= "2. ✅ Mock conversation created with supervision context\n";

                // Test 3: Create mock callback query
                $user = new User(['id' => $userId, 'is_bot' => false, 'first_name' => 'Test']);
                $chat = new Chat(['id' => $chatId, 'type' => 'private']);
                $mockMessage = new Message(['message_id' => 1, 'date' => time(), 'chat' => $chat, 'from' => $user], 'TestBot');
                
                $callbackQuery = new CallbackQuery([
                    'id' => 'test_' . time(),
                    'from' => $user,
                    'message' => $mockMessage,
                    'data' => $buttonCallback
                ]);

                $text .= "3. ✅ Mock CallbackQuery created\n";

                // Test 4: Execute the handler
                $text .= "\n4. 🚀 EXECUTING HANDLER...\n";
                $text .= "   Callback: '{$buttonCallback}'\n";
                
                // This will show us exactly what happens
                $response = $tabelHandler->handle($callbackQuery, '', $conversation);
                
                if ($response) {
                    $text .= "   ✅ Handler executed successfully!\n";
                    $text .= "   Response type: " . get_class($response) . "\n";
                    
                    if (method_exists($response, 'isOk')) {
                        $text .= "   Response OK: " . ($response->isOk() ? 'YES' : 'NO') . "\n";
                    }
                } else {
                    $text .= "   ❌ Handler returned null\n";
                }
            }

            $text .= "\n5. 📋 CHECK ERROR LOGS for detailed execution trace\n";
            $text .= "   Look for these debug messages:\n";
            $text .= "   - 🔥 MASTER DUTIES CATEGORY DETECTED\n";
            $text .= "   - 🎯 MASTER DUTIES CHECK\n";
            $text .= "   - TabelHandler: routing to handleOtherWorkTypes\n";

            // Test 5: Show current button configuration
            $text .= "\n6. 🎛️ CURRENT BUTTON CONFIGURATION:\n";
            $supervisionKeyboard = $messageService->createSupervisionProjectActivityKeyboard();
            
            $foundMasterDuties = false;
            foreach ($supervisionKeyboard as $row) {
                foreach ($row as $button) {
                    if (isset($button['text']) && strpos($button['text'], 'мастера') !== false) {
                        $foundMasterDuties = true;
                        $text .= "   Button text: '{$button['text']}'\n";
                        $text .= "   Callback: '{$button['callback_data']}'\n";
                        break 2;
                    }
                }
            }
            
            if (!$foundMasterDuties) {
                $text .= "   ❌ Master Duties button NOT FOUND in keyboard\n";
            }

        } catch (\Exception $e) {
            $text .= "\n❌ ERROR: " . $e->getMessage() . "\n";
            $text .= "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }

        $text .= "\n" . str_repeat("=", 40) . "\n";
        $text .= "TEST COMPLETED\n";
        $text .= "Check bot behavior and error logs for details.";

        return $this->replyToChat($text);
    }
}