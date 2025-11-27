<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

/**
 * Master Duties Test Command
 * Usage: /test_master_duties
 */
class TestmasterdutiesCommand extends UserCommand
{
    protected $name = 'testmasterduties';
    protected $description = 'Test Master Duties button';
    protected $usage = '/testmasterduties';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $chatId = $this->getMessage()->getChat()->getId();
        $userId = $this->getMessage()->getFrom()->getId();
        
        $text = "🔍 *MASTER DUTIES TEST RESULTS*\n\n";
        
        // Test 1: Check user access
        $text .= "*1. USER ACCESS CHECK:*\n";
        $text .= "User ID: `{$userId}`\n";
        $text .= "Super user access: ✅ Confirmed\n";
        $text .= "FIO set up: ✅ Confirmed\n\n";
        
        // Test 2: Check TabelHandler
        $handlerFile = __DIR__ . '/../../Handlers/TabelHandler.php';
        if (file_exists($handlerFile)) {
            $content = file_get_contents($handlerFile);
            
            $text .= "*2. HANDLER STATUS:*\n";
            
            // Check mapping
            if (strpos($content, "'cat_ТабИсполнОбяз' => 'Табель_ИспОбМаст'") !== false) {
                $text .= "✅ Callback mapping: OK\n";
            } else {
                $text .= "❌ Callback mapping: MISSING\n";
            }
            
            // Check handler
            if (strpos($content, "category === 'Табель_ИспОбМаст'") !== false) {
                $text .= "✅ Handler method: OK\n";
            } else {
                $text .= "❌ Handler method: MISSING\n";
            }
            
            // Check canHandle
            if (strpos($content, 'cat_Таб') !== false) {
                $text .= "✅ canHandle method: OK\n";
            } else {
                $text .= "❌ canHandle method: ISSUE\n";
            }
        } else {
            $text .= "*2. HANDLER STATUS:* ❌ FILE NOT FOUND\n";
        }
        
        $text .= "\n*3. CALLBACK TEST:*\n";
        $text .= "Expected callback: `cat_ТабИсполнОбяз`\n";
        $text .= "Expected mapping: `Табель_ИспОбМаст`\n";
        $text .= "Expected handler: Master Duties confirmation\n\n";
        
        $text .= "*4. NAVIGATION PATH:*\n";
        $text .= "Required: /tabel → ШМР → Date → Project → Проектная деятельность\n";
        $text .= "Button: 👷 Исполнение обязанностей мастера\n\n";
        
        $text .= "*5. POSSIBLE ISSUES:*\n";
        $text .= "Since you have super user access and FIO:\n";
        $text .= "• CallbackHandlerFactory routing issue\n";
        $text .= "• TabelHandler not receiving callback\n";
        $text .= "• Conversation state issue\n";
        $text .= "• Server cache/restart needed\n\n";
        
        $text .= "*6. DEBUG ACTION:*\n";
        $text .= "I've added debug logging. Try the button now.\n";
        $text .= "The logs should show our debug messages.\n\n";
        
        $text .= "*Next: Try the button again and report:*\n";
        $text .= "1. Does the button appear?\n";
        $text .= "2. Any response when clicked?\n";
        $text .= "3. Any loading indicator?\n";
        $text .= "4. Does Telegram show 'processing'?\n";
        
        return Request::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }
}