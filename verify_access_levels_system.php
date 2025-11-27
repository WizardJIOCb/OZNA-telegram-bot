<?php
require_once __DIR__ . '/Custome/DB.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Проверка работы новой системы уровней доступа ===\n\n";

// Инициализация сервисов
$messageService = new MessageService();
$databaseService = new DatabaseService();
$adminService = new AdminService($messageService);

// Тестовые пользователи
$testUsers = [
    // [user_id, expected_level, description]
    [230379668, DatabaseService::ACCESS_LEVEL_SUPER_ADMIN, "Суперадмин"],
    [5261465966, DatabaseService::ACCESS_LEVEL_SUPER_ADMIN, "Суперадмин"],
    [1090118386, DatabaseService::ACCESS_LEVEL_SUPER_ADMIN, "Суперадмин"],
    [444234398, DatabaseService::ACCESS_LEVEL_SUPER_ADMIN, "Суперадмин"],
    // Добавьте сюда тестовых пользователей с разными уровнями доступа
];

echo "Проверка уровней доступа пользователей:\n";
echo str_repeat("-", 60) . "\n";
echo str_pad("User ID", 15) . str_pad("Ожидаемый", 15) . str_pad("Фактический", 15) . "Результат\n";
echo str_repeat("-", 60) . "\n";

$testsPassed = 0;
$testsFailed = 0;

foreach ($testUsers as $testUser) {
    $userId = $testUser[0];
    $expectedLevel = $testUser[1];
    $description = $testUser[2];
    
    // Получаем фактический уровень доступа
    $actualLevel = $adminService->getUserAccessLevel($userId);
    $actualLevelName = $adminService->getAccessLevelName($actualLevel);
    $expectedLevelName = $adminService->getAccessLevelName($expectedLevel);
    
    // Проверяем соответствие
    $result = ($actualLevel == $expectedLevel) ? "✅ Пройден" : "❌ Провален";
    
    if ($actualLevel == $expectedLevel) {
        $testsPassed++;
    } else {
        $testsFailed++;
    }
    
    echo str_pad($userId, 15) . 
         str_pad($expectedLevelName, 15) . 
         str_pad($actualLevelName, 15) . 
         "$result ($description)\n";
}

echo "\n" . str_repeat("-", 60) . "\n";
echo "Результаты тестирования:\n";
echo "Пройдено тестов: $testsPassed\n";
echo "Провалено тестов: $testsFailed\n";
echo "Общее количество тестов: " . ($testsPassed + $testsFailed) . "\n";

// Проверка функций админ-панели
echo "\nПроверка функций админ-панели:\n";
echo str_repeat("-", 40) . "\n";

// Тест 1: Проверка доступа гостя к админке
$guestUserId = 123456789; // Пример ID гостя
$guestLevel = $adminService->getUserAccessLevel($guestUserId);
$hasAdminAccess = $adminService->hasAccessLevel($guestUserId, DatabaseService::ACCESS_LEVEL_ADMIN);

echo "Гость (ID: $guestUserId):\n";
echo "  Уровень доступа: " . $adminService->getAccessLevelName($guestLevel) . "\n";
echo "  Доступ к админке: " . ($hasAdminAccess ? "Есть" : "Нет") . "\n";

// Тест 2: Проверка доступа администратора к админке
$adminUserId = 111111111; // Пример ID администратора
$adminLevel = $adminService->getUserAccessLevel($adminUserId);
$hasAdminAccess = $adminService->hasAccessLevel($adminUserId, DatabaseService::ACCESS_LEVEL_ADMIN);

echo "\nАдминистратор (ID: $adminUserId):\n";
echo "  Уровень доступа: " . $adminService->getAccessLevelName($adminLevel) . "\n";
echo "  Доступ к админке: " . ($hasAdminAccess ? "Есть" : "Нет") . "\n";

// Тест 3: Проверка доступа суперадмина к админке
$superAdminUserId = 230379668;
$superAdminLevel = $adminService->getUserAccessLevel($superAdminUserId);
$hasAdminAccess = $adminService->hasAccessLevel($superAdminUserId, DatabaseService::ACCESS_LEVEL_ADMIN);

echo "\nСуперадмин (ID: $superAdminUserId):\n";
echo "  Уровень доступа: " . $adminService->getAccessLevelName($superAdminLevel) . "\n";
echo "  Доступ к админке: " . ($hasAdminAccess ? "Есть" : "Нет") . "\n";

echo "\n=== Проверка завершена ===\n";
?>