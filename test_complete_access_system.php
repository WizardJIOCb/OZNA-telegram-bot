<?php

/**
 * Полный тестовый скрипт для проверки системы уровней доступа
 */

require_once 'config.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/AdminService.php';
require_once 'CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Полное тестирование системы уровней доступа\n";
echo "==========================================\n\n";

try {
    // Создаем экземпляры сервисов
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    $dbService = new DatabaseService();
    
    // Тестируемый пользователь ID (замените на реальный ID для тестирования)
    $testUserId = 230379668; // Пример ID пользователя
    
    echo "1. Проверка текущего уровня доступа пользователя {$testUserId}:\n";
    $accessLevel = $adminService->getUserAccessLevel($testUserId);
    $levelName = $adminService->getAccessLevelName($accessLevel);
    echo "   Уровень доступа: {$accessLevel} ({$levelName})\n\n";
    
    echo "2. Проверка статуса суперадмина для пользователя {$testUserId}:\n";
    $isSuperAdmin = $adminService->isSuperAdmin($testUserId);
    echo "   Статус суперадмина: " . ($isSuperAdmin ? "Да" : "Нет") . "\n\n";
    
    echo "3. Получение списка всех суперадминов:\n";
    $superAdmins = $adminService->getSuperAdmins();
    echo "   Суперадмины: " . implode(", ", $superAdmins) . "\n\n";
    
    echo "4. Проверка доступа к уровню Администратора:\n";
    $hasAdminAccess = $adminService->hasAccessLevel($testUserId, DatabaseService::ACCESS_LEVEL_ADMIN);
    echo "   Доступ к уровню администратора: " . ($hasAdminAccess ? "Да" : "Нет") . "\n\n";
    
    echo "5. Проверка доступа к уровню Суперадминистратора:\n";
    $hasSuperAdminAccess = $adminService->hasAccessLevel($testUserId, DatabaseService::ACCESS_LEVEL_SUPER_ADMIN);
    echo "   Доступ к уровню суперадминистратора: " . ($hasSuperAdminAccess ? "Да" : "Нет") . "\n\n";
    
    echo "6. Получение ФИО пользователя {$testUserId}:\n";
    $userFio = $adminService->getUserFio($testUserId);
    echo "   ФИО: " . (!empty($userFio) ? $userFio : "Не указано") . "\n\n";
    
    echo "7. Получение всех пользователей:\n";
    $allUsers = $dbService->getAllUsers();
    echo "   Всего пользователей: " . count($allUsers) . "\n\n";
    
    echo "Полное тестирование завершено успешно!\n";
    
} catch (Exception $e) {
    echo "Ошибка при тестировании: " . $e->getMessage() . "\n";
    echo "Код ошибки: " . $e->getCode() . "\n";
}