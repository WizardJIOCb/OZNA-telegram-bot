<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Exception;

/**
 * Master Duties Diagnostic Command
 * Usage: /diag_master_duties
 */
class DiagmasterdutiesCommand extends UserCommand
{
    protected $name = 'diagmasterduties';
    protected $description = 'Diagnostic for Master Duties button';
    protected $usage = '/diagmasterduties';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();

        try {
            // Load diagnostic class
            require_once __DIR__ . '/../../diagnostic_master_duties_telegram.php';
            $diagnostic = new \MasterDutiesDiagnostic();
            
            // Run diagnostic
            $report = $diagnostic->runDiagnostic($chat_id, $user_id);
            
            // Send report
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => $report,
                'parse_mode' => 'Markdown'
            ]);

        } catch (Exception $e) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => "❌ Ошибка диагностики: " . $e->getMessage()
            ]);
        }
    }
}