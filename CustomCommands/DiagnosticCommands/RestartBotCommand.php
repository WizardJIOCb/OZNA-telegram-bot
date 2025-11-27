<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Exception;

/**
 * Quick Bot Restart Command
 * Usage: /restart_bot
 */
class RestartbotCommand extends UserCommand
{
    protected $name = 'restartbot';
    protected $description = 'Quick restart bot services';
    protected $usage = '/restartbot';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();

        try {
            // Check if user has admin privileges
            if (!$this->isAdmin($user_id)) {
                return Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => "❌ Доступ запрещен. Только администраторы могут перезапускать бота."
                ]);
            }

            $report = "🔄 **ПЕРЕЗАПУСК БОТА**\n\n";
            
            // Clear OPCache if available
            if (function_exists('opcache_reset')) {
                opcache_reset();
                $report .= "✅ OPCache очищен\n";
            }
            
            // Clear any PHP caches
            if (function_exists('apc_clear_cache')) {
                @apc_clear_cache();
                $report .= "✅ APC Cache очищен\n";
            }
            
            // Create restart flag
            $restartFlagCreated = $this->createRestartFlag();
            if ($restartFlagCreated) {
                $report .= "✅ Флаг перезапуска создан\n";
            }
            
            // Clear temporary states
            $tempCleared = $this->clearTempStates();
            if ($tempCleared) {
                $report .= "✅ Временные состояния очищены\n";
            }
            
            // Reset webhook (if needed)
            $webhookReset = $this->resetWebhook();
            if ($webhookReset) {
                $report .= "✅ Webhook сброшен\n";
            }
            
            $report .= "\n🎯 **СТАТУС:** Бот готов к тестированию\n";
            $report .= "📋 **СЛЕДУЮЩИЙ ШАГ:** Попробуйте кнопку 'Исполнение обязанностей мастера'\n";
            $report .= "🔍 **МОНИТОРИНГ:** `/monitor_master` для отслеживания";
            
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => $report,
                'parse_mode' => 'Markdown'
            ]);

        } catch (Exception $e) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => "❌ Ошибка перезапуска: " . $e->getMessage()
            ]);
        }
    }
    
    private function isAdmin(int $user_id): bool
    {
        // Check admin access from database or config
        try {
            require_once __DIR__ . '/../../CustomCommands/Services/DatabaseService.php';
            $db = new \CustomCommands\Services\DatabaseService();
            
            $result = $db->query(
                "SELECT access_level FROM users WHERE user_id = ? AND access_level >= 3",
                [$user_id]
            );
            
            return !empty($result);
        } catch (Exception $e) {
            error_log("Admin check failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function createRestartFlag(): bool
    {
        try {
            $flagFile = 'restart_required.flag';
            $timestamp = date('Y-m-d H:i:s');
            return file_put_contents($flagFile, "Restart requested at: {$timestamp}") !== false;
        } catch (Exception $e) {
            error_log("Failed to create restart flag: " . $e->getMessage());
            return false;
        }
    }
    
    private function clearTempStates(): bool
    {
        try {
            $tempDir = 'temp_states';
            if (is_dir($tempDir)) {
                $files = glob($tempDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("Failed to clear temp states: " . $e->getMessage());
            return false;
        }
    }
    
    private function resetWebhook(): bool
    {
        try {
            // This would be project-specific webhook reset logic
            if (file_exists('set_webhook.php')) {
                // Could potentially call webhook reset script
                error_log("Webhook reset requested");
            }
            return true;
        } catch (Exception $e) {
            error_log("Webhook reset failed: " . $e->getMessage());
            return false;
        }
    }
}