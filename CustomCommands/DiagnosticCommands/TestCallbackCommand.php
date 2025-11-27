<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Exception;

/**
 * Test Callback Command
 * Usage: /test_callback category_Табель_ИспОбМаст
 */
class TestcallbackCommandFix extends UserCommand
{
    protected $name = 'testcallback';
    protected $description = 'Test specific callback for Master Duties';
    protected $usage = '/testcallback <callback_data>';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $text = trim($message->getText(true));

        // Default callback if none provided
        if (empty($text)) {
            $text = 'category_Табель_ИспОбМаст';
        }

        try {
            // Load diagnostic class
            require_once __DIR__ . '/../../diagnostic_master_duties_telegram.php';
            $diagnostic = new \MasterDutiesDiagnostic();
            
            // Test specific callback
            $report = $diagnostic->testCallback($chat_id, $text);
            
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => $report,
                'parse_mode' => 'Markdown'
            ]);

        } catch (Exception $e) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => "❌ Ошибка тестирования: " . $e->getMessage()
            ]);
        }
    }
}
