<?php
/**
 * Test script to verify control project notifications are working
 */

echo "=== ТЕСТ УВЕДОМЛЕНИЙ ДЛЯ ПРОЕКТОВ НА КОНТРОЛЕ ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
    
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService, $messageService);
    
    echo "1. Проверяем пользователей с проектами на контроле...\n\n";
    
    $allUsers = $databaseService->getAllUsers();
    $usersWithControlProjects = [];
    
    foreach ($allUsers as $user) {
        $userId = $user['user_id'];
        $userData = $user['data'];
        $controlProjects = $userData['control_projects'] ?? [];
        
        if (!empty($controlProjects)) {
            $usersWithControlProjects[] = [
                'user_id' => $userId,
                'fio' => $userData['fio'] ?? 'Не указано',
                'projects' => $controlProjects
            ];
            
            echo "   Пользователь: {$userData['fio']} (ID: {$userId})\n";
            foreach ($controlProjects as $projectCode => $settings) {
                echo "     • Проект: {$projectCode}\n";
                echo "       - Group ID: " . ($settings['group_id'] ?? 'не настроено') . "\n";
                echo "       - Chat ID: " . ($settings['chat_id'] ?? 'не настроено') . "\n";
            }
            echo "\n";
        }
    }
    
    if (empty($usersWithControlProjects)) {
        echo "   ❌ Не найдено пользователей с проектами на контроле!\n";
        echo "   Добавьте проекты через интерфейс бота перед тестированием.\n";
        exit;
    }
    
    echo "2. Имитируем создание отчета для тестирования...\n\n";
    
    // Берем первый проект из списка для тестирования
    $testProject = array_keys($usersWithControlProjects[0]['projects'])[0];
    $testUserId = 999999999; // Фиктивный пользователь-создатель отчета
    $testFio = 'Тестовый Пользователь';
    $testDate = date('Y-m-d');
    $testActivityType = 'Электромонтажные';
    $testChatId = '-1234567890';
    
    echo "   Тестовые данные:\n";
    echo "   • Пользователь: {$testFio} (ID: {$testUserId})\n";
    echo "   • Проект: {$testProject}\n";
    echo "   • Тип активности: {$testActivityType}\n";
    echo "   • Дата: {$testDate}\n\n";
    
    echo "3. Отправляем тестовый отчет через TabelEntry...\n\n";
    
    // ВАЖНО: Это реальная отправка! Уведомления попадут в настроенные группы и чаты
    $success = $tabelEntry->addEntry($testUserId, $testFio, $testDate, $testProject, $testActivityType, $testChatId);
    
    if ($success) {
        echo "   ✅ Отчет успешно создан и отправлен!\n\n";
        
        echo "4. Что должно произойти:\n";
        echo "   ✅ Отчет отправлен в основную группу по функциональному направлению\n";
        echo "   ✅ Копии отправлены всем пользователям, у которых проект {$testProject} на контроле:\n\n";
        
        foreach ($usersWithControlProjects as $controlUser) {
            if (isset($controlUser['projects'][$testProject])) {
                $settings = $controlUser['projects'][$testProject];
                echo "     → Пользователь: {$controlUser['fio']} (ID: {$controlUser['user_id']})\n";
                
                if (!empty($settings['group_id'])) {
                    $threadInfo = (!empty($settings['chat_id']) && $settings['chat_id'] !== 'personal') 
                        ? " (тема: {$settings['chat_id']})" : "";
                    echo "       ✉️ Группа: {$settings['group_id']}{$threadInfo}\n";
                }
                
                if (!empty($settings['chat_id']) && $settings['chat_id'] === 'personal') {
                    echo "       ✉️ Личный чат пользователя\n";
                }
                echo "\n";
            }
        }
        
        echo "5. Проверьте Telegram группы и чаты на наличие сообщений!\n";
        echo "   Сообщение должно содержать информацию о тестовом отчете.\n\n";
        
        echo "🎉 ТЕСТ ЗАВЕРШЕН!\n";
        echo "Если вы получили уведомления в настроенных группах и чатах,\n";
        echo "то система копирования отчетов для проектов на контроле работает корректно!\n";
        
    } else {
        echo "   ❌ Ошибка создания отчета!\n";
        echo "   Проверьте логи для получения подробной информации.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "Стек вызовов:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== КОНЕЦ ТЕСТА ===\n";
?>