<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Exception;

/**
 * Check Recent Logs Command
 * Usage: /check_logs
 */
class ChecklogsCommand extends UserCommand
{
    protected $name = 'checklogs';
    protected $description = 'Check recent bot logs for Master Duties';
    protected $usage = '/checklogs';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        try {
            $report = "📋 **ПРОВЕРКА ЛОГОВ КНОПКИ 'ИСПОЛНЕНИЕ ОБЯЗАННОСТЕЙ МАСТЕРА'**\n\n";
            
            // Check error log
            $errorLog = $this->checkErrorLog();
            $report .= "**🔴 ERROR LOG:**\n";
            $report .= $errorLog . "\n\n";
            
            // Check telegram bot error log
            $telegramLog = $this->checkTelegramLog();
            $report .= "**📱 TELEGRAM BOT LOG:**\n";
            $report .= $telegramLog . "\n\n";
            
            // Check webhook log
            $webhookLog = $this->checkWebhookLog();
            $report .= "**🌐 WEBHOOK LOG:**\n";
            $report .= $webhookLog . "\n\n";
            
            $report .= "**🔍 ЧТО ИСКАТЬ:**\n";
            $report .= "• `🎯 MASTER DUTIES CHECK:`\n";
            $report .= "• `🎯 MASTER DUTIES: ✅ HANDLER TRIGGERED!`\n";
            $report .= "• `TabelHandler: handling master duties execution`\n";
            $report .= "• Ошибки с `category_Табель_ИспОбМаст`\n";
            
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => $report,
                'parse_mode' => 'Markdown'
            ]);

        } catch (Exception $e) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => "❌ Ошибка проверки логов: " . $e->getMessage()
            ]);
        }
    }
    
    private function checkErrorLog(): string
    {
        $logFiles = ['error.log', 'php_errors.log', 'telegram_bot_error.log'];
        $masterDutiesLines = [];
        
        foreach ($logFiles as $logFile) {
            if (file_exists($logFile)) {
                $lines = $this->getRecentLogLines($logFile, 50);
                foreach ($lines as $line) {
                    if (stripos($line, 'master duties') !== false || 
                        stripos($line, 'ИспОбМаст') !== false ||
                        stripos($line, 'category_Табель_ИспОбМаст') !== false) {
                        $masterDutiesLines[] = "📄 {$logFile}: " . trim($line);
                    }
                }
            }
        }
        
        if (empty($masterDutiesLines)) {
            return "✅ Нет ошибок связанных с Master Duties";
        }
        
        return "```\n" . implode("\n", array_slice($masterDutiesLines, -10)) . "\n```";
    }
    
    private function checkTelegramLog(): string
    {
        if (file_exists('telegram_bot_error.log')) {
            $lines = $this->getRecentLogLines('telegram_bot_error.log', 20);
            $relevantLines = [];
            
            foreach ($lines as $line) {
                if (stripos($line, 'callback') !== false || 
                    stripos($line, 'button') !== false ||
                    stripos($line, 'ИспОбМаст') !== false) {
                    $relevantLines[] = trim($line);
                }
            }
            
            if (empty($relevantLines)) {
                return "✅ Нет ошибок в Telegram логе";
            }
            
            return "```\n" . implode("\n", array_slice($relevantLines, -5)) . "\n```";
        }
        
        return "⚠️ telegram_bot_error.log не найден";
    }
    
    private function checkWebhookLog(): string
    {
        $webhookFiles = ['webhook_error.log', 'webhook_input.log', 'hook_error.log'];
        $relevantLines = [];
        
        foreach ($webhookFiles as $logFile) {
            if (file_exists($logFile)) {
                $lines = $this->getRecentLogLines($logFile, 20);
                foreach ($lines as $line) {
                    if (stripos($line, 'ИспОбМаст') !== false ||
                        stripos($line, 'category_Табель_') !== false) {
                        $relevantLines[] = "📄 {$logFile}: " . trim($line);
                    }
                }
            }
        }
        
        if (empty($relevantLines)) {
            return "✅ Нет ошибок в webhook логах";
        }
        
        return "```\n" . implode("\n", array_slice($relevantLines, -5)) . "\n```";
    }
    
    private function getRecentLogLines(string $filename, int $count = 20): array
    {
        if (!file_exists($filename)) {
            return [];
        }
        
        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }
        
        return array_slice($lines, -$count);
    }
}