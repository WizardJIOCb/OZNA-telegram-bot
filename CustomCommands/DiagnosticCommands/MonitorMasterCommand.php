<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Exception;

/**
 * Monitor Master Duties Command
 * Usage: /monitor_master
 */
class MonitormasterCommand extends UserCommand
{
    protected $name = 'monitormaster';
    protected $description = 'Start monitoring Master Duties button';
    protected $usage = '/monitormaster';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        try {
            // Load diagnostic class
            require_once __DIR__ . '/../../diagnostic_master_duties_telegram.php';
            $diagnostic = new \MasterDutiesDiagnostic();
            
            // Start monitoring
            $report = $diagnostic->startMonitoring($chat_id);
            
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => $report,
                'parse_mode' => 'Markdown'
            ]);

        } catch (Exception $e) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => "❌ Ошибка мониторинга: " . $e->getMessage()
            ]);
        }
    }
}