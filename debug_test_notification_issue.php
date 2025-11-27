<?php
/**
 * Диагностика проблемы с тестовыми уведомлениями в чат
 */

echo "=== ДИАГНОСТИКА ПРОБЛЕМЫ С ТЕСТОВЫМИ УВЕДОМЛЕНИЯМИ ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    $userId = 230379668;
    echo "1. Проверка настроек пользователя $userId:\n";
    
    $userData = $databaseService->getUserData($userId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    if (empty($controlProjects)) {
        echo "   ❌ У пользователя нет проектов в контроле\n";
        exit;
    }
    
    echo "   ✓ Найдено проектов в контроле: " . count($controlProjects) . "\n\n";
    
    foreach ($controlProjects as $projectCode => $settings) {
        echo "2. Настройки проекта '$projectCode':\n";
        echo "   • Group ID: " . ($settings['group_id'] ?? 'НЕ НАСТРОЕНО') . "\n";
        echo "   • Chat ID: " . ($settings['chat_id'] ?? 'НЕ НАСТРОЕНО') . "\n";
        
        if (isset($settings['group_id'])) {
            echo "   ✓ Группа настроена: {$settings['group_id']}\n";
        }
        
        if (isset($settings['chat_id'])) {
            $chatId = $settings['chat_id'];
            echo "   ✓ Чат настроен: $chatId\n";
            
            // Определим тип чата
            if ($chatId === 'personal') {
                echo "   📱 Тип чата: Личный чат (будет отправлено в чат пользователя $userId)\n";
            } else if (is_numeric($chatId) || (is_string($chatId) && strpos($chatId, '-') === 0)) {
                echo "   💬 Тип чата: Определённый чат ID: $chatId\n";
            } else {
                echo "   ⚠️ Неизвестный тип чата: $chatId\n";
            }
        } else {
            echo "   ❌ Чат НЕ настроен\n";
        }
        
        echo "\n3. Симуляция логики отправки тестового сообщения:\n";
        
        // Группа
        if (!empty($settings['group_id'])) {
            echo "   📤 ГРУППА: Отправка в {$settings['group_id']}\n";
            echo "      ✓ Сообщение будет отправлено в группу\n";
        } else {
            echo "   ⏭️ ГРУППА: Пропущено (не настроено)\n";
        }
        
        // Чат
        if (!empty($settings['chat_id'])) {
            $targetChatId = ($settings['chat_id'] === 'personal') ? $userId : $settings['chat_id'];
            echo "   📤 ЧАТ: Отправка в $targetChatId\n";
            
            if ($settings['chat_id'] === 'personal') {
                echo "      ✓ Сообщение будет отправлено в личный чат пользователя\n";
            } else {
                echo "      ✓ Сообщение будет отправлено в указанный чат\n";
            }
        } else {
            echo "   ⏭️ ЧАТ: Пропущено (не настроено)\n";
        }
        
        echo "\n4. Возможные причины неполучения в чате:\n";
        if (isset($settings['chat_id'])) {
            $targetChatId = ($settings['chat_id'] === 'personal') ? $userId : $settings['chat_id'];
            echo "   • Проверьте, добавлен ли бот в чат $targetChatId\n";
            echo "   • Проверьте, есть ли у бота права на отправку сообщений\n";
            echo "   • Проверьте правильность ID чата\n";
            
            if ($settings['chat_id'] !== 'personal') {
                echo "   • Для групповых чатов ID должен начинаться с '-'\n";
                echo "   • Для приватных чатов используется числовой ID\n";
            }
        } else {
            echo "   • Чат не настроен - настройте ID чата в настройках проекта\n";
        }
    }
    
    echo "\n5. Рекомендации по исправлению:\n";
    echo "   1. Убедитесь, что бот добавлен в целевой чат\n";
    echo "   2. Проверьте права бота в чате\n";
    echo "   3. Проверьте правильность ID чата\n";
    echo "   4. Попробуйте сначала отправить тестовое сообщение в личный чат (personal)\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>