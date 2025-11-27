<?php
/**
 * Тест тестового уведомления с новыми настройками
 */

echo "=== ТЕСТ ТЕСТОВОГО УВЕДОМЛЕНИЯ ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    $userId = 230379668;
    $projectCode = 'ОИ834';
    
    echo "1. Получение настроек проекта для пользователя $userId:\n";
    
    $userData = $databaseService->getUserData($userId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    if (isset($controlProjects[$projectCode])) {
        $projectSettings = $controlProjects[$projectCode];
        
        echo "   ✓ Проект $projectCode найден\n";
        echo "   • Group ID: {$projectSettings['group_id']}\n";
        echo "   • Chat ID: {$projectSettings['chat_id']}\n\n";
        
        echo "2. ИМИТАЦИЯ ТЕСТОВОГО УВЕДОМЛЕНИЯ:\n\n";
        
        // Создаём тестовое сообщение
        $testMessage = "🧪 Тестовое уведомление\n\n";
        $testMessage .= "📂 Проект: {$projectCode}\n";
        $testMessage .= "⏰ Время: " . date('d.m.Y H:i:s') . "\n";
        $testMessage .= "👤 Отправитель: Пользователь {$userId}\n\n";
        $testMessage .= "✅ Система уведомлений работает корректно!";
        
        $testsSent = [];
        $errors = [];
        
        // Тест отправки в группу
        echo "3. ТЕСТ ОТПРАВКИ В ГРУППУ:\n";
        if (!empty($projectSettings['group_id'])) {
            $groupId = $projectSettings['group_id'];
            echo "   Отправка в группу: $groupId\n";
            
            try {
                $result = $messageService->sendMessage($groupId, $testMessage);
                
                if (method_exists($result, 'isOk') && $result->isOk()) {
                    echo "   ✅ ГРУППА: Отправлено успешно!\n";
                    $testsSent[] = "группу $groupId";
                } else {
                    $error = method_exists($result, 'getDescription') ? $result->getDescription() : 'Неизвестная ошибка';
                    echo "   ❌ ГРУППА: Ошибка - $error\n";
                    $errors[] = "Группа: $error";
                }
            } catch (Exception $e) {
                echo "   ❌ ГРУППА: Исключение - {$e->getMessage()}\n";
                $errors[] = "Группа: {$e->getMessage()}";
            }
        }
        
        echo "\n4. ТЕСТ ОТПРАВКИ В ЧАТ:\n";
        if (!empty($projectSettings['chat_id'])) {
            $chatId = $projectSettings['chat_id'];
            $targetChatId = ($chatId === 'personal') ? $userId : $chatId;
            
            echo "   Исходный chat_id: $chatId\n";
            echo "   Целевой chat_id: $targetChatId\n";
            
            try {
                $result = $messageService->sendMessage($targetChatId, $testMessage);
                
                if (method_exists($result, 'isOk') && $result->isOk()) {
                    if ($chatId === 'personal') {
                        echo "   ✅ ЧАТ: Отправлено в личный чат!\n";
                        $testsSent[] = "личный чат";
                    } else {
                        echo "   ✅ ЧАТ: Отправлено в чат $targetChatId!\n";
                        $testsSent[] = "чат $targetChatId";
                    }
                } else {
                    $error = method_exists($result, 'getDescription') ? $result->getDescription() : 'Неизвестная ошибка';
                    echo "   ❌ ЧАТ: Ошибка - $error\n";
                    $errors[] = "Чат: $error";
                }
            } catch (Exception $e) {
                echo "   ❌ ЧАТ: Исключение - {$e->getMessage()}\n";
                $errors[] = "Чат: {$e->getMessage()}";
            }
        }
        
        echo "\n5. ИТОГОВЫЙ ОТЧЁТ:\n";
        if (!empty($testsSent)) {
            echo "   ✅ Успешно отправлено в: " . implode(', ', $testsSent) . "\n";
        }
        
        if (!empty($errors)) {
            echo "   ❌ Ошибки отправки:\n";
            foreach ($errors as $error) {
                echo "      • $error\n";
            }
        }
        
        if (empty($testsSent) && empty($errors)) {
            echo "   ⚠️ Не настроены получатели уведомлений\n";
        }
        
        echo "\n6. ЧТО ПРОВЕРИТЬ:\n";
        if (in_array("группу -1002722176147", $testsSent)) {
            echo "   ✓ Проверьте группу -1002722176147 - там должно быть сообщение\n";
        }
        if (in_array("личный чат", $testsSent)) {
            echo "   ✓ Проверьте ваш личный чат с ботом - там должно быть сообщение\n";
        }
        
        if (!empty($errors)) {
            echo "\n7. РЕКОМЕНДАЦИИ ПО ИСПРАВЛЕНИЮ ОШИБОК:\n";
            foreach ($errors as $error) {
                if (strpos($error, 'chat not found') !== false) {
                    echo "   • Чат не найден - проверьте правильность ID\n";
                } elseif (strpos($error, 'bot was blocked') !== false) {
                    echo "   • Бот заблокирован - разблокируйте бота в чате\n";
                } elseif (strpos($error, 'not enough rights') !== false) {
                    echo "   • Недостаточно прав - дайте боту права на отправку сообщений\n";
                }
            }
        }
        
    } else {
        echo "   ❌ Проект $projectCode не найден\n";
    }
    
} catch (Exception $e) {
    echo "❌ Критическая ошибка: " . $e->getMessage() . "\n";
}

echo "\n=== ТЕСТ ЗАВЕРШЁН ===\n";
echo "Теперь проверьте реальную кнопку 'Тестовое уведомление' в боте!\n";
?>