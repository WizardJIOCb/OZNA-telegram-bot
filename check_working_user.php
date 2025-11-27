<?php
/**
 * Проверка пользователя из диагностического файла
 */

echo "=== ПРОВЕРКА ПОЛЬЗОВАТЕЛЯ ИЗ ДИАГНОСТИКИ ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    // Из предыдущих диагностических файлов мы видели пользователя 4225504
    $testUserId = 4225504;
    
    echo "1. Проверка пользователя $testUserId (из предыдущих тестов):\n";
    
    $userData = $databaseService->getUserData($testUserId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    if (!empty($controlProjects)) {
        echo "   ✓ У этого пользователя ЕСТЬ проекты в контроле!\n\n";
        
        foreach ($controlProjects as $projectCode => $settings) {
            echo "2. ПРОЕКТ '$projectCode' (РАБОЧИЙ ПРИМЕР):\n";
            echo "   • Group ID: " . ($settings['group_id'] ?? 'не настроено') . "\n";
            echo "   • Chat ID: " . ($settings['chat_id'] ?? 'не настроено') . "\n";
            
            if (isset($settings['chat_id'])) {
                echo "\n3. ТЕСТИРОВАНИЕ ОТПРАВКИ В ЧАТ:\n";
                $chatId = $settings['chat_id'];
                $targetChatId = ($chatId === 'personal') ? $testUserId : $chatId;
                
                echo "   • Исходный chat_id: $chatId\n";
                echo "   • Целевой chat_id: $targetChatId\n";
                
                // Тестируем отправку
                $testMessage = "🔧 Тест отправки в чат " . date('H:i:s');
                
                try {
                    $result = $messageService->sendMessage($targetChatId, $testMessage);
                    
                    if (method_exists($result, 'isOk')) {
                        if ($result->isOk()) {
                            echo "   ✅ ОТПРАВКА УСПЕШНА!\n";
                        } else {
                            echo "   ❌ ОШИБКА: " . $result->getDescription() . "\n";
                            echo "   Код: " . $result->getErrorCode() . "\n";
                        }
                    }
                } catch (Exception $e) {
                    echo "   ❌ ИСКЛЮЧЕНИЕ: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "\n4. СРАВНЕНИЕ С ВАШИМ СЛУЧАЕМ:\n";
        echo "   Рабочий пользователь: $testUserId\n";
        echo "   Ваш пользователь: 230379668\n";
        echo "   У рабочего пользователя есть настройки, у вас - нет\n\n";
        
        echo "5. РЕШЕНИЕ - СКОПИРОВАТЬ НАСТРОЙКИ:\n";
        echo "   Можем скопировать рабочие настройки на ваш ID\n";
        echo "   Или вы можете добавить проект через интерфейс бота\n";
        
        // Предложение скопировать настройки
        echo "\n6. ХОТИТЕ СКОПИРОВАТЬ НАСТРОЙКИ? (введите 'да' для подтверждения)\n";
        
    } else {
        echo "   ❌ У этого пользователя тоже нет проектов\n";
        echo "   Проверим вашего пользователя ещё раз...\n\n";
        
        $yourUserData = $databaseService->getUserData(230379668);
        echo "2. Ваши данные:\n";
        print_r($yourUserData);
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";
?>