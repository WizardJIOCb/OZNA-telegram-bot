<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

class AddprojectCommand extends SystemCommand
{
    protected $name = 'addproject';
    protected $description = 'Добавить новый проект (только для администраторов)';
    protected $usage = '/addproject';
    
    private $databaseService;

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();

        // Only superadmin can run this command
        if ($user_id != 230379668 && $user_id != 5261465966) {
            return $this->replyToChat('⛔ Доступ запрещён. Только для администраторов.');
        }

        try {
            $this->databaseService = new DatabaseService();
            $conn = $this->databaseService->getConnection();

            // First, clean up test projects
            $stmt = $conn->prepare("DELETE FROM project WHERE code LIKE ? OR code LIKE ? OR code LIKE ? OR code LIKE ?");
            $testPattern1 = 'ПКПО-%';
            $testPattern2 = 'TEST-%';
            $testPattern3 = 'ШМР-%';
            $testPattern4 = 'ПНР-%';
            $stmt->bind_param('ssss', $testPattern1, $testPattern2, $testPattern3, $testPattern4);
            $stmt->execute();
            $deleted = $stmt->affected_rows;
            
            $response = "✅ <b>Очистка завершена</b>\n\n";
            $response .= "🗑️ Удалено тестовых проектов: {$deleted}\n\n";
            $response .= "➕ <b>Добавление нового проекта:</b>\n\n";
            $response .= "Используйте формат:\n";
            $response .= "<code>/addproject ШИФ|Наименование</code>\n\n";
            $response .= "Пример:\n";
            $response .= "<code>/addproject 2024-001|Новый проект 2024</code>";

            // Check if project details provided
            $text = trim($message->getText(true));
            if (!empty($text)) {
                $parts = explode('|', $text);
                if (count($parts) === 2) {
                    $code = trim($parts[0]);
                    $name = trim($parts[1]);
                    
                    // Insert new project
                    $stmt = $conn->prepare("
                        INSERT INTO project (code, name, is_active, created_at) 
                        VALUES (?, ?, 1, NOW())
                        ON DUPLICATE KEY UPDATE name = ?, is_active = 1
                    ");
                    $activeFlag = 1;
                    $stmt->bind_param('sss', $code, $name, $name);
                    $stmt->execute();
                    
                    $response = "✅ <b>Проект добавлен!</b>\n\n";
                    $response .= "🔹 Шифр: {$code}\n";
                    $response .= "🔹 Наименование: {$name}\n\n";
                    $response .= "Теперь можете открыть карточку проекта в разделе Проекты → Карточка проекта";
                }
            }

            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => $response,
                'parse_mode' => 'HTML'
            ]);

        } catch (\Exception $e) {
            error_log("AddprojectCommand error: " . $e->getMessage());
            return $this->replyToChat("❌ Ошибка: " . $e->getMessage());
        }
    }
}
