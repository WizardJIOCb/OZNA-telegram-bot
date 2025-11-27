<?php
/**
 * Test script for user data storage functionality
 */

// Include necessary files with error handling
$filesToInclude = [
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

echo "=== Тестирование системы хранения данных пользователя ===\n\n";

try {
    // Initialize services
    $messageService = new Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    echo "MessageService инициализирован успешно\n";
    
    $databaseService = new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    echo "DatabaseService инициализирован успешно\n";
    
    $adminService = new Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
    echo "AdminService инициализирован успешно\n";
    
    // Test user ID
    $testUserId = 123456789; // Test user ID
    
    echo "\nТестирование для пользователя ID: {$testUserId}\n\n";
    
    // Test 1: Get current user data
    echo "1. Получение текущих данных пользователя:\n";
    $userData = $databaseService->getUserData($testUserId);
    echo "   Текущие данные: " . json_encode($userData, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Test 2: Set user FIO
    echo "2. Установка ФИО пользователя:\n";
    $fio = "Тестовый Пользователь ФИО";
    $result = $databaseService->setUserFio($testUserId, $fio);
    echo "   Результат установки ФИО: " . ($result ? "Успешно" : "Ошибка") . "\n";
    
    // Test 3: Set user functional direction
    echo "3. Установка функционального направления:\n";
    $direction = "Тестовое направление";
    $result = $databaseService->setUserFunctionalDirection($testUserId, $direction);
    echo "   Результат установки направления: " . ($result ? "Успешно" : "Ошибка") . "\n";
    
    // Test 4: Update user access level
    echo "4. Обновление уровня доступа:\n";
    $result = $databaseService->updateUserAccessLevel($testUserId, Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_USER);
    echo "   Результат обновления уровня доступа: " . ($result ? "Успешно" : "Ошибка") . "\n";
    
    // Test 5: Get updated user data
    echo "\n5. Получение обновленных данных пользователя:\n";
    $userData = $databaseService->getUserData($testUserId);
    echo "   Обновленные данные: " . json_encode($userData, JSON_UNESCAPED_UNICODE) . "\n";
    
    // Test 6: Get user access level through AdminService
    echo "\n6. Получение уровня доступа через AdminService:\n";
    $accessLevel = $adminService->getUserAccessLevel($testUserId);
    $levelName = $adminService->getAccessLevelName($accessLevel);
    echo "   Уровень доступа: {$levelName} ({$accessLevel})\n";
    
    echo "\n=== Тестирование завершено успешно ===\n";
    
} catch (Exception $e) {
    echo "Ошибка при тестировании: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
}

?>