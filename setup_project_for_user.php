<?php
/**
 * Настройка проекта для вашего пользователя
 */

echo "=== НАСТРОЙКА ПРОЕКТА ДЛЯ ВАШЕГО ID ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    $yourUserId = 230379668;
    
    echo "1. Создание проекта ОИ834 для пользователя $yourUserId:\n\n";
    
    // Получаем текущие данные пользователя
    $userData = $databaseService->getUserData($yourUserId);
    
    // Создаём настройки проекта
    $projectSettings = [
        'ОИ834' => [
            'group_id' => '-1002722176147',  // Ваша рабочая группа
            'chat_id' => 'personal',         // Сначала личный чат (всегда работает)
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'notification_types' => ['daily_reports', 'critical_issues'],
            'notification_mode' => 'standard'
        ]
    ];
    
    // Добавляем проекты в данные пользователя
    $userData['control_projects'] = $projectSettings;
    
    echo "2. НАСТРОЙКИ ПРОЕКТА:\n";
    echo "   • Проект: ОИ834\n";
    echo "   • Group ID: -1002722176147 (ваша рабочая группа)\n";
    echo "   • Chat ID: personal (ваш личный чат)\n";
    echo "   • Типы уведомлений: daily_reports, critical_issues\n";
    echo "   • Режим: standard\n\n";
    
    echo "3. СОХРАНЕНИЕ НАСТРОЕК...\n";
    
    // Сохраняем обновлённые данные
    $result = $databaseService->updateUserData($yourUserId, $userData);
    
    if ($result) {
        echo "   ✅ НАСТРОЙКИ СОХРАНЕНЫ УСПЕШНО!\n\n";
        
        echo "4. ПРОВЕРКА СОХРАНЕНИЯ:\n";
        $updatedData = $databaseService->getUserData($yourUserId);
        $savedProjects = $updatedData['control_projects'] ?? [];
        
        if (!empty($savedProjects)) {
            echo "   ✓ Проект успешно сохранён в базе данных\n";
            foreach ($savedProjects as $code => $settings) {
                echo "   • $code: группа={$settings['group_id']}, чат={$settings['chat_id']}\n";
            }
        } else {
            echo "   ❌ Проект не сохранился\n";
        }
        
    } else {
        echo "   ❌ ОШИБКА СОХРАНЕНИЯ\n";
    }
    
    echo "\n5. ТЕПЕРЬ МОЖЕТЕ ТЕСТИРОВАТЬ:\n";
    echo "   1. Откройте бота в Telegram\n";
    echo "   2. Перейдите в 'Проекты на контроле'\n";
    echo "   3. Найдите проект ОИ834\n";
    echo "   4. Нажмите 'Тестовое уведомление'\n";
    echo "   5. Проверьте:\n";
    echo "      - Группу -1002722176147 (должно прийти)\n";
    echo "      - Ваш личный чат с ботом (должно прийти)\n\n";
    
    echo "6. ЕСЛИ ХОТИТЕ ИЗМЕНИТЬ CHAT_ID:\n";
    echo "   • Используйте интерфейс бота для настройки другого чата\n";
    echo "   • Или измените на конкретный ID чата, где бот имеет права\n";
    echo "   • Убедитесь, что бот добавлен в целевой чат\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== НАСТРОЙКА ЗАВЕРШЕНА ===\n";
echo "Теперь у вас есть рабочий проект с настройками!\n";
?>