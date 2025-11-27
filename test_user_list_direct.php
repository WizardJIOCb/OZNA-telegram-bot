<?php
/**
 * Прямой тест получения списка пользователей
 * Этот скрипт проверяет работу метода getAllUsersWithAccess
 */

echo "=== ТЕСТ ПОЛУЧЕНИЯ СПИСКА ПОЛЬЗОВАТЕЛЕЙ ===\n\n";

// Подключаем необходимые файлы
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

try {
    echo "Шаг 1: Создание сервисов...\n";
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    echo "✅ Сервисы созданы успешно\n\n";
    
    echo "Шаг 2: Получение всех пользователей с доступом...\n";
    $usersWithAccess = $adminService->getAllUsersWithAccess();
    echo "✅ Метод getAllUsersWithAccess выполнен\n";
    echo "Количество пользователей: " . count($usersWithAccess) . "\n\n";
    
    if (empty($usersWithAccess)) {
        echo "⚠️ ПРОБЛЕМА НАЙДЕНА: Список пользователей ПУСТОЙ!\n\n";
        
        echo "Проверка 1: Получение всех пользователей из базы...\n";
        $databaseService = new DatabaseService();
        $allUsers = $databaseService->getAllUsers();
        echo "Всего пользователей в базе: " . count($allUsers) . "\n\n";
        
        if (empty($allUsers)) {
            echo "❌ КРИТИЧЕСКАЯ ПРОБЛЕМА: База данных не возвращает пользователей!\n";
            echo "Возможные причины:\n";
            echo "1. Таблица ozna_data пустая\n";
            echo "2. Проблема с подключением к базе данных\n";
            echo "3. Неправильное имя таблицы\n\n";
        } else {
            echo "✅ В базе есть " . count($allUsers) . " пользователей\n\n";
            
            echo "Проверка 2: Анализ уровней доступа...\n";
            $usersWithLevel = 0;
            $usersWithoutLevel = 0;
            
            foreach ($allUsers as $user) {
                $accessLevel = $user['data']['access_level'] ?? 0;
                $fio = $user['data']['fio'] ?? 'Без ФИО';
                
                echo "User ID: {$user['user_id']}, ФИО: {$fio}, Уровень: {$accessLevel}\n";
                
                if ($accessLevel > 0) {
                    $usersWithLevel++;
                } else {
                    $usersWithoutLevel++;
                }
            }
            
            echo "\n";
            echo "Пользователей с уровнем > 0: {$usersWithLevel}\n";
            echo "Пользователей с уровнем 0: {$usersWithoutLevel}\n\n";
            
            if ($usersWithLevel === 0) {
                echo "❌ ПРОБЛЕМА: У всех пользователей уровень доступа = 0 (Гость)!\n";
                echo "Это объясняет почему список пустой.\n\n";
                echo "РЕШЕНИЕ:\n";
                echo "Нужно повысить уровень доступа хотя бы одному пользователю.\n";
                echo "Используйте скрипт admin_access_manager.php для управления уровнями.\n";
            }
        }
    } else {
        echo "✅ Список пользователей получен успешно!\n\n";
        echo "Пользователи с доступом:\n";
        echo "-------------------------\n";
        
        foreach ($usersWithAccess as $user) {
            $userId = $user['user_id'];
            $userData = $user['data'] ?? [];
            $fio = $userData['fio'] ?? 'Без ФИО';
            $accessLevel = $userData['access_level'] ?? 0;
            $functionalDirection = $userData['functional_direction'] ?? 'Не указано';
            
            $levelName = match($accessLevel) {
                0 => 'Гость',
                1 => 'Пользователь',
                2 => 'Администратор',
                3 => 'Суперадминистратор',
                4 => 'Суперадминистратор',
                default => "Неизвестный ({$accessLevel})"
            };
            
            echo "ID: {$userId}\n";
            echo "ФИО: {$fio}\n";
            echo "ФН: {$functionalDirection}\n";
            echo "Уровень: {$levelName} ({$accessLevel})\n";
            echo "-------------------------\n";
        }
    }
    
    echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Стек вызовов:\n" . $e->getTraceAsString() . "\n";
}
