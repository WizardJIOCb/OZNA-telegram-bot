<?php
/**
 * Диагностика проблемы с отправкой в чат
 */

echo "=== ДИАГНОСТИКА ПРОБЛЕМЫ С ОТПРАВКОЙ В ЧАТ ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    $userId = 230379668;
    echo "1. Проверка настроек пользователя $userId:\n";
    
    $userData = $databaseService->getUserData($userId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    if (empty($controlProjects)) {
        echo "   ❌ У пользователя нет проектов в контроле\n";
        echo "   Сначала добавьте проект через бота\n";
        exit;
    }
    
    echo "   ✓ Найдено проектов: " . count($controlProjects) . "\n\n";
    
    foreach ($controlProjects as $projectCode => $settings) {
        echo "2. ДЕТАЛЬНАЯ ПРОВЕРКА ПРОЕКТА '$projectCode':\n";
        echo "   • Group ID: " . ($settings['group_id'] ?? 'НЕ НАСТРОЕНО') . "\n";
        echo "   • Chat ID: " . ($settings['chat_id'] ?? 'НЕ НАСТРОЕНО') . "\n";
        echo "   • Создан: " . ($settings['created_at'] ?? 'неизвестно') . "\n";
        echo "   • Обновлён: " . ($settings['updated_at'] ?? 'неизвестно') . "\n\n";
        
        // Если есть настройки чата - тестируем
        if (isset($settings['chat_id'])) {
            $chatId = $settings['chat_id'];
            echo "3. ТЕСТИРОВАНИЕ ОТПРАВКИ В ЧАТ '$chatId':\n";
            
            // Определяем целевой чат
            $targetChatId = ($chatId === 'personal') ? $userId : $chatId;
            echo "   • Исходный chat_id: $chatId\n";
            echo "   • Целевой chat_id: $targetChatId\n";
            
            if ($chatId === 'personal') {
                echo "   • Тип: Личный чат пользователя\n";
            } else {
                echo "   • Тип: Конкретный чат\n";
                
                // Проверяем формат ID
                if (strpos($chatId, '-') === 0) {
                    echo "   ✓ Формат ID корректный (начинается с -)\n";
                } else if (is_numeric($chatId) && $chatId > 0) {
                    echo "   ✓ Формат ID корректный (положительное число)\n";
                } else {
                    echo "   ⚠️ Формат ID может быть некорректным\n";
                }
            }
            
            // Пробуем отправить тестовое сообщение
            echo "\n4. ПОПЫТКА ОТПРАВКИ ТЕСТОВОГО СООБЩЕНИЯ:\n";
            $testMessage = "🔧 Диагностический тест " . date('H:i:s');
            
            try {
                echo "   Отправка в chat_id: $targetChatId\n";
                $result = $messageService->sendMessage($targetChatId, $testMessage);
                
                if (method_exists($result, 'isOk')) {
                    if ($result->isOk()) {
                        echo "   ✅ УСПЕХ: Сообщение отправлено успешно!\n";
                        echo "   Проверьте чат $targetChatId - сообщение должно быть там\n";
                    } else {
                        echo "   ❌ ОШИБКА API: " . $result->getDescription() . "\n";
                        echo "   Код ошибки: " . $result->getErrorCode() . "\n";
                        
                        // Анализ типичных ошибок
                        $description = $result->getDescription();
                        if (strpos($description, 'chat not found') !== false) {
                            echo "   💡 ПРИЧИНА: Чат не найден. Проверьте правильность ID\n";
                        } elseif (strpos($description, 'bot was blocked') !== false) {
                            echo "   💡 ПРИЧИНА: Бот заблокирован в этом чате\n";
                        } elseif (strpos($description, 'not enough rights') !== false) {
                            echo "   💡 ПРИЧИНА: У бота нет прав на отправку сообщений\n";
                        } elseif (strpos($description, 'user not found') !== false) {
                            echo "   💡 ПРИЧИНА: Пользователь не найден\n";
                        }
                    }
                } else {
                    echo "   ❌ ОШИБКА: Неожиданный тип результата\n";
                    echo "   Тип результата: " . gettype($result) . "\n";
                    if (is_object($result)) {
                        echo "   Класс результата: " . get_class($result) . "\n";
                    }
                }
                
            } catch (Exception $e) {
                echo "   ❌ ИСКЛЮЧЕНИЕ: " . $e->getMessage() . "\n";
                echo "   Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
            }
            
        } else {
            echo "3. ⚠️ ЧАТ НЕ НАСТРОЕН\n";
            echo "   Настройте chat_id через интерфейс бота\n";
        }
        
        echo "\n" . str_repeat("=", 60) . "\n\n";
    }
    
    echo "5. РЕКОМЕНДАЦИИ ПО ИСПРАВЛЕНИЮ:\n";
    echo "   1. Убедитесь, что бот добавлен в целевой чат\n";
    echo "   2. Убедитесь, что у бота есть права на отправку сообщений\n";
    echo "   3. Проверьте правильность ID чата\n";
    echo "   4. Для групповых чатов: ID должен начинаться с -\n";
    echo "   5. Для личных чатов: используйте 'personal' или числовой ID\n";
    echo "   6. Сначала напишите боту в личку, если используете личный чат\n";
    
} catch (Exception $e) {
    echo "❌ Критическая ошибка: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>