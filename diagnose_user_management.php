<?php
// Диагностика проблемы с управлением пользователями
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== ДИАГНОСТИКА УПРАВЛЕНИЯ ПОЛЬЗОВАТЕЛЯМИ ===\n\n";

try {
    // Шаг 1: Проверка базы данных
    echo "Шаг 1: Проверка подключения к базе данных\n";
    echo str_repeat("-", 60) . "\n";
    
    $databaseService = new DatabaseService();
    echo "✅ Подключение к базе данных успешно\n\n";
    
    // Шаг 2: Получение всех пользователей
    echo "Шаг 2: Получение всех пользователей из базы\n";
    echo str_repeat("-", 60) . "\n";
    
    $allUsers = $databaseService->getAllUsers();
    echo "Всего пользователей в базе: " . count($allUsers) . "\n\n";
    
    // Шаг 3: Фильтрация пользователей с доступом
    echo "Шаг 3: Фильтрация пользователей с уровнем доступа > 0\n";
    echo str_repeat("-", 60) . "\n";
    
    $usersWithAccess = [];
    foreach ($allUsers as $user) {
        $accessLevel = $user['data']['access_level'] ?? 0;
        if ($accessLevel > 0) {
            $usersWithAccess[] = $user;
        }
    }
    
    echo "Пользователей с доступом (уровень > 0): " . count($usersWithAccess) . "\n\n";
    
    if (count($usersWithAccess) === 0) {
        echo "⚠️  ПРОБЛЕМА: Нет пользователей с уровнем доступа > 0!\n";
        echo "Это объясняет, почему список пустой.\n\n";
    } else {
        echo "✅ Найдены пользователи с доступом\n\n";
        
        // Показываем первых 5 пользователей
        echo "Первые 5 пользователей с доступом:\n";
        echo str_repeat("-", 60) . "\n";
        $count = min(5, count($usersWithAccess));
        for ($i = 0; $i < $count; $i++) {
            $user = $usersWithAccess[$i];
            $userId = $user['user_id'];
            $userData = $user['data'] ?? [];
            $fio = $userData['fio'] ?? 'Без ФИО';
            $accessLevel = $userData['access_level'] ?? 0;
            
            // Получение имени уровня доступа
            $levelNames = [
                0 => 'Гость',
                1 => 'Пользователь',
                2 => 'Супервайзер',
                3 => 'Администратор',
                4 => 'Суперадминистратор'
            ];
            $levelName = $levelNames[$accessLevel] ?? 'Неизвестный';
            
            echo ($i + 1) . ". User ID: {$userId}\n";
            echo "   ФИО: {$fio}\n";
            echo "   Уровень: {$levelName} ({$accessLevel})\n";
            echo str_repeat("-", 60) . "\n";
        }
    }
    
    // Шаг 4: Тест AdminService.getAllUsersWithAccess()
    echo "\nШаг 4: Тест AdminService.getAllUsersWithAccess()\n";
    echo str_repeat("-", 60) . "\n";
    
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    $usersFromService = $adminService->getAllUsersWithAccess();
    echo "Пользователей возвращено AdminService: " . count($usersFromService) . "\n";
    
    if (count($usersFromService) === 0) {
        echo "❌ ПРОБЛЕМА: AdminService вернул пустой список!\n";
    } else {
        echo "✅ AdminService работает корректно\n";
    }
    
    // Шаг 5: Проверка уровня доступа суперадмина
    echo "\nШаг 5: Проверка вашего уровня доступа\n";
    echo str_repeat("-", 60) . "\n";
    
    $superAdminId = 5261465966; // Ваш User ID
    $yourAccessLevel = $adminService->getUserAccessLevel($superAdminId);
    $yourLevelName = $adminService->getAccessLevelName($yourAccessLevel);
    
    echo "Ваш User ID: {$superAdminId}\n";
    echo "Ваш уровень доступа: {$yourLevelName} ({$yourAccessLevel})\n";
    
    if ($yourAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
        echo "❌ ПРОБЛЕМА: У вас недостаточный уровень доступа!\n";
        echo "   Требуется: Администратор (3) или выше\n";
        echo "   У вас: {$yourLevelName} ({$yourAccessLevel})\n";
    } else {
        echo "✅ У вас достаточный уровень доступа\n";
    }
    
    // Резюме
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "РЕЗЮМЕ ДИАГНОСТИКИ\n";
    echo str_repeat("=", 60) . "\n";
    
    $issues = [];
    
    if (count($usersWithAccess) === 0) {
        $issues[] = "Нет пользователей с уровнем доступа > 0 в базе данных";
    }
    
    if (count($usersFromService) === 0) {
        $issues[] = "AdminService.getAllUsersWithAccess() возвращает пустой список";
    }
    
    if ($yourAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
        $issues[] = "Недостаточный уровень доступа у суперадмина (требуется ≥3)";
    }
    
    if (empty($issues)) {
        echo "✅ Все проверки пройдены успешно!\n";
        echo "\nПроблема может быть в:\n";
        echo "1. Обработке callback данных (CallbackHandlerFactory)\n";
        echo "2. Логике AccessLevelHandler\n";
        echo "3. Отправке сообщения пользователю\n";
    } else {
        echo "❌ Обнаружены следующие проблемы:\n\n";
        foreach ($issues as $i => $issue) {
            echo ($i + 1) . ". {$issue}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo "\nСтек вызовов:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Диагностика завершена\n";
