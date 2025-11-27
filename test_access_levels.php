<?php

/**
 * Test script for access levels functionality
 */

// Include necessary files with error handling
$filesToInclude = [
    __DIR__ . '/Custome/DB.php',
    __DIR__ . '/CustomCommands/Services/DatabaseService.php',
    __DIR__ . '/CustomCommands/Services/AdminService.php',
    __DIR__ . '/CustomCommands/Services/MessageService.php'
];

foreach ($filesToInclude as $file) {
    if (file_exists($file)) {
        require_once $file;
        echo "Подключен файл: " . basename($file) . "\n";
    } else {
        echo "Файл не найден: " . $file . "\n";
        exit(1);
    }
}

echo "=== Тестирование системы уровней доступа ===\n\n";

try {
    // Initialize services
    $messageService = new Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    echo "MessageService инициализирован успешно\n";
    
    $adminService = new Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
    echo "AdminService инициализирован успешно\n";
    
    // Test user ID (you can change this to test with different users)
    $testUserId = 230379668; // This should be a super admin
    
    echo "\nТестирование для пользователя ID: {$testUserId}\n\n";
    
    // Test 1: Check current access level
    echo "1. Проверка текущего уровня доступа:\n";
    $currentLevel = $adminService->getUserAccessLevel($testUserId);
    $levelName = $adminService->getAccessLevelName($currentLevel);
    echo "   Текущий уровень: {$levelName} ({$currentLevel})\n\n";
    
    // Test 2: Check if user is super admin
    echo "2. Проверка статуса суперадмина:\n";
    $isSuperAdmin = $adminService->isSuperAdmin($testUserId);
    echo "   Суперадмин: " . ($isSuperAdmin ? "Да" : "Нет") . "\n\n";
    
    // Test 3: Test access level checking
    echo "3. Проверка уровней доступа:\n";
    for ($i = 0; $i <= 3; $i++) {
        $hasAccess = $adminService->hasAccessLevel($testUserId, $i);
        $levelName = $adminService->getAccessLevelName($i);
        echo "   Доступ к уровню {$i} ({$levelName}): " . ($hasAccess ? "Есть" : "Нет") . "\n";
    }
    echo "\n";
    
    // Test 4: Display all access level names
    echo "4. Все уровни доступа:\n";
    echo "   " . Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_GUEST . " - " . $adminService->getAccessLevelName(Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_GUEST) . "\n";
    echo "   " . Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_USER . " - " . $adminService->getAccessLevelName(Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_USER) . "\n";
    echo "   " . Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_ADMIN . " - " . $adminService->getAccessLevelName(Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_ADMIN) . "\n";
    echo "   " . Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_SUPER_ADMIN . " - " . $adminService->getAccessLevelName(Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) . "\n\n";
    
    echo "=== Тестирование завершено успешно ===\n";
    
} catch (Exception $e) {
    echo "Ошибка при тестировании: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
}

?>