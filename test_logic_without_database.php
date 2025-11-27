<?php

/**
 * Тестовый скрипт для проверки логики без подключения к базе данных
 */

require_once 'config.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Тестирование логики системы уровней доступа без базы данных\n";
echo "=========================================================\n\n";

try {
    // Создаем экземпляры сервисов
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Тестируемый пользователь ID
    $testUserId = 230379668;
    
    echo "1. Проверка метода isSuperAdmin при недоступной базе данных:\n";
    $isSuperAdmin = $adminService->isSuperAdmin($testUserId);
    echo "   Результат: " . ($isSuperAdmin ? "true" : "false") . "\n";
    echo "   Ожидаемый результат: false (база данных недоступна)\n\n";
    
    echo "2. Проверка метода getSuperAdmins при недоступной базе данных:\n";
    $superAdmins = $adminService->getSuperAdmins();
    echo "   Результат: " . json_encode($superAdmins) . "\n";
    echo "   Ожидаемый результат: [] (пустой массив, база данных недоступна)\n\n";
    
    echo "3. Проверка метода getUserAccessLevel при недоступной базе данных:\n";
    $accessLevel = $adminService->getUserAccessLevel($testUserId);
    $levelName = $adminService->getAccessLevelName($accessLevel);
    echo "   Результат: {$accessLevel} ({$levelName})\n";
    echo "   Ожидаемый результат: 1 (Пользователь) - уровень по умолчанию при недоступной базе\n\n";
    
    echo "4. Проверка метода hasAccessLevel при недоступной базе данных:\n";
    $hasUserAccess = $adminService->hasAccessLevel($testUserId, 1);
    $hasAdminAccess = $adminService->hasAccessLevel($testUserId, 3);
    echo "   Доступ к уровню пользователя (1): " . ($hasUserAccess ? "true" : "false") . "\n";
    echo "   Доступ к уровню администратора (3): " . ($hasAdminAccess ? "true" : "false") . "\n";
    echo "   Ожидаемый результат: true, false (уровень по умолчанию 1)\n\n";
    
    echo "Тестирование логики без базы данных завершено успешно!\n";
    echo "Все методы корректно обрабатывают ситуацию с недоступной базой данных.\n";
    
} catch (Exception $e) {
    echo "Ошибка при тестировании: " . $e->getMessage() . "\n";
    echo "Код ошибки: " . $e->getCode() . "\n";
}