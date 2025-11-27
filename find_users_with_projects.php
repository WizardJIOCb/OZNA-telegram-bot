<?php
/**
 * Поиск всех пользователей с проектами в контроле
 */

echo "=== ПОИСК ВСЕХ ПОЛЬЗОВАТЕЛЕЙ С ПРОЕКТАМИ ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    echo "1. Поиск всех пользователей в базе данных...\n";
    
    // Прямой запрос к базе данных для поиска всех пользователей
    $reflection = new ReflectionClass($databaseService);
    $property = $reflection->getProperty('db');
    $property->setAccessible(true);
    $db = $property->getValue($databaseService);
    
    if ($db) {
        echo "   ✓ Подключение к базе данных активно\n\n";
        
        // Получаем всех пользователей
        $stmt = $db->query("SELECT user_id, data FROM user_data ORDER BY updated_at DESC LIMIT 20");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "2. Найдено пользователей: " . count($users) . "\n\n";
        
        $usersWithProjects = 0;
        
        foreach ($users as $user) {
            $userId = $user['user_id'];
            $data = json_decode($user['data'], true);
            
            if (isset($data['control_projects']) && !empty($data['control_projects'])) {
                $usersWithProjects++;
                echo "3. ПОЛЬЗОВАТЕЛЬ С ПРОЕКТАМИ: $userId\n";
                
                if (isset($data['fio'])) {
                    echo "   ФИО: {$data['fio']}\n";
                }
                
                echo "   Проекты в контроле:\n";
                foreach ($data['control_projects'] as $projectCode => $settings) {
                    echo "   • $projectCode:\n";
                    echo "     - Group ID: " . ($settings['group_id'] ?? 'не настроено') . "\n";
                    echo "     - Chat ID: " . ($settings['chat_id'] ?? 'не настроено') . "\n";
                    if (isset($settings['created_at'])) {
                        echo "     - Создан: {$settings['created_at']}\n";
                    }
                }
                echo "\n";
                
                // Если это ваш ID - проверяем дополнительно
                if ($userId == 230379668) {
                    echo "   🎯 ЭТО ВАШ ID! Проекты найдены.\n\n";
                }
            }
        }
        
        if ($usersWithProjects == 0) {
            echo "3. ❌ НИ У ОДНОГО ПОЛЬЗОВАТЕЛЯ НЕТ ПРОЕКТОВ В КОНТРОЛЕ\n";
            echo "   Это означает, что проекты ещё не добавлены или\n";
            echo "   сохраняются в другом месте.\n\n";
        } else {
            echo "3. ✓ Найдено пользователей с проектами: $usersWithProjects\n\n";
        }
        
        // Поиск недавно обновлённых пользователей
        echo "4. НЕДАВНО АКТИВНЫЕ ПОЛЬЗОВАТЕЛИ:\n";
        $stmt = $db->query("SELECT user_id, data, updated_at FROM user_data ORDER BY updated_at DESC LIMIT 5");
        $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($recentUsers as $user) {
            $data = json_decode($user['data'], true);
            $fio = $data['fio'] ?? 'Неизвестно';
            echo "   • ID: {$user['user_id']}, ФИО: $fio, Обновлён: {$user['updated_at']}\n";
        }
        
    } else {
        echo "   ❌ Не удалось получить подключение к базе данных\n";
    }
    
    echo "\n5. ЧТО ДЕЛАТЬ ДАЛЬШЕ:\n";
    echo "   Если ваш ID (230379668) не найден среди пользователей с проектами:\n";
    echo "   1. Добавьте проект через интерфейс бота\n";
    echo "   2. Настройте ID группы и чата\n";
    echo "   3. Повторите диагностику\n\n";
    
    echo "   Если другой пользователь имеет проекты:\n";
    echo "   1. Возможно, вы использовали другой аккаунт для настройки\n";
    echo "   2. Скопируйте настройки на ваш текущий ID\n";
    echo "   3. Или используйте тот аккаунт, где есть настройки\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== ПОИСК ЗАВЕРШЁН ===\n";
?>